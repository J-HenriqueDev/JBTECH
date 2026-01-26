<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NotaEntrada;

echo "Verificando notas com XML invalido (resNFe salvo como xml_content)...\n";

$notas = NotaEntrada::whereNotNull('xml_content')->get();
$count = 0;

foreach ($notas as $nota) {
    if (strpos($nota->xml_content, '<resNFe') !== false) {
        echo "Corrigindo nota: " . $nota->chave_acesso . "\n";
        $nota->update([
            'xml_content' => null,
            'status' => 'detectada' // Volta para detectada para o robô tentar baixar de novo
        ]);
        $count++;
    }
}

echo "Total corrigidas: $count\n";
