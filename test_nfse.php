<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NFSeService;
use App\Models\NotaFiscalServico;
use Illuminate\Support\Facades\Log;

echo "Iniciando teste de emissão NFS-e...\n";

try {
    $nfse = NotaFiscalServico::find(7814);
if (!$nfse) {
    die("NFS-e ID 7814 não encontrada.\n");
}
    $nfse->id = rand(1000, 9999); // Force new ID/Number
    $nfse->serie_rps = '900'; // Force series 900
    // $nfse->aliquota_iss = 0.00; // Ensure 0 to avoid E0600
    $nfse->data_emissao = now();
    $service = new NFSeService();
    echo "Service instanciado.\n";

    $resultado = $service->emitir($nfse);

    echo "Resultado:\n";
    print_r($resultado);

} catch (Exception $e) {
    echo "Exceção capturada:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
