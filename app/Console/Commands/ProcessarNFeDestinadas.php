<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NFeService;
use App\Models\Configuracao;
use App\Models\NotaEntrada;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessarNFeDestinadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfe:processar-destinadas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta novas NF-e destinadas na SEFAZ, manifesta e baixa XMLs automaticamente';

    /**
     * Execute the console command.
     */
    public function handle(NFeService $nfeService)
    {
        $this->info('Iniciando processamento automático de NF-e destinadas...');
        Log::info('Command nfe:processar-destinadas iniciado.');

        // 0. Correção Automática de Dados (Sanitização)
        // Corrige notas que ficaram com resNFe salvo no lugar do XML completo
        $affected = NotaEntrada::where('xml_content', 'like', '%<resNFe%')
            ->update([
                'xml_content' => null,
                'status' => 'detectada',
                'manifestacao' => 'ciencia' // Garante que vamos tentar baixar de novo
            ]);

        if ($affected > 0) {
            $this->info("Sanitização: $affected notas com XML inválido (resNFe) foram resetadas para novo download.");
            Log::warning("Sanitização: $affected notas corrigidas automaticamente.");
        }

        // 1. Verificação de Bloqueio (Consumo Indevido - Global)
        $nextQuery = Configuracao::get('nfe_next_dfe_query');
        if ($nextQuery) {
            $nextQueryTime = Carbon::parse($nextQuery);
            if (now()->lt($nextQueryTime)) {
                $diff = (int) ceil(now()->diffInMinutes($nextQueryTime));
                $msg = "Consulta bloqueada pela SEFAZ. Aguardando {$diff} minutos.";
                $this->warn($msg);
                Log::warning("Command nfe:processar-destinadas: $msg");
                return;
            }
        }

        try {
            // 2. Manifestar e Baixar XMLs para notas apenas "detectadas" ou "pendente" (PRIORIDADE)
            // Executamos antes de buscar novas para garantir que o backlog seja processado
            // mesmo que estejamos em "wait" para novas consultas.

            // Estratégia Agressiva: Pega até 50 notas por vez, priorizando as recém-detectadas (updated_at)
            $notasPendentes = NotaEntrada::whereIn('status', ['detectada', 'pendente'])
                ->orderBy('updated_at', 'desc')
                ->take(50)
                ->get();

            if ($notasPendentes->count() > 0) {
                $this->info("Encontradas {$notasPendentes->count()} notas pendentes de download. Iniciando manifestação e download...");

                foreach ($notasPendentes as $nota) {
                    $chave = $nota->chave_acesso;
                    $this->info("Processando nota: $chave");

                    try {
                        // Verifica bloqueio GLOBAL antes de cada tentativa
                        $nextQueryCheck = Configuracao::get('nfe_next_dfe_query');
                        if ($nextQueryCheck && now()->lt(Carbon::parse($nextQueryCheck))) {
                            $this->warn('Bloqueio detectado. Interrompendo downloads.');
                            break;
                        }

                        // baixarPorChave já contém toda a lógica de Manifestação (Ciência/Confirmação) e Retry
                        $result = $nfeService->baixarPorChave($chave);

                        // Se retornou sucesso, atualiza o registro
                        if ($result && isset($result['content'])) {
                            $this->processarDocDFe($result['nsu'], $result['schema'], $result['content']);

                            // Se baixou XML completo, assume Ciência da Operação se não houver manifestação definida
                            if (strpos($result['schema'], 'procNFe') !== false || strpos($result['schema'], 'resNFe') === false) {

                                // Validação adicional: Verifica se o conteúdo é realmente um XML completo ou se é um Resumo disfarçado
                                if (strpos($result['content'], '<resNFe') !== false) {
                                    $this->info("Aviso: Conteúdo retornado é um Resumo (resNFe), apesar do schema. Aguardando XML completo.");
                                    $nota->update(['manifestacao' => 'ciencia']);
                                } else {
                                    // Recarrega para verificar se processarDocDFe salvou o XML
                                    $nota->refresh();

                                    if (empty($nota->xml_content)) {
                                        $this->warn("Aviso: processarDocDFe não salvou o XML (provável falha no parse). Forçando salvamento do conteúdo.");
                                        $nota->update([
                                            'xml_content' => $result['content'],
                                            'status' => 'downloaded',
                                            'manifestacao' => 'ciencia'
                                        ]);
                                    } else {
                                        // Apenas atualiza a manifestação se necessário
                                        $nota->update(['manifestacao' => 'ciencia']);
                                    }

                                    $this->info("Sucesso: XML baixado e salvo. Manifestação atualizada para Ciência.");
                                }
                            } else {
                                $this->info("Sucesso: Resumo atualizado (Ainda pendente XML completo).");
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("Erro ao processar nota $chave: " . $e->getMessage());
                        // Não interrompe o loop por erro individual, a menos que seja bloqueio (já tratado no baixarPorChave/config)
                    }

                    // Pausa de segurança entre downloads
                    sleep(5);
                }
            } else {
                $this->info("Nenhuma nota pendente de download.");
            }

            // 3. Consultar Novas Notas (Resumos)
            // Lógica similar ao NotaEntradaController::buscarNovas, mas simplificada

            // Verifica intervalo de espera por consulta vazia (137) antes de tentar consultar NSU
            $nextNSUCheck = Configuracao::get('nfe_next_nsu_check');
            if ($nextNSUCheck && now()->lt(Carbon::parse($nextNSUCheck))) {
                $this->info("Aguardando intervalo de verificação de novas notas (Regra SEFAZ 137). Pulando consulta NSU.");
            } else {
                $lastNSU = Configuracao::get('nfe_last_nsu') ?: 0;
                $maxLoops = 3; // Limite de segurança
                $loopCount = 0;
                $novasNotas = 0;

                $this->info("Consultando SEFAZ a partir do NSU: $lastNSU");

                do {
                    // Verifica bloqueio GLOBAL antes de cada loop
                    $nextQueryCheck = Configuracao::get('nfe_next_dfe_query');
                    if ($nextQueryCheck && now()->lt(Carbon::parse($nextQueryCheck))) {
                        $this->warn('Bloqueio ativado durante o loop.');
                        break;
                    }

                    try {
                        $resp = $nfeService->consultarNotasDestinadas($lastNSU);
                    } catch (\Exception $e) {
                        if (str_contains($e->getMessage(), 'Aguardando intervalo')) {
                            $this->info($e->getMessage());
                            break;
                        }
                        throw $e;
                    }

                    $ultNSU = $resp->ultNSU;
                    $maxNSU = $resp->maxNSU;

                    if (isset($resp->loteDistDFeInt->docZip)) {
                        $docs = is_array($resp->loteDistDFeInt->docZip) ? $resp->loteDistDFeInt->docZip : [$resp->loteDistDFeInt->docZip];

                        foreach ($docs as $doc) {
                            // Proteção contra formatos inesperados
                            if (!is_object($doc) && !is_array($doc)) {
                                Log::warning("Formato inesperado em docZip: " . json_encode($doc));
                                continue;
                            }

                            // Converte para objeto se for array, para padronizar acesso
                            $docObj = (object) $doc;

                            $nsu = $docObj->NSU ?? null;
                            $schema = $docObj->schema ?? null;

                            if (!$nsu) {
                                continue;
                            }

                            // Extrai conteúdo
                            $contentEncoded = $docObj->{'$'} ?? $docObj->{0} ?? null;
                            if (!$contentEncoded) {
                                foreach ($docObj as $key => $value) {
                                    if (is_string($value) && strlen($value) > 20) {
                                        $contentEncoded = $value;
                                        break;
                                    }
                                }
                            }

                            if ($contentEncoded) {
                                try {
                                    $xmlContent = gzdecode(base64_decode($contentEncoded));
                                    // Processa (Salva Resumo ou XML se já vier)
                                    $novaChave = $this->processarDocDFe($nsu, $schema, $xmlContent);

                                    // Se for uma nova chave (resNFe detectado), processa IMEDIATAMENTE
                                    if ($novaChave) {
                                        $this->info("Nova nota detectada: $novaChave. Iniciando download imediato...");
                                        try {
                                            // Tenta baixar imediatamente (Ciência -> Download)
                                            // Isso evita esperar a próxima execução do comando
                                            $nfeService->baixarPorChave($novaChave);
                                            $this->info("Nota $novaChave processada com sucesso no fluxo contínuo.");
                                        } catch (\Exception $eDownload) {
                                            $this->error("Erro ao tentar baixar imediatamente a nota $novaChave: " . $eDownload->getMessage());
                                            // Se falhar, ela já está salva como 'detectada' e será pega na próxima execução ou loop
                                        }
                                    }

                                    $novasNotas++;
                                } catch (\Exception $e) {
                                    Log::error("Erro ao processar doc NSU $nsu no command: " . $e->getMessage());
                                }
                            }
                        }
                    }

                    // Atualiza NSU
                    Configuracao::set('nfe_last_nsu', $ultNSU, 'nfe', 'text', 'Último NSU consultado na SEFAZ');
                    $lastNSU = $ultNSU;
                    $loopCount++;

                    if ($ultNSU >= $maxNSU) {
                        break;
                    }

                    // Rate limiting
                    sleep(2);
                } while ($loopCount < $maxLoops);

                $this->info("Consulta finalizada. {$novasNotas} documentos processados.");
            }

            // Health Check Final
            $notasProntas = NotaEntrada::whereIn('status', ['detectada', 'downloaded', 'pendente'])->count();
            // Log no banco via LogService (Sistema)
            LogService::registrarSistema('SEFAZ', 'Saúde SEFAZ', "Sincronização finalizada. [{$notasProntas}] Notas prontas para conferência na tela de Entrada.");
        } catch (\Exception $e) {
            $this->error('Erro fatal no command: ' . $e->getMessage());
            Log::error('Erro fatal no command nfe:processar-destinadas: ' . $e->getMessage());
        }
    }

    /**
     * Copiado/Adaptado de NotaEntradaController
     */
    protected function processarDocDFe($nsu, $schema, $xmlContent)
    {
        $xml = simplexml_load_string($xmlContent);
        $returnChave = null; // Retorna a chave se for um novo resumo detectado

        if (strpos($schema, 'resNFe') !== false) {
            // Resumo da NFe
            $chave = preg_replace('/[^0-9]/', '', (string) $xml->chNFe);
            $cnpj = (string) $xml->CNPJ;
            $nome = (string) $xml->xNome;
            $valor = (float) $xml->vNF;
            $data = (string) $xml->dhEmi;
            $statusSefaz = (int) $xml->cSitNFe;

            $status = 'detectada';
            if ($statusSefaz == 3) $status = 'cancelada';

            // Verifica se já existe para saber se é novo
            $exists = NotaEntrada::where('chave_acesso', $chave)->exists();

            // Só cria se não existir ou atualiza se for resumo
            $nota = NotaEntrada::updateOrCreate(
                ['chave_acesso' => $chave],
                [
                    'nsu' => $nsu, // Salva o NSU para controle
                    'emitente_cnpj' => $cnpj,
                    'emitente_nome' => $nome,
                    'valor_total' => $valor,
                    'data_emissao' => $data,
                    'status' => $status
                ]
            );

            // Log de Sistema "Cagueta" para detecção
            if (!$exists) {
                LogService::registrarSistema('SEFAZ', 'NF-e Detectada', "Chave: {$chave} - Status: {$status}");
            }

            // Se for novo e não estiver cancelado, marca para retorno para processamento imediato
            if (!$exists && $status != 'cancelada') {
                $returnChave = $chave;
            }
        } elseif (strpos($schema, 'procNFe') !== false || strpos($schema, 'resNFe') === false) {
            // NFe Completa
            $infNFe = null;
            if (isset($xml->NFe->infNFe)) {
                $infNFe = $xml->NFe->infNFe;
            } elseif (isset($xml->infNFe)) {
                $infNFe = $xml->infNFe;
            }

            if ($infNFe) {
                $chave = preg_replace('/[^0-9]/', '', (string) $infNFe['Id']);
                $emit = $infNFe->emit;
                $total = $infNFe->total->ICMSTot;

                NotaEntrada::updateOrCreate(
                    ['chave_acesso' => $chave],
                    [
                        'nsu' => $nsu, // Atualiza NSU se vier no XML completo também
                        'emitente_cnpj' => (string) $emit->CNPJ,
                        'emitente_nome' => (string) $emit->xNome,
                        'valor_total' => (float) $total->vNF,
                        'data_emissao' => (string) $infNFe->ide->dhEmi,
                        'xml_content' => $xmlContent,
                        'status' => 'downloaded' // Status final que libera processamento
                    ]
                );
            }
        } elseif (strpos($schema, 'resEvento') !== false) {
            // Evento (Cancelamento, CCe, etc)
            $chave = (string) $xml->chNFe;
            $tpEvento = (string) $xml->tpEvento;

            // 110111 = Cancelamento
            if ($tpEvento == '110111') {
                NotaEntrada::where('chave_acesso', $chave)->update(['status' => 'cancelada']);
                $this->info("Evento de Cancelamento processado para nota: $chave");
            }
            // 610600 = CTe (Na verdade, eventos de CTe são em outro WS, mas se vier aqui, logamos)
            // Se for Carta de Correção (110110), apenas logamos por enquanto
            else {
                Log::info("Evento $tpEvento recebido para nota $chave. (Não processado especificamente)");
            }
        }

        return $returnChave;
    }
}
