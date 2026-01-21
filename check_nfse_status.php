try {
$n = App\Models\NotaFiscalServico::latest()->first();
if ($n) {
echo "ID: " . $n->id . "\n";
echo "Status: " . $n->status . "\n";
echo "Motivo Rejeicao: " . $n->motivo_rejeicao . "\n";
echo "XML Retorno: " . $n->xml_retorno . "\n";
} else {
echo "Nenhuma NFS-e encontrada.\n";
}
} catch (\Exception $e) {
echo "Erro: " . $e->getMessage() . "\n";
}