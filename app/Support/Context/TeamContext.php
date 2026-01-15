<?php

namespace App\Support\Context;

class TeamContext
{
    public const SESSION_KEY = 'context.team_id';
    public const SESSION_NAME_KEY = 'context.team_name';

    public static function set(int $teamId, ?string $teamName = null): void
    {
        session()->put(self::SESSION_KEY, $teamId);

        if ($teamName !== null) {
            session()->put(self::SESSION_NAME_KEY, $teamName);
        }
    }

    public static function get(): ?int
    {
        return session()->get(self::SESSION_KEY);
    }

    public static function clear(): void
    {
        session()->forget([self::SESSION_KEY, self::SESSION_NAME_KEY]);
    }
}
