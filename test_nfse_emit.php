<?php

use App\Models\NotaFiscalServico;
use App\Services\NFSeService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nfse = NotaFiscalServico::latest()->first();
echo "Tentando emitir NFS-e ID: " . $nfse->id . "\n";
echo "Status atual: " . $nfse->status . "\n";

try {
    $service = new NFSeService();
    $result = $service->emitir($nfse);
    print_r($result);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
