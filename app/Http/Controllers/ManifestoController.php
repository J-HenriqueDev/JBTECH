<?php

namespace App\Http\Controllers;

use App\Models\NotaEntrada;
use App\Models\Configuracao;
use App\Services\NFeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ManifestoController extends Controller
{
    protected $nfeService;

    public function __construct(NFeService $nfeService)
    {
        $this->nfeService = $nfeService;
    }

    public function index()
    {
        // Buscar notas dos últimos 90 dias ou atualizadas recentemente
        $notas = NotaEntrada::where(function ($q) {
            $q->where('created_at', '>=', Carbon::now()->subDays(90))
                ->orWhere('updated_at', '>=', Carbon::now()->subDays(1));
        })
            ->orderBy('updated_at', 'desc')
            ->get();

        // Verifica se há bloqueio ativo para exibir alerta na tela
        $nextQuery = Configuracao::get('nfe_next_dfe_query');
        $bloqueioMsg = null;
        if ($nextQuery) {
            try {
                $nextQueryDate = Carbon::parse($nextQuery);
                if (now()->lt($nextQueryDate)) {
                    $diffMinutes = (int) ceil(now()->diffInMinutes($nextQueryDate));
                    $bloqueioMsg = "Sincronização temporariamente pausada para evitar bloqueio na SEFAZ. Próxima tentativa em {$diffMinutes} minutos.";
                }
            } catch (\Exception $e) {
                // Se a data estiver inválida, ignoramos o bloqueio visual e logamos o erro
                Log::warning("Data de bloqueio inválida na configuração: $nextQuery");
            }
        }

        return view('content.nfe.manifesto', compact('notas', 'bloqueioMsg'));
    }

    public function manifestar(Request $request)
    {
        $request->validate([
            'chaves' => 'required|array',
            'tipo' => 'required|in:ciencia,confirmada,desconhecida,nao_realizada',
        ]);

        $chaves = $request->input('chaves');
        $tipo = $request->input('tipo');
        $sucesso = 0;
        $erros = [];

        // Mapeamento de códigos de evento para a SEFAZ
        $eventoMap = [
            'ciencia' => 210210,
            'confirmada' => 210200,
            'desconhecida' => 210220,
            'nao_realizada' => 210240,
        ];

        $codEvento = $eventoMap[$tipo] ?? 210210;

        foreach ($chaves as $chave) {
            try {
                // Chama o serviço para manifestar na SEFAZ
                $this->nfeService->manifestar($chave, $codEvento);

                // Atualiza localmente
                $nota = NotaEntrada::where('chave_acesso', $chave)->first();
                if ($nota) {
                    $nota->manifestacao = $tipo;

                    // Tenta baixar o XML imediatamente após manifestar
                    try {
                        sleep(2); // Pausa para processamento na SEFAZ
                        $doc = $this->nfeService->baixarPorChave($chave);
                        if ($doc && isset($doc['content'])) {
                            $nota->xml_content = $doc['content'];
                            $nota->status = 'processada'; // XML disponível
                        }
                    } catch (\Exception $eDl) {
                        // Ignora erro de download imediato (provavelmente 137 - Não disponível ainda)
                        // O loop de sincronização pegará depois
                        Log::info("Download imediato pós-manifesto falhou (normal): " . $eDl->getMessage());
                    }

                    $nota->save();
                }

                $sucesso++;
            } catch (\Exception $e) {
                // Tratamento automático para erro 596 (Prazo de Ciência expirado)
                if (str_contains($e->getMessage(), '596') && $codEvento == 210210) {
                    try {
                        Log::info("Prazo de ciência expirado para $chave. Tentando Confirmação da Operação (210200).");
                        $this->nfeService->manifestar($chave, 210200);

                        // Atualiza localmente como confirmada
                        $nota = NotaEntrada::where('chave_acesso', $chave)->first();
                        if ($nota) {
                            $nota->manifestacao = 'confirmada'; // Mudou de ciencia para confirmada

                            // Tenta baixar
                            try {
                                sleep(2);
                                $doc = $this->nfeService->baixarPorChave($chave);
                                if ($doc && isset($doc['content'])) {
                                    $nota->xml_content = $doc['content'];
                                    $nota->status = 'processada';
                                }
                            } catch (\Exception $eDl) {
                                Log::info("Download pós-confirmação (fallback) falhou: " . $eDl->getMessage());
                            }
                            $nota->save();
                        }
                        $sucesso++;
                        $erros[] = "Nota $chave: Ciência expirada. Realizada CONFIRMAÇÃO DA OPERAÇÃO automaticamente.";
                        continue; // Sucesso no fallback
                    } catch (\Exception $e2) {
                        Log::error("Erro no fallback de manifestação $chave: " . $e2->getMessage());
                        $erros[] = "Erro na chave $chave (Falha também no fallback): " . $e2->getMessage();
                    }
                } else {
                    Log::error("Erro ao manifestar nota $chave: " . $e->getMessage());
                    $erros[] = "Erro na chave $chave: " . $e->getMessage();
                }
            }
        }

        if (count($erros) > 0) {
            return redirect()->back()->with('warning', "$sucesso notas manifestadas com sucesso. Erros: " . implode('; ', $erros));
        }

        return redirect()->back()->with('success', "$sucesso notas manifestadas como " . strtoupper($tipo) . " com sucesso!");
    }

    // Método para baixar XML individualmente (tentativa manual)
    public function baixarXml($id)
    {
        $nota = NotaEntrada::findOrFail($id);

        try {
            // Verifica se há bloqueio ativo antes de tentar
            $nextQuery = Configuracao::get('nfe_next_dfe_query');
            if ($nextQuery && now()->lt(Carbon::parse($nextQuery))) {
                $diffMinutes = (int) ceil(now()->diffInMinutes(Carbon::parse($nextQuery)));
                return redirect()->back()->with('error', "Sistema em pausa temporária pela SEFAZ. Aguarde {$diffMinutes} minutos.");
            }

            $doc = $this->nfeService->baixarPorChave($nota->chave_acesso);

            if ($doc && isset($doc['content'])) {
                $nota->xml_content = $doc['content'];
                $nota->status = 'processada';
                $nota->save();
                return redirect()->back()->with('success', 'XML baixado e armazenado com sucesso!');
            }

            return redirect()->back()->with('warning', 'O XML ainda não está disponível para download na SEFAZ. (Status 137 ou 138 pendente). Tente novamente mais tarde.');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '656') || str_contains($msg, 'Consumo Indevido')) {
                Configuracao::set('nfe_next_dfe_query', now()->addHour()->toDateTimeString(), 'nfe', 'datetime', 'Próxima consulta DFe permitida');
                return redirect()->back()->with('error', 'A SEFAZ bloqueou temporariamente o download (Consumo Indevido). Aguarde 1 hora.');
            }

            return redirect()->back()->with('error', 'Erro ao baixar XML: ' . $msg);
        }
    }

    // Método para sincronizar manualmente (buscar novas notas na SEFAZ)
    public function sincronizar()
    {
        // Previne execução concorrente (Double-click protection)
        $lock = Cache::lock('nfe_sincronizacao', 10); // 10 segundos de lock inicial

        if (!$lock->get()) {
            return redirect()->back()->with('warning', 'A sincronização já está em andamento. Aguarde alguns segundos.');
        }

        try {
            // Verifica se existe bloqueio de tempo para nova consulta (Consumo Indevido ou Sem Documentos)
            $nextQuery = Configuracao::get('nfe_next_dfe_query');
            if ($nextQuery) {
                $nextQueryDate = Carbon::parse($nextQuery);
                if (now()->lt($nextQueryDate)) {
                    $diffMinutes = (int) ceil(now()->diffInMinutes($nextQueryDate));
                    return redirect()->back()->with('error', "Aguarde {$diffMinutes} minutos para realizar uma nova busca (Regra da SEFAZ para evitar bloqueio).");
                }
            }

            $lastNSU = Configuracao::get('nfe_last_nsu') ?: 0;
            $maxLoops = 10; // Aumentado para 10 loops (500 docs) para recuperar atraso
            $loopCount = 0;
            $newDocsCount = 0;
            $parouPorBloqueio = false;

            do {
                try {
                    $resp = $this->nfeService->consultarNotasDestinadas($lastNSU);
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    if (str_contains($msg, '656') || str_contains($msg, 'Consumo Indevido')) {
                        // Se bloquear no meio do loop, paramos mas salvamos o que já fizemos
                        Configuracao::set('nfe_next_dfe_query', now()->addHour()->toDateTimeString(), 'nfe', 'datetime', 'Próxima consulta DFe permitida');
                        $parouPorBloqueio = true;
                        break;
                    }
                    throw $e; // Outros erros interrompem
                }

                $ultNSU = $resp->ultNSU;
                $maxNSU = $resp->maxNSU;

                // Proteção contra loop infinito se NSU não andar
                if ($ultNSU <= $lastNSU) {
                    break;
                }

                if (isset($resp->loteDistDFeInt->docZip)) {
                    $docs = is_array($resp->loteDistDFeInt->docZip) ? $resp->loteDistDFeInt->docZip : [$resp->loteDistDFeInt->docZip];

                    foreach ($docs as $doc) {
                        try {
                            $nsu = null;
                            $schema = null;
                            $contentEncoded = null;

                            if (is_object($doc)) {
                                $nsu = $doc->NSU ?? null;
                                $schema = $doc->schema ?? null;
                                $contentEncoded = $doc->{'$'} ?? $doc->{0} ?? null;
                                // Fallback para objeto convertido em string
                                if (!$contentEncoded && method_exists($doc, '__toString')) {
                                    $str = (string)$doc;
                                    if (strlen($str) > 20) $contentEncoded = $str;
                                }
                            } elseif (is_array($doc)) {
                                $nsu = $doc['NSU'] ?? null;
                                $schema = $doc['schema'] ?? null;
                                $contentEncoded = $doc['$'] ?? ($doc[0] ?? null);
                            } elseif (is_string($doc)) {
                                $contentEncoded = $doc;
                            }

                            if (!$contentEncoded && (is_object($doc) || is_array($doc))) {
                                foreach ($doc as $key => $value) {
                                    if (is_string($value) && strlen($value) > 20) {
                                        $contentEncoded = $value;
                                        break;
                                    }
                                }
                            }

                            if ($contentEncoded) {
                                $xmlContent = gzdecode(base64_decode($contentEncoded));
                                $resultType = $this->processarDocDFe($nsu, $schema, $xmlContent);

                                if ($resultType === 'new') {
                                    $newDocsCount++;
                                }
                            }
                        } catch (\Exception $eDoc) {
                            Log::error("Erro ao processar documento individual (NSU " . ($nsu ?? 'N/A') . "): " . $eDoc->getMessage());
                            // Continua para o próximo documento
                        }
                    }
                }

                Configuracao::set('nfe_last_nsu', $ultNSU, 'nfe', 'text', 'Último NSU consultado na SEFAZ');
                $lastNSU = $ultNSU;
                $loopCount++;

                if ($ultNSU >= $maxNSU) break;

                sleep(2); // Rate limiting simples
            } while ($loopCount < $maxLoops);

            $msgSuccess = "Sincronização concluída!";
            if ($newDocsCount > 0) {
                $msgSuccess .= " {$newDocsCount} novos documentos importados.";
            } else {
                $msgSuccess .= " Nenhum documento novo encontrado.";
            }

            if ($parouPorBloqueio) {
                $msgSuccess .= " (Atenção: A SEFAZ solicitou pausa nas consultas. Aguarde 1 hora antes de tentar novamente.)";
                return redirect()->back()->with('warning', $msgSuccess);
            }

            return redirect()->back()->with('success', $msgSuccess);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '656') || str_contains($msg, 'Consumo Indevido')) {
                $msg = 'A SEFAZ bloqueou temporariamente as consultas por excesso de tentativas (Consumo Indevido). Por favor, aguarde 1 hora antes de tentar novamente.';
            }
            Log::error("Erro ao sincronizar notas: " . $msg);
            return redirect()->back()->with('error', 'Erro ao sincronizar: ' . $msg);
        } finally {
            $lock->release();
        }
    }

    protected function processarDocDFe($nsu, $schema, $xmlContent)
    {
        $xml = simplexml_load_string($xmlContent);

        if (!$schema || $schema === '') {
            $content = (string) $xmlContent;
            if (strpos($content, '<resNFe') !== false) {
                $schema = 'resNFe_v1.00.xsd';
            } elseif (strpos($content, '<resEvento') !== false) {
                $schema = 'resEvento_v1.00.xsd';
            } else {
                $schema = 'procNFe_v4.00.xsd';
            }
        }

        if (strpos($schema, 'resNFe') !== false) {
            // Resumo da NFe
            $chave = preg_replace('/[^0-9]/', '', (string) $xml->chNFe);
            $cnpj = (string) $xml->CNPJ;
            $nome = (string) $xml->xNome;
            $valor = (float) $xml->vNF;
            $statusSefaz = (int) $xml->cSitNFe;
            $data = (string) $xml->dhEmi;

            $status = 'pendente';
            if ($statusSefaz == 3) $status = 'cancelada';

            $nota = NotaEntrada::updateOrCreate(
                ['chave_acesso' => $chave],
                [
                    'emitente_cnpj' => $cnpj,
                    'emitente_nome' => $nome,
                    'valor_total' => $valor,
                    'data_emissao' => $data,
                    'status' => $status
                ]
            );
            return $nota->wasRecentlyCreated ? 'new' : 'updated';
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

                $nota = NotaEntrada::updateOrCreate(
                    ['chave_acesso' => $chave],
                    [
                        'emitente_cnpj' => (string) $emit->CNPJ,
                        'emitente_nome' => (string) $emit->xNome,
                        'valor_total' => (float) $total->vNF,
                        'data_emissao' => (string) $infNFe->ide->dhEmi,
                        'xml_content' => $xmlContent,
                        'status' => 'downloaded' // Status indica que temos o XML
                    ]
                );
                return $nota->wasRecentlyCreated ? 'new' : 'updated';
            }
        } elseif (strpos($schema, 'resEvento') !== false) {
            // Evento
            $chave = (string) $xml->chNFe;
            $tpEvento = (string) $xml->tpEvento;

            if ($tpEvento == '110111') {
                NotaEntrada::where('chave_acesso', $chave)->update(['status' => 'cancelada']);
                return 'updated';
            } else {
                Log::info("ManifestoController: Evento ignorado (NSU: $nsu, Schema: $schema, Tipo: $tpEvento, Chave: $chave)");
            }
        } else {
            Log::info("ManifestoController: Schema desconhecido ou ignorado (NSU: $nsu, Schema: $schema)");
        }
        return 'ignored';
    }
}
