<?php

namespace App\Listeners;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Login;

class AuditLogin
{
    public function handle(Login $event): void
    {
        AuditLogger::log($event->user, 'auth.login');
    }
}
