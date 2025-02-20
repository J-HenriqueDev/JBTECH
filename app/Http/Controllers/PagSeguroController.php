<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PagSeguro\Configuration\Configure;
use PagSeguro\Library;
use App\Models\Venda;

class PagSeguroController extends Controller
{
    public function notification(Request $request)
    {
        // Configura o ambiente do PagSeguro
        Library::initialize();
        $env = env('PAGSEGURO_ENV', 'sandbox'); // 'sandbox' ou 'production'
        Configure::setEnvironment($env);
        Configure::setAccountCredentials(env('PAGSEGURO_EMAIL'), env('PAGSEGURO_TOKEN'));

        // Obtém o código da notificação
        $notificationCode = $request->input('notificationCode');

        try {
            // Consulta a transação no PagSeguro
            $transaction = \PagSeguro\Services\Transactions\Notification::check(
                Configure::getAccountCredentials(),
                $notificationCode
            );

            // Atualiza o status da venda
            $venda = Venda::where('reference', $transaction->getReference())->first();
            if ($venda) {
                $venda->status = $transaction->getStatus();
                $venda->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
