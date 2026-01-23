<?php

use App\Models\NotaEntrada;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Total de Notas no Banco: " . NotaEntrada::count() . "\n";
echo "Notas recentes (últimas 5):\n";

$notas = NotaEntrada::orderBy('updated_at', 'desc')->take(5)->get();

foreach ($notas as $nota) {
    echo "ID: {$nota->id} | Chave: " . substr($nota->chave_acesso, 0, 20) . "... | Status: {$nota->status} | Updated: {$nota->updated_at} | Data Emissao: {$nota->data_emissao}\n";
}
 
        