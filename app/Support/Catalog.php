<?php

namespace App\Support;

class Catalog
{
    public static function projectStatuses(): array
    {
        return array_keys(config('catalogs.projects.statuses', []));
    }

    public static function projectStatusLabels(): array
    {
        return config('catalogs.projects.statuses', []);
    }

    public static function projectPriorities(): array
    {
        return array_keys(config('catalogs.projects.priorities', []));
    }

    public static function projectPriorityLabels(): array
    {
        return config('catalogs.projects.priorities', []);
    }

    public static function projectTransitions(): array
    {
        return config('catalogs.projects.transitions', []);
    }

    public static function isValidProjectStatus(string $value): bool
    {
        return array_key_exists($value, self::projectStatusLabels());
    }

    public static function isValidProjectPriority(string $value): bool
    {
        return array_key_exists($value, self::projectPriorityLabels());
    }
}
