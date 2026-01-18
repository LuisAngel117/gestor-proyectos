<?php

namespace App\Services\TimeTracking;

use App\Models\TaskTimeEntry;
use Carbon\Carbon;

class TimeEntryValidationService
{
    public const MIN_DURATION_SECONDS = 60;
    public const MAX_DURATION_SECONDS = 43200;

    public function hasActiveTimerForUser(int $userId): bool
    {
        return TaskTimeEntry::query()
            ->where('user_id', $userId)
            ->whereNull('stopped_at')
            ->exists();
    }

    public function hasOverlap(int $userId, Carbon $start, Carbon $end, ?int $ignoreEntryId = null): bool
    {
        $query = TaskTimeEntry::query()
            ->where('user_id', $userId)
            ->whereNotNull('stopped_at')
            ->where('started_at', '<', $end)
            ->where('stopped_at', '>', $start);

        if ($ignoreEntryId) {
            $query->where('id', '!=', $ignoreEntryId);
        }

        return $query->exists();
    }

    public function calculateDurationSeconds(Carbon $start, Carbon $end): int
    {
        return max(1, $end->diffInSeconds($start));
    }

    public function isDurationWithinLimits(int $seconds): bool
    {
        return $seconds >= self::MIN_DURATION_SECONDS
            && $seconds <= self::MAX_DURATION_SECONDS;
    }
}
