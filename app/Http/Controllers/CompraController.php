<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Fornecedor;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        // Only admin or authorized users should manage purchases.
        // Assuming there is a gate or middleware, but for now filtering in view logic or here if needed.
        // For now, listing all for everyone, but actions are restricted.

        $showCompleted = $request->query('completed', 0);

        $query = Compra::with(['fornecedor', 'cliente']);

        if ($showCompleted) {
            $query->whereIn('status', ['recebido', 'cancelado']);
        } else {
            $query->whereNotIn('status', ['recebido', 'cancelado']);
        }

        $compras = $query->orderByRaw("FIELD(prioridade, 'alta', 'media', 'baixa')")
            ->orderBy('data_compra', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('content.compras.index', compact('compras', 'showCompleted'));
    }

    public function create()
    {
        $fornecedores = Fornecedor::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $clientes = Clientes::orderBy('nome')->get(); // For linked orders
        return view('content.compras.create', compact('fornecedores', 'produtos', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:reposicao,inovacao,uso_interno',
            'prioridade' => 'required|in:baixa,media,alta',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'cliente_id' => 'nullable|exists:clientes,id',
            'data_compra' => 'required|date',
            'status' => 'required|in:solicitado,aprovado,cotacao,pendente,comprado,recebido,cancelado',
            'items' => 'required|array',
            'items.*.produto_id' => 'nullable|exists:produtos,id|required_without:items.*.descricao_livre',
            'items.*.descricao_livre' => 'nullable|string|max:255|required_without:items.*.produto_id',
            'items.*.quantidade' => 'required|integer|min:1',
            'items.*.valor_unitario' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $valorTotalCompra = 0;

            $compra = Compra::create([
                'fornecedor_id' => $request->fornecedor_id,
                'cliente_id' => $request->cliente_id,
                'local_compra' => $request->local_compra,
                'data_compra' => $request->data_compra,
                'status' => $request->status,
                'tipo' => $request->tipo,
                'prioridade' => $request->prioridade,
                'observacoes' => $request->observacoes,
                'user_id' => Auth::id(),
                'valor_total' => 0,
            ]);

            foreach ($request->items as $item) {
                $valorUnitario = $item['valor_unitario'] ?? 0;
                $valorTotalItem = $item['quantidade'] * $valorUnitario;
                $valorTotalCompra += $valorTotalItem;

                CompraItem::create([
                    'compra_id' => $compra->id,
                    'produto_id' => $item['produto_id'] ?? null,
                    'descricao_livre' => $item['descricao_livre'] ?? null,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $valorUnitario > 0 ? $valorUnitario : null,
                    'valor_total' => $valorTotalItem > 0 ? $valorTotalItem : null,
                    'status' => 'pendente', // Default item status
                ]);

                // If received and product exists, update stock
                if ($request->status === 'recebido' && !empty($item['produto_id'])) {
                    $produto = Produto::find($item['produto_id']);
                    $produto->increment('estoque', $item['quantidade']);
                }
            }

            // Only update total if values were provided
            if ($valorTotalCompra > 0) {
                $compra->update(['valor_total' => $valorTotalCompra]);
            }

            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Solicitação de compra registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Erro ao registrar compra: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $compra = Compra::with(['fornecedor', 'items.produto', 'user', 'cliente'])->findOrFail($id);
        return view('content.compras.show', compact('compra'));
    }

    // New method to update item status
    public function updateItemStatus(Request $request, $itemId)
    {
        // Check if user is admin (ID 1)
        if (Auth::id() !== 1) {
            return redirect()->back()->withErrors('Apenas administradores podem gerenciar itens.');
        }

        $request->validate([
            'status' => 'required|in:aprovado,recusado,pendente',
        ]);

        $item = CompraItem::findOrFail($itemId);
        $item->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status do item atualizado.');
    }

    public function edit($id)
    {
        $compra = Compra::with('items')->findOrFail($id);
        $fornecedores = Fornecedor::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $clientes = Clientes::orderBy('nome')->get();
        return view('content.compras.edit', compact('compra', 'fornecedores', 'produtos', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::with('items')->findOrFail($id);

        // Validation based on input. If it's just a status update, validation is simpler.
        $request->validate([
            'status' => 'required|in:solicitado,aprovado,cotacao,pendente,comprado,recebido,cancelado',
            'motivo_recusa' => 'nullable|string|required_if:status,cancelado',
            'data_prevista_entrega' => 'nullable|date',
            // Basic fields validation if they are present
            'tipo' => 'sometimes|in:reposicao,inovacao,uso_interno',
            'prioridade' => 'sometimes|in:baixa,media,alta',
            'local_compra' => 'nullable|string|max:255',
            'data_compra' => 'sometimes|date',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $compra->status;
            $newStatus = $request->status;

            // Update basic fields if present
            $compra->fill($request->only([
                'fornecedor_id',
                'cliente_id',
                'local_compra',
                'data_compra',
                'data_prevista_entrega',
                'tipo',
                'prioridade',
                'observacoes'
            ]));

            // Update status and reason
            $compra->status = $newStatus;
            if ($newStatus === 'cancelado') {
                $compra->motivo_recusa = $request->motivo_recusa;
            }

            // Handle Stock Update on Receive
            if ($newStatus === 'recebido' && $oldStatus !== 'recebido') {
                foreach ($compra->items as $item) {
                    if ($item->produto_id) {
                        $produto = Produto::find($item->produto_id);
                        $produto->increment('estoque', $item->quantidade);
                    }
                }
            }

            // Handle Stock Reversal if un-receiving (optional, safety)
            if ($oldStatus === 'recebido' && $newStatus !== 'recebido') {
                foreach ($compra->items as $item) {
                    if ($item->produto_id) {
                        $produto = Produto::find($item->produto_id);
                        $produto->decrement('estoque', $item->quantidade);
                    }
                }
            }

            $compra->save();

            DB::commit();
            return redirect()->back()->with('success', 'Compra atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Erro ao atualizar compra: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $compra = Compra::findOrFail($id);

        // If it was received, we should reverse stock changes ideally
        // But simple delete for now
        $compra->delete();

        return redirect()->route('compras.index')->with('success', 'Compra excluída com sucesso!');
    }
}
