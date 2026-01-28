<?php

namespace App\Http\Controllers;

use App\Http\Requests\Messages\StoreMessageRequest;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MessageController extends Controller
{
    public function inbox(Request $request): \Illuminate\View\View
    {
        $user = $request->user();

        return view('messages.index', [
            'messages' => $this->buildMessagesPayload($user),
            'messageScopes' => $this->buildScopes($user),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $scopeType = $request->string('scope_type')->toString();
        $scopeId = (int) $request->input('scope_id');

        if (in_array($scopeType, ['team', 'project'], true) && $scopeId > 0) {
            $this->markReadForScope($user, $scopeType, $scopeId);
        }
        $payload = $this->buildMessagesPayload($user);

        return response()->json([
            'messages' => $payload,
        ]);
    }

    public function store(StoreMessageRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $attributes = [
            'sender_id' => $user->id,
            'recipient_id' => $data['recipient_id'] ?? null,
            'body' => $data['body'],
        ];

        if ($data['scope_type'] === 'team') {
            $attributes['team_id'] = $data['scope_id'];
        }

        if ($data['scope_type'] === 'project') {
            $attributes['project_id'] = $data['scope_id'];
        }

        $message = Message::create($attributes);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Mensaje enviado.',
                'id' => $message->id,
            ]);
        }

        return back()->with('success', 'Mensaje enviado.');
    }

    private function buildMessagesPayload(User $user): Collection
    {
        $teamIds = $user->teams()->pluck('teams.id')->all();
        $projectIds = $user->projects()->pluck('projects.id')->all();

        $messages = Message::query()
            ->where(function ($query) use ($teamIds, $projectIds) {
                $query->whereIn('team_id', $teamIds)
                    ->orWhereIn('project_id', $projectIds);
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('recipient_id')
                    ->orWhere('recipient_id', $user->id)
                    ->orWhere('sender_id', $user->id);
            })
            ->with([
                'sender:id,name,apellido',
                'recipient:id,name,apellido',
                'team:id,name',
                'project:id,name',
            ])
            ->latest()
            ->limit(60)
            ->get();

        $messageIds = $messages->pluck('id')->all();
        $recipientIds = $messages->pluck('recipient_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $readEntries = collect();
        if (!empty($messageIds)) {
            $readEntries = MessageRead::query()
                ->whereIn('message_id', $messageIds)
                ->whereIn('user_id', array_unique(array_merge([$user->id], $recipientIds)))
                ->get()
                ->groupBy('message_id');
        }

        return $messages->map(function (Message $message) use ($user, $readEntries) {
            $senderName = trim($message->sender?->name . ' ' . $message->sender?->apellido);
            $recipientName = $message->recipient
                ? trim($message->recipient->name . ' ' . $message->recipient->apellido)
                : null;

            $scopeType = $message->team_id ? 'team' : 'project';
            $scopeName = $message->team_id ? $message->team?->name : $message->project?->name;
            $scopeId = $message->team_id ?: $message->project_id;
            $sender = $message->sender;
            $senderInitials = strtoupper(trim(($sender?->name[0] ?? '') . ($sender?->apellido[0] ?? '')));
            $reads = $readEntries->get($message->id, collect());
            $recipientRead = $message->recipient_id
                ? (bool) $reads->firstWhere('user_id', $message->recipient_id)
                : false;
            $readByCurrent = (bool) $reads->firstWhere('user_id', $user->id);

            return [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at?->toISOString(),
                'created_label' => $message->created_at?->format('Y-m-d H:i'),
                'sender' => [
                    'id' => $message->sender_id,
                    'name' => $senderName ?: $message->sender?->name,
                    'initials' => $senderInitials,
                    'avatar' => $sender?->avatar_path ? asset('storage/' . $sender->avatar_path) : null,
                ],
                'recipient' => $recipientName ? [
                    'id' => $message->recipient_id,
                    'name' => $recipientName,
                ] : null,
                'scope' => [
                    'type' => $scopeType,
                    'id' => $scopeId,
                    'name' => $scopeName,
                    'label' => $scopeType === 'team' ? 'Equipo' : 'Proyecto',
                ],
                'is_own' => $message->sender_id === $user->id,
                'read_by_current' => $readByCurrent,
                'read_by_recipient' => $recipientRead,
            ];
        });
    }

    private function buildScopes(User $user): Collection
    {
        $teamScopes = $user->teams()
            ->with('users:id,name,apellido')
            ->get()
            ->map(function ($team) use ($user) {
                $recipients = $team->users
                    ->reject(fn ($member) => $member->id === $user->id)
                    ->map(function ($member) {
                        $initials = strtoupper(trim(($member->name[0] ?? '') . ($member->apellido[0] ?? '')));
                        return [
                            'id' => $member->id,
                            'name' => trim($member->name . ' ' . $member->apellido),
                            'initials' => $initials,
                            'avatar' => $member->avatar_path ? asset('storage/' . $member->avatar_path) : null,
                        ];
                    })
                    ->values();

                return [
                    'key' => 'team:' . $team->id,
                    'type' => 'team',
                    'id' => $team->id,
                    'name' => $team->name,
                    'role' => $team->pivot->role ?? null,
                    'can_send' => ($team->pivot->role ?? null) !== 'observer',
                    'recipients' => $recipients,
                ];
            });

        $projectScopes = $user->projects()
            ->with('team.users:id,name,apellido')
            ->get()
            ->map(function ($project) use ($user) {
                $recipients = $project->team
                    ? $project->team->users
                    : collect()
                ;
                $recipients = $recipients
                    ->reject(fn ($member) => $member->id === $user->id)
                    ->map(function ($member) {
                        $initials = strtoupper(trim(($member->name[0] ?? '') . ($member->apellido[0] ?? '')));
                        return [
                            'id' => $member->id,
                            'name' => trim($member->name . ' ' . $member->apellido),
                            'initials' => $initials,
                            'avatar' => $member->avatar_path ? asset('storage/' . $member->avatar_path) : null,
                        ];
                    })
                    ->values();

                return [
                    'key' => 'project:' . $project->id,
                    'type' => 'project',
                    'id' => $project->id,
                    'name' => $project->name,
                    'role' => $project->pivot->role ?? null,
                    'can_send' => ($project->pivot->role ?? null) !== 'observer',
                    'recipients' => $recipients,
                ];
            });

        return $teamScopes->merge($projectScopes)->values();
    }

    private function markReadForScope(User $user, string $scopeType, int $scopeId): void
    {
        if ($scopeType === 'team' && ! $user->teams()->where('teams.id', $scopeId)->exists()) {
            return;
        }

        if ($scopeType === 'project' && ! $user->projects()->where('projects.id', $scopeId)->exists()) {
            return;
        }

        $query = Message::query()
            ->where($scopeType === 'team' ? 'team_id' : 'project_id', $scopeId)
            ->where(function ($query) use ($user) {
                $query->whereNull('recipient_id')
                    ->orWhere('recipient_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->latest()
            ->limit(200);

        $messageIds = $query->pluck('id');
        if ($messageIds->isEmpty()) {
            return;
        }

        $alreadyRead = MessageRead::query()
            ->where('user_id', $user->id)
            ->whereIn('message_id', $messageIds)
            ->pluck('message_id')
            ->all();

        $pending = $messageIds->diff($alreadyRead);
        if ($pending->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $pending->map(fn ($messageId) => [
            'message_id' => $messageId,
            'user_id' => $user->id,
            'read_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        MessageRead::insert($rows);
    }
}
