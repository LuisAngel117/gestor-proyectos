<?php

namespace App\Listeners;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Logout;

class AuditLogout
{
    public function handle(Logout $event): void
    {
        AuditLogger::log($event->user, 'auth.logout');
    }
}
