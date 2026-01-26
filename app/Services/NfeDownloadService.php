<?php

namespace App\Services;

use App\Models\Configuracao;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use Exception;
use Illuminate\Support\Facades\DB;

class NfeDownloadService
{
    protected $config;
    protected $certificate;
    protected $tools;

    public function __construct()
    {
        // 1. Carregar Configurações (Mesma lógica do NFeService)
        $this->config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => (int) Configuracao::get('nfe_ambiente') ?: 2,
            'razaosocial' => Configuracao::get('nfe_razao_social') ?: Configuracao::get('empresa_nome') ?: 'RAZÃO SOCIAL NÃO CONFIGURADA',
            'siglaUF' => Configuracao::get('empresa_uf') ?: 'RJ',
            'cnpj' => Configuracao::get('nfe_cnpj') ?: Configuracao::get('empresa_cnpj') ?: '',
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'CSC' => Configuracao::get('nfe_csc') ?: '',
            'CSCid' => Configuracao::get('nfe_csc_id') ?: '',
        ];

        try {
            $this->loadCertificate();
        } catch (Exception $e) {
            Log::error('NfeDownloadService: Erro ao carregar certificado: ' . $e->getMessage());
            // Não lança exceção aqui para permitir instanciar o serviço, mas métodos falharão se tools for null
        }
    }

    protected function loadCertificate()
    {
        $certPathConfig = Configuracao::get('nfe_cert_path');
        $certPath = storage_path('app/certificates/' . ($certPathConfig ?: 'certificado.pfx'));
        $certPassword = Configuracao::get('nfe_cert_password');

        if (!file_exists($certPath)) {
            // Tenta restaurar do banco (Lógica existente no NFeService)
            $certData = Configuracao::get('nfe_cert_data');
            if ($certData) {
                if (!file_exists(dirname($certPath))) {
                    mkdir(dirname($certPath), 0755, true);
                }
                file_put_contents($certPath, base64_decode($certData));
            } else {
                throw new Exception("Certificado não encontrado em {$certPath}");
            }
        }

        if (empty($certPassword)) {
            throw new Exception("Senha do certificado não configurada");
        }

        $certContent = file_get_contents($certPath);
        $this->certificate = Certificate::readPfx($certContent, $certPassword);
        $this->tools = new Tools(json_encode($this->config), $this->certificate);
    }

    public function executarSincronizacao()
    {
        if (!$this->tools) {
            Log::error('NfeDownloadService: Tools não inicializado (Verifique certificado).');
            return;
        }

        $ultNSU = Configuracao::get('nfe_ultimo_nsu') ?: '0';
        Log::info("Iniciando busca DFe a partir do NSU: {$ultNSU}");

        try {
            // Executa a busca
            $resp = $this->tools->sefazDistDFe($ultNSU);
        } catch (Exception $e) {
            Log::error("Erro ao comunicar com SEFAZ DistDFe: " . $e->getMessage());
            return;
        }

        $st = new Standardize();
        $std = $st->toStd($resp);

        // Tratamento de Limites (Regras 2026)
        if (in_array($std->cStat, ['137', '656'])) {
            Log::warning("NfeDownloadService: Parada solicitada pela SEFAZ. cStat: {$std->cStat} - {$std->xMotivo}");
            return;
        }

        if ($std->cStat != '138') {
            Log::info("NfeDownloadService: Retorno não processável. cStat: {$std->cStat} - {$std->xMotivo}");
            // Se houver ultNSU no retorno mesmo com erro, atualizamos? Melhor não.
            // Apenas 138 (Documentos localizados) nos interessa para processar lote.
            // Porém, a documentação diz que pode retornar vazio (137) e devemos atualizar NSU?
            // Não, 137 é "Nenhum documento localizado para o destinatário". O ultNSU enviado já é o último.
            // Se retornou 138, tem documentos.

            // Nota: DistDFe pode retornar documentos zipados.
        }

        // Verifica se há documentos no retorno (tag docZip)
        // O response padronizado coloca docZip como array? Depende da lib.
        // Vamos verificar a estrutura do XML ou array.

        // A lib NFePHP retorna docZip como array de strings base64 se usarmos toStd?
        // Vamos checar como 'loteDistDFeInt' retorna.

        // Melhor usar o DOM ou array para iterar
        $arr = $st->toArray($resp);

        if (isset($arr['loteDistDFeInt']['docZip'])) {
            $docs = $arr['loteDistDFeInt']['docZip'];
            // Se for um único documento, pode não ser array de arrays. Normalizar.
            if (isset($docs['@attributes'])) { // Único item
                $docs = [$docs];
            } elseif (isset($docs[0]) && !is_array($docs[0])) {
                // Pode ser array de strings se não tiver atributos?
                // O docZip tem atributos NSU e schema.
                // Normalmente o NFePHP toArray estrutura bem.
            }

            // Garantir que seja iterável
            $listaDocs = isset($docs[0]) ? $docs : [$docs];

            $maxNSU = $ultNSU;

            foreach ($listaDocs as $doc) {
                $nsu = $doc['@attributes']['NSU'];
                $schema = $doc['@attributes']['schema'];
                $content = $doc['#text'] ?? $doc; // Conteúdo GZip Base64

                // Atualiza maxNSU
                if ($nsu > $maxNSU) {
                    $maxNSU = $nsu;
                }

                $xml = gzdecode(base64_decode($content));

                $this->processarDocumento($nsu, $schema, $xml);
            }

            // Atualiza ultimo NSU
            if ($arr['loteDistDFeInt']['ultNSU'] > $ultNSU) {
                Configuracao::set('nfe_ultimo_nsu', $arr['loteDistDFeInt']['ultNSU'], 'nfe');
            }
        } else {
            Log::info("NfeDownloadService: Nenhum documento no lote (mas cStat indicou sucesso?).");
            // Se tem ultNSU novo, atualiza
            if (isset($arr['loteDistDFeInt']['ultNSU']) && $arr['loteDistDFeInt']['ultNSU'] > $ultNSU) {
                Configuracao::set('nfe_ultimo_nsu', $arr['loteDistDFeInt']['ultNSU'], 'nfe');
            }
        }
    }

    protected function processarDocumento($nsu, $schema, $xml)
    {
        $st = new Standardize();

        // Identifica o tipo pelo schema ou conteúdo
        if (strpos($schema, 'resNFe') !== false) {
            // Resumo
            $stdRes = $st->toStd($xml);
            $chave = $stdRes->chNFe;
            $cnpjEmit = $stdRes->CNPJ;
            $nomeEmit = $stdRes->xNome;
            $dataEmissao = $stdRes->dhEmi;

            Log::info("Processando Resumo: {$chave} (NSU: {$nsu})");

            // Salva no banco
            $exists = DB::table('nfe_recebidas')->where('chave', $chave)->exists();

            if (!$exists) {
                DB::table('nfe_recebidas')->insert([
                    'chave' => $chave,
                    'nsu' => $nsu,
                    'status' => 'resumo',
                    'data_emissao' => $dataEmissao,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Manifesta Ciência
                $this->manifestarCiencia($chave);
            }
        } elseif (strpos($schema, 'procNFe') !== false) {
            // XML Completo
            // O XML pode vir envelopado em nfeProc ou apenas NFe dependendo.
            // procNFe geralmente é o completo (NFe + Protocolo).

            $stdProc = $st->toStd($xml);
            // Extrair chave. Se for procNFe, a chave está em NFe->infNFe->Id (com NFe) ou protNFe->infProt->chNFe
            $chave = null;
            if (isset($stdProc->protNFe->infProt->chNFe)) {
                $chave = $stdProc->protNFe->infProt->chNFe;
            } elseif (isset($stdProc->NFe->infNFe->Id)) {
                $chave = str_replace('NFe', '', $stdProc->NFe->infNFe->Id);
            }

            if ($chave) {
                Log::info("Processando XML Completo: {$chave} (NSU: {$nsu})");

                // Salva no Banco (Heroku Friendly)
                DB::table('nfe_recebidas')->updateOrInsert(
                    ['chave' => $chave],
                    [
                        'nsu' => $nsu,
                        'status' => 'concluido',
                        'xml_content' => $xml, // Salva o XML diretamente
                        'updated_at' => now()
                    ]
                );
            }
        }
    }

    protected function manifestarCiencia($chave)
    {
        try {
            $retorno = $this->tools->sefazManifesta($chave, '210210');
            $st = new Standardize();
            $std = $st->toStd($retorno);

            if ($std->cStat == '128') { // Lote processado
                // Verifica o evento individual
                $retEvento = $std->retEvento;
                if (is_array($retEvento)) $retEvento = $retEvento[0]; // Se houver multiplos (nao deve ocorrer aqui)

                if ($retEvento->infEvento->cStat == '135' || $retEvento->infEvento->cStat == '136') {
                    Log::info("Manifestação Ciência Sucesso: {$chave}");
                    DB::table('nfe_recebidas')->where('chave', $chave)->update(['status' => 'manifestado']);
                } else {
                    Log::warning("Erro ao manifestar ciência {$chave}: {$retEvento->infEvento->cStat} - {$retEvento->infEvento->xMotivo}");
                }
            } else {
                Log::warning("Erro lote manifestação {$chave}: {$std->cStat} - {$std->xMotivo}");
            }
        } catch (Exception $e) {
            Log::error("Exceção ao manifestar ciência {$chave}: " . $e->getMessage());
        }
    }
}
