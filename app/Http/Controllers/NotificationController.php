<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification;
use App\Models\User;
use App\Models\Configuracao;
use App\Models\Log;
use App\Services\LogService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['notifications' => [], 'unread_count' => 0]);
            }

            if (!Schema::hasTable('notifications')) {
                return response()->json(['notifications' => [], 'unread_count' => 0]);
            }

            $notifications = $user->notifications()->latest()->take(20)->get();
            $unreadCount = $user->unreadNotifications->count();

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar notificações: ' . $e->getMessage());
            if (str_contains($e->getMessage(), 'no such table: notifications')) {
                return response()->json(['notifications' => [], 'unread_count' => 0]);
            }
            return response()->json(['error' => 'Erro interno ao buscar notificações', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Create a test notification (for debugging).
     */
    public function testNotification()
    {
        try {
            $user = Auth::user();
            $user->notify(new SystemNotification(
                'Teste de Notificação',
                'Esta é uma notificação de teste criada em ' . now()->format('d/m/Y H:i:s'),
                'info'
            ));

            return redirect()->back()->with('success', 'Notificação de teste enviada!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao enviar notificação de teste: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao enviar notificação: ' . $e->getMessage());
        }
    }

    public function admin(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        $roles = User::select('role')->distinct()->pluck('role')->filter()->values();

        $scheduledJson = Configuracao::get('scheduled_notifications');
        $scheduled = [];
        if ($scheduledJson) {
            try {
                $scheduled = json_decode($scheduledJson, true) ?: [];
            } catch (\Exception $e) {
                $scheduled = [];
            }
        }

        return view('content.notifications.admin', compact('users', 'roles', 'scheduled'));
    }

    public function send(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $request->validate([
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:5000',
            'type' => 'required|in:success,info,warning,danger',
            'target' => 'required|in:user,all,role',
            'user_id' => 'nullable|integer',
            'role' => 'nullable|string',
            'link' => 'nullable|url',
            'scheduled_at' => 'nullable|date',
            'require_confirm' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:4096',
        ]);

        $title = $request->title;
        $message = $request->message;
        $type = $request->type;
        $link = $request->link;
        $target = $request->target;
        $scheduledAt = $request->scheduled_at ? \Carbon\Carbon::parse($request->scheduled_at) : null;
        $requireConfirm = (bool) $request->require_confirm;
        $imageUrl = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $mime = $file->getMimeType() ?: 'image/png';
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $imageUrl = 'data:' . $mime . ';base64,' . $base64;
        }

        // Se agendado para o futuro, salva em Configuracao
        if ($scheduledAt && $scheduledAt->isFuture()) {
            $entry = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'require_confirm' => $requireConfirm,
                'image_url' => $imageUrl,
                'target' => $target,
                'user_id' => $request->user_id,
                'role' => $request->role,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'created_by' => auth()->id(),
                'sent_at' => null,
            ];

            $scheduledJson = Configuracao::get('scheduled_notifications');
            $scheduled = [];
            if ($scheduledJson) {
                try {
                    $scheduled = json_decode($scheduledJson, true) ?: [];
                } catch (\Exception $e) {
                }
            }
            $scheduled[] = $entry;
            Configuracao::set('scheduled_notifications', json_encode($scheduled), 'notifications', 'json', 'Notificações agendadas');

            return redirect()->route('notifications.admin')->with('success', 'Lembrete agendado com sucesso!');
        }

        // Envio imediato
        $targets = collect();
        if ($target === 'all') {
            $targets = User::all();
        } elseif ($target === 'role') {
            $targets = User::where('role', $request->role)->get();
        } else {
            if (!$request->user_id) {
                return back()->withErrors('Selecione o usuário para envio específico.');
            }
            $user = User::find($request->user_id);
            if ($user) $targets = collect([$user]);
        }

        $notification = new SystemNotification($title, $message, $type, $link, $requireConfirm, $imageUrl);
        foreach ($targets as $u) {
            $u->notify($notification);
        }

        LogService::registrar('Notificação', 'Enviar', "Título: {$title} | Destino: {$target}" . ($target === 'user' ? " | User ID: {$request->user_id}" : ($target === 'role' ? " | Role: {$request->role}" : "")));

        return redirect()->route('notifications.admin')->with('success', 'Notificação enviada com sucesso!');
    }

    public function ack(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined'
        ]);
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }
        $data = $notification->data;
        if (empty($data['require_confirm'])) {
            return response()->json(['error' => 'Confirmation not required'], 400);
        }
        $data['ack_status'] = $request->status;
        $notification->data = $data;
        $notification->save();
        $notification->markAsRead();
        return response()->json(['success' => true, 'ack_status' => $data['ack_status']]);
    }

    public function cancelScheduled(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }
        $request->validate(['id' => 'required|string']);

        $scheduledJson = Configuracao::get('scheduled_notifications');
        $scheduled = [];
        if ($scheduledJson) {
            try {
                $scheduled = json_decode($scheduledJson, true) ?: [];
            } catch (\Exception $e) {
            }
        }
        $scheduled = array_values(array_filter($scheduled, function ($item) use ($request) {
            return ($item['id'] ?? '') !== $request->id;
        }));
        Configuracao::set('scheduled_notifications', json_encode($scheduled), 'notifications', 'json', 'Notificações agendadas');

        return redirect()->route('notifications.admin')->with('success', 'Notificação agendada cancelada.');
    }

    public function history(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $query = Log::with('user')->where('categoria', 'Notificação')->latest();

        if ($request->filled('usuario')) {
            $query->where('user_id', $request->usuario);
        }
        if ($request->filled('acao')) {
            $query->where('acao', $request->acao);
        }
        if ($request->filled('periodo')) {
            if ($request->periodo === 'hoje') {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($request->periodo === '7d') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($request->periodo === '30d') {
                $query->where('created_at', '>=', now()->subDays(30));
            }
        }

        $registros = $query->paginate(15);
        $users = User::orderBy('name')->get(['id', 'name']);

        // Confirmações de notificações (últimas 200)
        $confirmations = collect();
        if (Schema::hasTable('notifications')) {
            $items = DatabaseNotification::query()->latest()->take(200)->get();
            $confirmations = $items->filter(function ($n) {
                $data = $n->data ?? [];
                return !empty($data['require_confirm']);
            })->map(function ($n) {
                return [
                    'id' => $n->id,
                    'notifiable_id' => $n->notifiable_id,
                    'title' => $n->data['title'] ?? 'Notificação',
                    'type' => $n->data['type'] ?? 'info',
                    'ack_status' => $n->data['ack_status'] ?? null,
                    'created_at' => $n->created_at,
                ];
            });
        }

        return view('content.notifications.history', compact('registros', 'users', 'confirmations'));
    }
}
