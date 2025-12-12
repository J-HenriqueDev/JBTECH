<?php

namespace App\Http\Controllers\Api\PDV;

use App\Http\Controllers\Controller;
use App\Models\Caixa;
use App\Models\Sangria;
use App\Models\Suprimento;
use App\Helpers\PDVHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaixaController extends Controller
{
    public function status(Request $request)
    {
        $operadorId = PDVHelper::getOperadorId($request);
        
        if (!$operadorId) {
            return response()->json([
                'success' => false,
                'message' => 'Operador não autenticado'
            ], 401);
        }

        $caixaAberto = Caixa::where('status', 'aberto')
            ->where('user_id', $operadorId)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'caixa_aberto' => $caixaAberto !== null,
                'caixa' => $caixaAberto,
            ]
        ]);
    }

    public function abrir(Request $request)
    {
        $validated = $request->validate([
            'valor_abertura' => 'required|numeric|min:0',
            'observacoes' => 'nullable|string',
        ]);

        // Verifica se já existe caixa aberto
        $caixaAberto = Caixa::where('status', 'aberto')
            ->where('user_id', $request->user()->id)
            ->first();

        if ($caixaAberto) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um caixa aberto'
            ], 400);
        }

        $operadorId = PDVHelper::getOperadorId($request);
        
        if (!$operadorId) {
            return response()->json([
                'success' => false,
                'message' => 'Operador não autenticado'
            ], 401);
        }

        $caixa = Caixa::create([
            'user_id' => $operadorId,
            'data_abertura' => now()->toDateString(),
            'hora_abertura' => now()->toTimeString(),
            'valor_abertura' => $validated['valor_abertura'],
            'observacoes' => $validated['observacoes'] ?? null,
            'status' => 'aberto',
        ]);

        return response()->json([
            'success' => true,
            'data' => $caixa,
            'message' => 'Caixa aberto com sucesso'
        ]);
    }

    public function fechar(Request $request, $id)
    {
        $validated = $request->validate([
            'valor_fechamento' => 'required|numeric|min:0',
            'observacoes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $caixa = Caixa::findOrFail($id);

            if ($caixa->status !== 'aberto') {
                return response()->json([
                    'success' => false,
                    'message' => 'Caixa não está aberto'
                ], 400);
            }

            $operadorId = PDVHelper::getOperadorId($request);
            
            if (!$operadorId || $caixa->user_id !== $operadorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para fechar este caixa'
                ], 403);
            }

            // Calcula valor esperado
            $caixa->calcularValorEsperado();

            // Fecha o caixa
            $caixa->valor_fechamento = $validated['valor_fechamento'];
            $caixa->diferenca = $validated['valor_fechamento'] - $caixa->valor_esperado;
            $caixa->data_fechamento = now()->toDateString();
            $caixa->hora_fechamento = now()->toTimeString();
            $caixa->status = 'fechado';
            $caixa->observacoes = $validated['observacoes'] ?? $caixa->observacoes;
            $caixa->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $caixa->load(['vendas', 'sangrias', 'suprimentos']),
                'message' => 'Caixa fechado com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sangria(Request $request)
    {
        $validated = $request->validate([
            'caixa_id' => 'required|exists:caixas,id',
            'valor' => 'required|numeric|min:0.01',
            'observacoes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $caixa = Caixa::findOrFail($validated['caixa_id']);

            if ($caixa->status !== 'aberto') {
                return response()->json([
                    'success' => false,
                    'message' => 'Caixa não está aberto'
                ], 400);
            }

            $operadorId = PDVHelper::getOperadorId($request);
            
            if (!$operadorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operador não autenticado'
                ], 401);
            }

            $sangria = Sangria::create([
                'caixa_id' => $caixa->id,
                'user_id' => $operadorId,
                'valor' => $validated['valor'],
                'observacoes' => $validated['observacoes'] ?? null,
                'data_hora' => now(),
            ]);

            // Atualiza valores do caixa
            $caixa->valor_total_sangrias += $validated['valor'];
            $caixa->calcularValorEsperado();
            $caixa->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $sangria,
                'message' => 'Sangria registrada com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function suprimento(Request $request)
    {
        $validated = $request->validate([
            'caixa_id' => 'required|exists:caixas,id',
            'valor' => 'required|numeric|min:0.01',
            'observacoes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $caixa = Caixa::findOrFail($validated['caixa_id']);

            if ($caixa->status !== 'aberto') {
                return response()->json([
                    'success' => false,
                    'message' => 'Caixa não está aberto'
                ], 400);
            }

            $operadorId = PDVHelper::getOperadorId($request);
            
            if (!$operadorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operador não autenticado'
                ], 401);
            }

            $suprimento = Suprimento::create([
                'caixa_id' => $caixa->id,
                'user_id' => $operadorId,
                'valor' => $validated['valor'],
                'observacoes' => $validated['observacoes'] ?? null,
                'data_hora' => now(),
            ]);

            // Atualiza valores do caixa
            $caixa->valor_total_suprimentos += $validated['valor'];
            $caixa->calcularValorEsperado();
            $caixa->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $suprimento,
                'message' => 'Suprimento registrado com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $caixa = Caixa::with(['vendas', 'sangrias', 'suprimentos', 'user'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $caixa
        ]);
    }
}
