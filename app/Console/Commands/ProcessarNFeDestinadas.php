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

        // 1. Verificação de Bloqueio (Consumo Indevido)
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
            // 2. Consultar Novas Notas (Resumos)
            // Lógica similar ao NotaEntradaController::buscarNovas, mas simplificada
            $lastNSU = Configuracao::get('nfe_last_nsu') ?: 0;
            $maxLoops = 3; // Limite de segurança
            $loopCount = 0;
            $novasNotas = 0;

            $this->info("Consultando SEFAZ a partir do NSU: $lastNSU");

            do {
                // Verifica bloqueio antes de cada loop
                $nextQueryCheck = Configuracao::get('nfe_next_dfe_query');
                if ($nextQueryCheck && now()->lt(Carbon::parse($nextQueryCheck))) {
                    $this->warn('Bloqueio ativado durante o loop.');
                    break;
                }

                $resp = $nfeService->consultarNotasDestinadas($lastNSU);
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
                                $this->processarDocDFe($nsu, $schema, $xmlContent);
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

            // 3. Manifestar e Baixar XMLs para notas apenas "detectadas" ou "pendente" (Resumos)
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
                        // Verifica bloqueio antes de cada tentativa
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
                            $this->info("Sucesso: XML baixado e salvo.");
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

            // Só cria se não existir ou atualiza se for resumo
            NotaEntrada::updateOrCreate(
                ['chave_acesso' => $chave],
                [
                    'emitente_cnpj' => $cnpj,
                    'emitente_nome' => $nome,
                    'valor_total' => $valor,
                    'data_emissao' => $data,
                    'status' => $status
                ]
            );
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
            // Evento (Cancelamento)
            $chave = (string) $xml->chNFe;
            $tpEvento = (string) $xml->tpEvento;

            if ($tpEvento == '110111') {
                NotaEntrada::where('chave_acesso', $chave)->update(['status' => 'cancelada']);
            }
        }
    }
}
