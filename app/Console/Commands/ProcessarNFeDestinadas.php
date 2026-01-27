<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NFeService;
use App\Models\Configuracao;
use App\Models\NotaEntrada;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\LogService;

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
        // 0. Verificação de Bloqueio (Consumo Indevido - Global) - PRIORIDADE MÁXIMA
        $nextQuery = Configuracao::get('nfe_next_dfe_query');
        if ($nextQuery) {
            $nextQueryTime = Carbon::parse($nextQuery);
            if (now()->lt($nextQueryTime)) {
                $this->info("Sistema em modo soneca até " . $nextQueryTime->format('H:i:s'));
                return;
            }
        }

        $this->info('Command nfe:processar-destinadas iniciado.');
        Log::info('Command nfe:processar-destinadas iniciado.');

        // 0. Sanitização de Status (Correção Retroativa)
        // Se tem XML mas o status ainda é pendente/downloaded, corrige para concluido
        $fixedStatus = NotaEntrada::whereNotNull('xml_content')
            ->where('xml_content', '!=', '')
            ->where('status', '!=', 'concluido')
            ->update(['status' => 'concluido']);

        if ($fixedStatus > 0) {
            $this->info("Sanitização: Status corrigido para 'concluido' em $fixedStatus notas com XML baixado.");
            Log::info("Sanitização: $fixedStatus notas tiveram status corrigido para 'concluido'.");
        }

        // 0.1 Correção Automática de Dados (Sanitização XML)
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

        // 0.2 Sanitização Preventiva (Tamanho Mínimo)
        // XMLs muito pequenos (<1000 chars) geralmente são erros ou resumos mal salvos
        $smallXmls = NotaEntrada::whereNotNull('xml_content')
            ->whereRaw('LENGTH(xml_content) < 1000')
            ->update([
                'xml_content' => null,
                'status' => 'detectada',
                'manifestacao' => 'ciencia'
            ]);

        if ($smallXmls > 0) {
            $this->info("Sanitização: $smallXmls notas com XML suspeito (<1000 chars) foram resetadas.");
            Log::warning("Sanitização: $smallXmls notas resetadas por tamanho insuficiente.");
        }

        // 1. Verificação de Bloqueio (Consumo Indevido - Global)
        $nextQuery = Configuracao::get('nfe_next_dfe_query');
        if ($nextQuery) {
            $nextQueryTime = Carbon::parse($nextQuery);
            if (now()->lt($nextQueryTime)) {
                $this->info("Sistema em modo soneca até " . $nextQueryTime->format('H:i:s'));
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

                $sucessosCount = 0;

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

                        // 1. Prioridade de Download por Chave (Solicitação Explícita)
                        $result = $nfeService->baixarPorChave($chave);

                        // DETECÇÃO DE MODO SONECA (Erro 656) no Download Manual
                        if (isset($result['cStat']) && $result['cStat'] == '656') {
                             $sonecaTime = now()->addMinutes(65);
                             Configuracao::set('nfe_next_dfe_query', $sonecaTime->toDateTimeString(), 'nfe', 'datetime', 'Bloqueio temporário SEFAZ (656)');

                             $this->info("Modo Soneca Ativado! Erro 656 detectado. Pausa de 65 minutos.");
                             Log::warning("[Sistema] - Bloqueio SEFAZ detectado. Robô pausado por 65 min para evitar banimento do IP.");
                             break;
                        }

                        // Imediatamente após a chamada, verifique se a coluna xml_content da $nota foi preenchida
                        $nota->refresh();

                        if (!empty($nota->xml_content)) {
                            // Se houver conteúdo, force o status
                            $nota->update(['status' => 'concluido']);

                            $this->info("Sucesso: XML recuperado e salvo. Status: concluido.");
                            $sucessosCount++;

                            // Log individual removido para evitar spam (Lei do Silêncio)
                        } else {
                            $msgErro = isset($result['message']) ? $result['message'] : 'Erro desconhecido ao baixar XML.';
                            $this->error("Falha: XML não salvo para a nota {$chave}. Motivo: {$msgErro}");

                            // Log de Falha mantido pois é erro
                            LogService::registrarSistema('Sistema', 'Falha Download', "Falha ao recuperar XML da Nota {$chave}. Motivo: {$msgErro}");
                        }

                    } catch (\Exception $e) {
                        $this->error("Erro ao processar nota $chave: " . $e->getMessage());
                        LogService::registrarSistema('Sistema', 'Erro Exceção', "Erro ao processar nota {$chave}: " . $e->getMessage());
                    }

                    // Pausa de segurança entre downloads
                    sleep(10);
                }

                // Log Agrupado de Sucesso
                if ($sucessosCount > 0) {
                     LogService::registrarSistema('Sistema', 'Sincronização Finalizada', "{$sucessosCount} notas baixadas e salvas com sucesso.");
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

                    if (is_array($resp)) {
                        $resp = (object) $resp;
                    }

                    // DETECÇÃO DE MODO SONECA (Erro 656)
                    if (isset($resp->cStat) && $resp->cStat == '656') {
                         $sonecaTime = now()->addMinutes(65);
                         Configuracao::set('nfe_next_dfe_query', $sonecaTime->toDateTimeString(), 'nfe', 'datetime', 'Bloqueio temporário SEFAZ (656)');

                         $this->info("Modo Soneca Ativado! Erro 656 detectado. Próxima execução apenas após " . $sonecaTime->format('H:i:s'));
                         Log::warning("[Sistema] - Bloqueio SEFAZ detectado. Robô pausado por 65 min para evitar banimento do IP.");
                         break;
                    }

                    $ultNSU = data_get($resp, 'ultNSU', 0);
                    $maxNSU = data_get($resp, 'maxNSU', $ultNSU);

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
            LogService::registrarSistema('Sistema', 'Saúde SEFAZ', "Sincronização finalizada. [{$notasProntas}] Notas prontas para conferência na tela de Entrada.");

            // 4. Limpeza de Cache de Configuração (Heroku)
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            $this->info('Cache limpo para garantir integridade do Heroku.');
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

            // Verifica se NSU já existe em outra nota para evitar duplicação
            if ($nsu) {
                $nsuExistente = NotaEntrada::where('nsu', $nsu)->where('chave_acesso', '!=', $chave)->first();
                if ($nsuExistente) {
                    Log::warning("NSU {$nsu} duplicado ignorado para chave {$chave} (Command). Pertence à chave {$nsuExistente->chave_acesso}");
                    return null;
                }
            }

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

            // Log de Auditoria de Sistema para detecção
            if (!$exists) {
                LogService::registrarSistema('Sistema', 'NF-e Detectada', "Chave: {$chave} - Status: {$status}");
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

                // Verifica se NSU já existe em outra nota para evitar duplicação
                if ($nsu) {
                    $nsuExistente = NotaEntrada::where('nsu', $nsu)->where('chave_acesso', '!=', $chave)->first();
                    if ($nsuExistente) {
                        Log::warning("NSU {$nsu} duplicado ignorado para chave {$chave} (Command XML). Pertence à chave {$nsuExistente->chave_acesso}");
                        return null;
                    }
                }

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
                        'status' => 'concluido' // Status final que libera processamento (Verde na View)
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
