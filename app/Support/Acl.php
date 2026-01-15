<?php

namespace App\Support;

class Acl
{
    public static function globalRoles(): array
    {
        return config('acl.roles.global', []);
    }

    public static function teamRoles(): array
    {
        return config('acl.roles.team', []);
    }

    public static function projectRoles(): array
    {
        return config('acl.roles.project', []);
    }

    public static function abilities(): array
    {
        return config('acl.abilities', []);
    }

    public static function isValidGlobalRole(string $role): bool
    {
        return in_array($role, self::globalRoles(), true);
    }

    public static function isValidTeamRole(string $role): bool
    {
        return in_array($role, self::teamRoles(), true);
    }

    public static function isValidProjectRole(string $role): bool
    {
        return in_array($role, self::projectRoles(), true);
    }

    public static function isValidAbility(string $ability): bool
    {
        return in_array($ability, self::abilities(), true);
    }
}
