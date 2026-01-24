<?php

use App\Models\NotaFiscalServico;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nfse = NotaFiscalServico::find(1);
if ($nfse) {
    $nfse->codigo_servico = '14.01';
    $nfse->save();
    echo "Código de serviço resetado para 14.01\n";
} else {
    echo "NFS-e não encontrada.\n";
}
