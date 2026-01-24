<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nfse = \App\Models\NotaFiscalServico::find(1);
if ($nfse) {
    $nfse->codigo_servico = '14.01.01'; // Trying a valid subitem/desdobro
    $nfse->save();
    echo "NFS-e ID 1 updated to codigo_servico 14.01.01\n";
} else {
    echo "NFS-e ID 1 not found\n";
}
