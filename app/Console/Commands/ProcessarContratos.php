<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contrato;
use App\Models\Cobranca;
use App\Models\NotaFiscalServico;
use App\Services\NFSeService;
use App\Mail\NFSeContratoEnviada;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Configuracao;

class ProcessarContratos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contratos:processar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa contratos ativos, gera cobranças e emite NFS-e automaticamente';

    protected $nfseService;

    public function __construct(NFSeService $nfseService)
    {
        parent::__construct();
        $this->nfseService = $nfseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando processamento de contratos...');

        // Configuração de dias de antecedência (default: 7 dias)
        $diasAntecedencia = (int) Configuracao::get('contratos_dias_antecedencia', 7);
        $targetDate = now()->addDays($diasAntecedencia);

        $this->info("Buscando contratos com vencimento até: " . $targetDate->format('d/m/Y'));

        // Busca contratos que vencem na data alvo (ou antes, se houve falha anterior)
        // A comparação deve ser exata ou range?
        // Se rodar todo dia, whereDate <= targetDate vai pegar.
        // Mas precisamos garantir que não gere duplicado para o mesmo mês/período.
        // O campo proximo_faturamento é atualizado ao final, então contratos já processados terão data futura.
        $contratos = Contrato::where('ativo', true)
            ->whereDate('proximo_faturamento', '<=', $targetDate)
            ->get();

        $count = 0;

        foreach ($contratos as $contrato) {
            $this->info("Processando contrato #{$contrato->id} - Cliente: {$contrato->cliente->nome}");

            // Verifica se é contrato parcelado e já finalizou
            if ($contrato->tipo === 'parcelado' && $contrato->parcela_atual > $contrato->qtd_parcelas) {
                $contrato->ativo = false;
                $contrato->save();
                continue;
            }

            DB::beginTransaction();

            try {
                // Calcula o valor da cobrança (divisão se houver múltiplos dias personalizados)
                $valorCobranca = $contrato->valor;
                if ($contrato->dias_personalizados) {
                    $dias = array_map('trim', explode(',', $contrato->dias_personalizados));
                    $qtdDias = count(array_filter($dias));
                    if ($qtdDias > 1) {
                        $valorCobranca = $contrato->valor / $qtdDias;
                    }
                }

                // 1. Cria a Cobrança
                $cobranca = Cobranca::create([
                    'contrato_id' => $contrato->id,
                    'venda_id' => null,
                    'cliente_id' => $contrato->cliente_id,
                    'metodo_pagamento' => $contrato->forma_pagamento ?? 'boleto_pix',
                    'status' => 'pendente',
                    'valor' => $valorCobranca,
                    'data_vencimento' => $contrato->proximo_faturamento, // Vencimento real
                    'recorrente' => true,
                    'frequencia_recorrencia' => $contrato->frequencia,
                    'proxima_cobranca' => $contrato->calcularProximoFaturamento($contrato->proximo_faturamento),
                    'enviar_email' => true,
                ]);

                // Gera dados de pagamento
                // PIX (apenas se metodo incluir pix)
                if (in_array($cobranca->metodo_pagamento, ['pix', 'boleto_pix'])) {
                    $chavePix = config('app.pix_chave', 'financeiro@jbtech.com.br');
                    $valorFmt = number_format($cobranca->valor, 2, '.', '');

                    $descCobranca = "Contrato #{$contrato->id}";
                    if ($contrato->tipo === 'parcelado') {
                        $descCobranca .= " - Parc {$contrato->parcela_atual}/{$contrato->qtd_parcelas}";
                    } else {
                        $descCobranca .= " - Fat #{$cobranca->id}";
                    }
                    $descPix = substr($descCobranca, 0, 25); // Limitando tamanho

                    $cobranca->codigo_pix = "00020126{$chavePix}52040000530398654{$valorFmt}5802BR59{$descPix}6001S62070503***6304";
                }

                // Boleto (apenas se metodo incluir boleto)
                if (in_array($cobranca->metodo_pagamento, ['boleto', 'boleto_pix'])) {
                    $cobranca->link_boleto = route('cobrancas.show', ['cobranca' => $cobranca->id]); // Link para visualizar no sistema por enquanto
                }

                $cobranca->save();

                // 2. Cria a NFS-e (Draft)
                $valorIss = ($valorCobranca * ($contrato->aliquota_iss ?? 0)) / 100;

                $discriminacao = $contrato->discriminacao_servico ?? $contrato->descricao;
                if ($contrato->tipo === 'parcelado') {
                    $discriminacao .= " - Parcela {$contrato->parcela_atual}/{$contrato->qtd_parcelas}";
                } elseif (isset($qtdDias) && $qtdDias > 1) {
                    $discriminacao .= " - Parcela (Pagamento Dividido)";
                }

                $nfse = new NotaFiscalServico();
                $nfse->cliente_id = $contrato->cliente_id;
                $nfse->user_id = 1; // Sistema ou Admin
                $nfse->valor_servico = $valorCobranca;
                $nfse->valor_iss = $valorIss;
                $nfse->aliquota_iss = $contrato->aliquota_iss ?? 0;
                $nfse->iss_retido = $contrato->iss_retido ?? false;
                $nfse->valor_total = $valorCobranca; // Ajustar se tiver retenção
                $nfse->discriminacao = $discriminacao;
                $nfse->codigo_servico = $contrato->codigo_servico;
                $nfse->codigo_nbs = $contrato->codigo_nbs;
                // $nfse->municipio_prestacao = ...; // Usar do cliente ou config
                $nfse->status = 'pendente';
                $nfse->save();

                // 3. Tenta Emitir NFS-e
                $pdfContent = null;
                try {
                    // Verifica requisitos mínimos para emissão
                    if ($nfse->codigo_servico && $contrato->cliente->cpf_cnpj) {
                        $resultado = $this->nfseService->emitir($nfse);

                        if ($resultado['status']) {
                            $this->info("NFS-e emitida com sucesso: " . ($resultado['message'] ?? ''));

                            // Tenta baixar PDF se autorizada imediatamente (síncrono)
                            if ($nfse->status == 'autorizada' && $nfse->chave_acesso) {
                                try {
                                    $pdfContent = $this->nfseService->downloadPdf($nfse);
                                } catch (\Exception $e) {
                                    Log::warning("Contrato #{$contrato->id}: Falha ao baixar PDF após emissão: " . $e->getMessage());
                                }
                            }
                        } else {
                            $this->error("Erro ao emitir NFS-e: " . ($resultado['message'] ?? 'Unknown error'));
                        }
                    } else {
                        $this->warn("Dados incompletos para emissão automática de NFS-e (Código Serviço ou CPF/CNPJ).");
                    }
                } catch (\Exception $e) {
                    $this->error("Exceção ao emitir NFS-e: " . $e->getMessage());
                    Log::error("Contrato #{$contrato->id}: Erro emissão NFS-e: " . $e->getMessage());
                }

                // 4. Enviar E-mail
                if ($contrato->cliente->email) {
                    try {
                        Mail::to($contrato->cliente->email)
                            ->send(new NFSeContratoEnviada($nfse, $cobranca, $pdfContent));
                        $this->info("Email enviado para {$contrato->cliente->email}");
                        $cobranca->update(['enviar_email' => true]); // Confirma envio
                    } catch (\Exception $e) {
                        $this->error("Erro ao enviar email: " . $e->getMessage());
                        Log::error("Contrato #{$contrato->id}: Erro envio email: " . $e->getMessage());
                    }
                }

                // 5. Atualizar Contrato
                $contrato->ultimo_faturamento = $contrato->proximo_faturamento;
                $contrato->proximo_faturamento = $contrato->calcularProximoFaturamento($contrato->proximo_faturamento);

                if ($contrato->tipo === 'parcelado') {
                    $contrato->parcela_atual++;
                    if ($contrato->parcela_atual > $contrato->qtd_parcelas) {
                        $contrato->ativo = false;
                        $contrato->proximo_faturamento = null;
                    }
                }

                $contrato->save();

                DB::commit();
                $count++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Falha ao processar contrato #{$contrato->id}: " . $e->getMessage());
                Log::error("Contrato #{$contrato->id}: Falha geral processamento: " . $e->getMessage());
            }
        }

        $this->info("Processamento concluído. {$count} contratos processados.");
    }
}
