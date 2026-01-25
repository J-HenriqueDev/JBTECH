<?php
$nota = \App\Models\NotaEntrada::where('chave_acesso', '35260135300311000150550030002063241438979105')->first();
if ($nota) {
    echo "Manifestacao: " . $nota->manifestacao . "\n";
    echo "Status: " . $nota->status . "\n";
    echo "XML Content Length: " . ($nota->xml_content ? strlen($nota->xml_content) : 'NULL') . "\n";
} else {
    echo "Nota not found.\n";
}
