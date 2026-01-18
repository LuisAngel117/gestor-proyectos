<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notifications\MarkAllNotificationsReadRequest;
use App\Http\Requests\Notifications\MarkNotificationReadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($notifications);
        }

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(
        MarkNotificationReadRequest $request,
        string $notification
    ): JsonResponse|RedirectResponse {
        $userNotification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($userNotification->read_at === null) {
            $userNotification->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Notificacion marcada como leida.',
            ]);
        }

        return back()->with('success', 'Notificacion marcada como leida.');
    }

    public function markAllRead(
        MarkAllNotificationsReadRequest $request
    ): JsonResponse|RedirectResponse {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Notificaciones marcadas como leidas.',
            ]);
        }

        return back()->with('success', 'Notificaciones marcadas como leidas.');
    }
}
