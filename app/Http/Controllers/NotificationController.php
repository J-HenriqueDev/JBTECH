<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SystemNotification;

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

            $notifications = $user->notifications()->latest()->take(20)->get();
            $unreadCount = $user->unreadNotifications->count();

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar notificações: ' . $e->getMessage());
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
}
