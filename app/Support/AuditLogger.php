<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public static function log(?Authenticatable $user, string $action, ?Model $auditable = null, array $meta = [], ?Request $request = null): void
    {
        if (!$user) {
            return;
        }

        $req = $request ?: request();

        AuditLog::create([
            'user_id' => $user->getAuthIdentifier(),
            'action' => $action,
            'auditable_type' => $auditable ? $auditable->getMorphClass() : null,
            'auditable_id' => $auditable?->getKey(),
            'meta' => $meta,
            'ip_address' => $req?->ip(),
            'user_agent' => $req?->userAgent(),
        ]);
    }
}
