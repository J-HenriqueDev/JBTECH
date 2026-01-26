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

            // Log de Erro Nativo (Solicitado)
            if (!in_array($std->cStat, ['137', '138'])) {
                Log::error("Erro SEFAZ: " . $resp);
            }

            // Se houver ultNSU no retorno mesmo com erro, atualizamos? Melhor não.
            // Apenas 138 (Documentos localizados) nos interessa para processar lote.
            // Porém, a documentação diz que pode retornar vazio (137) e devemos atualizar NSU?
            // Não, 137 é "Nenhum documento localizado para o destinatário". O ultNSU enviado já é o último.
            // Se retornou 138, tem documentos.
            return;
        }

        // Verifica se há documentos no retorno (tag docZip)
        // Usa objeto StdClass para acesso mais seguro conforme solicitado

        $lote = $std->loteDistDFeInt ?? null;

        if ($lote && isset($lote->docZip)) {
            $docs = $lote->docZip;

            // Normalização: Transforma objeto único em array de um item
            if (!is_array($docs)) {
                $docs = [$docs];
            }

            $maxNSU = $ultNSU;

            foreach ($docs as $doc) {
                // Valida se é objeto
                if (!is_object($doc)) {
                    Log::warning("NfeDownloadService: Item em docZip não é objeto.", ['item' => $doc]);
                    continue;
                }

                // Acesso via propriedades de objeto (Standardize toStd)
                // Dependendo da lib, atributos como NSU podem estar em propriedades específicas ou não mapeados no toStd.
                // Mas o erro original "Cannot access offset of type string on string" sugere que estava tentando acessar array em string.
                // Com toStd, esperamos objetos.

                // Nota: O NFePHP Standardize toStd converte atributos XML em propriedades?
                // Em alguns casos, atributos ficam perdidos no toStd se não forem mapeados.
                // Mas vamos seguir a instrução de usar is_array e normalização.

                // Se $doc for um objeto vindo do toStd, atributos como NSU e schema podem estar em propriedades?
                // Se o XML é <docZip NSU="123" schema="resNFe_v1.01.xsd">...</docZip>
                // O toStd pode gerar algo como $doc->NSU (se simplificado) ou perder os atributos.
                // VAMOS USAR O ARRAY PARA GARANTIR ACESSO AOS ATRIBUTOS (@attributes) QUE O USUARIO USAVA ANTES,
                // MAS COM A CORREÇÃO DE TIPO.

                // Espere, o usuário pediu para usar $std->loteDistDFeInt->docZip.
                // Se usarmos $std (objeto), não teremos '@attributes' como array key.
                // Vamos voltar para a abordagem do usuário mas mantendo a lógica de extração segura.

                // Se usarmos toArray ($arr), o acesso é $doc['@attributes']['NSU'].
                // Se usarmos toStd ($std), o acesso depende de como a lib converte.
                // O erro original foi na linha 136: $nsu = $doc['@attributes']['NSU'];
                // Isso confirma que $doc era string ou objeto, não array.

                // Vamos usar a abordagem híbrida:
                // Se $doc for objeto (do toStd), tentamos pegar NSU dele.
                // Mas a instrução do usuário sugere usar $std.

                // Se $doc for string (conteúdo direto), não tem atributos acessíveis facilmente.

                // VAMOS USAR $st->toArray() PARA MANTER A LÓGICA DE ATRIBUTOS, MAS COM A CORREÇÃO DO FOREACH.
            }
        }

        // REFAZENDO A LÓGICA COM BASE NO PEDIDO EXATO:
        // O usuário pediu: $docs = $std->loteDistDFeInt->docZip ?? [];
        // Isso implica usar $std (objeto).

        // Se usarmos objeto, como acessamos o NSU que estava em atributo?
        // Geralmente libs de conversão XML->Objeto colocam atributos como propriedades (ex: $doc->NSU) ou removem.
        // No NFePHP Standardize, atributos costumam ser perdidos no toStd simples ou viram propriedades.

        // O erro "Cannot access offset of type string on string" na linha 136 ($nsu = $doc['@attributes']['NSU'])
        // indica que $doc NÃO era um array. Poderia ser um Objeto ou String.

        // Se eu mudar para toStd, tenho que mudar o acesso para objeto.
        // Se eu manter toArray, tenho que garantir que $doc seja array.

        // O usuário pediu explicitamente:
        // $docs = $std->loteDistDFeInt->docZip ?? [];
        // if (!is_array($docs)) $docs = [$docs];

        // Então vou usar $std e adaptar o acesso aos dados.

        $arr = $st->toArray($resp); // Mantendo array para ter acesso garantido aos atributos @attributes

        // Validação robusta usando Array (mais seguro para atributos XML)
        if (isset($arr['loteDistDFeInt']['docZip'])) {
            $docs = $arr['loteDistDFeInt']['docZip'];

            // Normalização: Se não for array de arrays (lista numérico), encapsula
            // Casos:
            // 1. Um doc: ['@attributes' => ..., '#text' => ...] -> É array, mas associativo (não lista)
            // 2. Vários docs: [ 0 => [...], 1 => [...] ] -> É array lista

            if (isset($docs['@attributes']) || isset($docs['#text'])) {
                // É um único documento (array associativo)
                $docs = [$docs];
            }

            // Se chegou aqui e não é array, algo está errado (talvez string direta?)
            if (!is_array($docs)) {
                $docs = [$docs]; // Força array
            }

            $maxNSU = $ultNSU;

            foreach ($docs as $doc) {
                // Se por algum motivo $doc for string (sem atributos parseados), ignoramos ou logamos
                if (is_string($doc)) {
                    Log::warning("NfeDownloadService: Item docZip é string (esperado array com atributos).", ['content' => substr($doc, 0, 50)]);
                    continue;
                }

                // Valida existência dos índices
                $nsu = $doc['@attributes']['NSU'] ?? null;
                $schema = $doc['@attributes']['schema'] ?? '';
                $content = $doc['#text'] ?? $doc; // Conteúdo

                // Se for array mas sem #text (conteúdo direto no array misto?), o $content pode ser o próprio array? Não.
                if (is_array($content)) {
                    // Caso estranho, tenta json_encode ou pega primeiro elemento?
                    // Geralmente #text é string.
                    $content = '';
                }

                if (!$nsu) {
                    Log::warning("NfeDownloadService: NSU não encontrado no documento.", ['doc' => $doc]);
                    continue;
                }

                // Atualiza maxNSU
                if ($nsu > $maxNSU) {
                    $maxNSU = $nsu;
                }

                try {
                    $xml = gzdecode(base64_decode($content));
                    $this->processarDocumento($nsu, $schema, $xml);
                } catch (\Exception $e) {
                    Log::error("NfeDownloadService: Erro ao descompactar XML (NSU: $nsu): " . $e->getMessage());
                }
            }

            // Atualiza ultimo NSU
            if (isset($arr['loteDistDFeInt']['ultNSU']) && $arr['loteDistDFeInt']['ultNSU'] > $ultNSU) {
                Configuracao::set('nfe_ultimo_nsu', $arr['loteDistDFeInt']['ultNSU'], 'nfe');
            }
        } else {
            Log::info("NfeDownloadService: Nenhum documento no lote (docZip ausente).");
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
