<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = Log::with('user')
            ->when($request->usuario, function ($query, $usuario) {
                return $query->whereHas('user', function ($query) use ($usuario) {
                    $query->where('name', 'like', "%$usuario%");
                });
            })
            ->when($request->categoria, function ($query, $categoria) {
                return $query->where('categoria', 'like', "%$categoria%");
            })
            ->when($request->data, function ($query, $data) {
                return $query->whereDate('created_at', $data);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('content.logs.index', compact('logs'));
    }
}
