<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NotaEntrada;

$chaves = [
    '35260135300311000150550030002063241438979105',
    '35260144017677000108550020003181271404673560',
    '35260141992134000113550020001707281760315307',
    '35260112316229000119550020002130661767219706'
];

echo "Verificando e corrigindo notas...\n";

foreach ($chaves as $chave) {
    $nota = NotaEntrada::where('chave_acesso', $chave)->first();
    if ($nota) {
        echo "Nota $chave:\n";
        echo "  - Status atual: " . $nota->status . "\n";
        echo "  - XML Content Len: " . ($nota->xml_content ? strlen($nota->xml_content) : 'NULL') . "\n";
        
        if ($nota->xml_content) {
            $preview = substr($nota->xml_content, 0, 100);
            echo "  - XML Preview: " . htmlspecialchars($preview) . "\n";
            
            if (strpos($nota->xml_content, '<resNFe') !== false) {
                echo "  -> DETECTADO XML INVALIDO (resNFe). Limpando...\n";
                $nota->xml_content = null;
                $nota->status = 'detectada';
                $nota->save();
                echo "  -> Corrigido.\n";
            } else {
                echo "  -> XML parece ok (nao comeca com resNFe).\n";
            }
        } else {
            echo "  -> XML ja esta vazio.\n";
        }
    } else {
        echo "Nota $chave nao encontrada.\n";
    }
    echo "----------------------------------------\n";
}
