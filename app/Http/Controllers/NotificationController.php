<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notifications\MarkAllNotificationsReadRequest;
use App\Http\Requests\Notifications\MarkNotificationReadRequest;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
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

        $collection = $notifications->getCollection();

        $taskIds = $collection
            ->pluck('data.task_id')
            ->filter()
            ->unique()
            ->values();

        $userIds = $collection
            ->map(function ($notification) {
                return $notification->data['assigned_by']
                    ?? $notification->data['user_id']
                    ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $tasks = $taskIds->isEmpty()
            ? collect()
            : Task::query()
                ->select('id', 'project_id', 'title', 'due_date')
                ->whereIn('id', $taskIds)
                ->get()
                ->keyBy('id');

        $users = $userIds->isEmpty()
            ? collect()
            : User::query()
                ->select('id', 'name', 'apellido')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id');

        $collection->transform(function ($notification) use ($tasks, $users) {
            $data = $notification->data ?? [];
            $task = $tasks->get($data['task_id'] ?? null);
            $actorId = $data['assigned_by'] ?? $data['user_id'] ?? null;
            $actor = $actorId ? $users->get($actorId) : null;
            $assignedAt = !empty($data['assigned_at'])
                ? Carbon::parse($data['assigned_at'])
                : null;
            $occurredAt = !empty($data['occurred_at'])
                ? Carbon::parse($data['occurred_at'])
                : null;

            $notification->meta = [
                'event' => $data['event'] ?? null,
                'task' => $task,
                'actor' => $actor,
                'assigned_at' => $assignedAt,
                'occurred_at' => $occurredAt,
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'source' => $data['source'] ?? null,
                'project_id' => $data['project_id'] ?? null,
            ];

            return $notification;
        });

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
