<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$chave = '35260135300311000150550030002063241438979105';
$nota = App\Models\NotaEntrada::where('chave_acesso', $chave)->first();

if (!$nota) {
    echo "Nota nao encontrada.\n";
    exit;
}

if (!$nota->xml_content) {
    echo "Nota sem xml_content.\n";
    exit;
}

echo "XML Length: " . strlen($nota->xml_content) . "\n";
echo "Preview (first 500 chars):\n";
echo substr($nota->xml_content, 0, 500) . "\n\n";

$xml = simplexml_load_string($nota->xml_content);
if ($xml) {
    echo "Root Element: " . $xml->getName() . "\n";
    echo "Namespaces: " . implode(', ', $xml->getNamespaces(true)) . "\n";
    
    if (isset($xml->NFe->infNFe)) echo "Has NFe->infNFe\n";
    if (isset($xml->infNFe)) echo "Has infNFe\n";
} else {
    echo "Failed to load XML with simplexml_load_string\n";
}
