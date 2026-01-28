<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotaEntrada;
use App\Models\Configuracao;
use Carbon\Carbon;

class SefazHealthController extends Controller
{
    /**
     * Retorna o status de saúde do módulo fiscal (SEFAZ).
     * Pode retornar JSON (para API/AJAX) ou View (se implementada futuramente).
     */
    public function index(Request $request)
    {
        $data = self::getHealthData();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($data);
        }

        return view('content.fiscal.health', ['health' => $data]);
    }

    /**
     * Centraliza a lógica de cálculo dos indicadores de saúde.
     */
    public static function getHealthData()
    {
        $sonecaMinutos = Configuracao::getTempoRestanteSoneca();
        
        $notasDetectadas = NotaEntrada::whereMonth('created_at', now()->month)->count();
        $xmlsCompletos = NotaEntrada::whereNotNull('xml_content')
            ->whereMonth('created_at', now()->month)
            ->count();
        $processamentoPendente = NotaEntrada::where('status', 'pendente')->count();

        // Lógica de mensagem de bloqueio (similar ao que estava no NotaEntradaController)
        $status = $sonecaMinutos > 0 ? 'soneca' : 'ativo';
        $statusTitle = $sonecaMinutos > 0 ? '💤 Modo Soneca Ativo' : '🚀 Motor Fiscal Ativo';
        $statusMessage = $sonecaMinutos > 0 
            ? "O robô está em repouso tático para evitar bloqueios. Retorno em {$sonecaMinutos} min." 
            : "Sincronização em tempo real liberada.";

        return [
            'sonecaMinutos' => $sonecaMinutos,
            'notasDetectadas' => $notasDetectadas,
            'xmlsCompletos' => $xmlsCompletos,
            'processamentoPendente' => $processamentoPendente,
            'status' => $status,
            'statusTitle' => $statusTitle,
            'statusMessage' => $statusMessage,
            'timestamp' => now()->toIso8601String()
        ];
    }
}
