<?php

namespace App\Services\Reports;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Services\Tracking\TaskStatusTrackingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BurndownChartService
{
    public function buildSeries(Project $project, Sprint $sprint): array
    {
        $range = $this->resolveSprintRange($sprint);
        $days = $this->buildDayRange($range['start'], $range['end']);

        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->where('sprint_id', $sprint->id)
            ->get(['id', 'estimated_hours', 'status', 'completed_at', 'status_changed_at', 'updated_at']);

        $totalEstimated = (float) $tasks->sum('estimated_hours');

        $completedByDate = $this->buildCompletedByDate($tasks);
        $remaining = [];
        $cumulativeCompleted = 0.0;

        foreach ($days as $day) {
            $dateKey = $day->format('Y-m-d');
            $cumulativeCompleted += $completedByDate[$dateKey] ?? 0.0;
            $remaining[] = max(0.0, $totalEstimated - $cumulativeCompleted);
        }

        $ideal = $this->buildIdealLine($totalEstimated, count($days));

        return [
            'days' => $days,
            'remaining' => $remaining,
            'ideal' => $ideal,
            'total' => $totalEstimated,
            'start' => $range['start'],
            'end' => $range['end'],
        ];
    }

    public function renderSvg(array $series, array $options = []): string
    {
        $width = (int) ($options['width'] ?? 640);
        $height = (int) ($options['height'] ?? 220);
        $margin = (int) ($options['margin'] ?? 30);

        $days = $series['days'];
        $remaining = $series['remaining'];
        $ideal = $series['ideal'];
        $count = max(2, count($days));
        $maxValue = max(1.0, max($remaining ?: [0.0]));

        $plotWidth = $width - ($margin * 2);
        $plotHeight = $height - ($margin * 2);

        $remainingPoints = $this->buildPolyline($remaining, $count, $plotWidth, $plotHeight, $margin, $maxValue);
        $idealPoints = $this->buildPolyline($ideal, $count, $plotWidth, $plotHeight, $margin, $maxValue);

        $startLabel = $days ? $days[0]->format('Y-m-d') : '';
        $endLabel = $days ? $days[count($days) - 1]->format('Y-m-d') : '';
        $heightMinusMargin = $height - $margin;
        $widthMinusMargin = $width - $margin;
        $labelY = $height - 10;
        $labelEndX = $widthMinusMargin - 60;
        $titleY = $margin - 8;

        return <<<SVG
<svg width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" xmlns="http://www.w3.org/2000/svg">
    <rect x="0" y="0" width="{$width}" height="{$height}" fill="#ffffff"/>
    <line x1="{$margin}" y1="{$margin}" x2="{$margin}" y2="{$heightMinusMargin}" stroke="#d1d5db" stroke-width="1"/>
    <line x1="{$margin}" y1="{$heightMinusMargin}" x2="{$widthMinusMargin}" y2="{$heightMinusMargin}" stroke="#d1d5db" stroke-width="1"/>
    <polyline fill="none" stroke="#2563eb" stroke-width="2" points="{$remainingPoints}" />
    <polyline fill="none" stroke="#9ca3af" stroke-dasharray="4 3" stroke-width="2" points="{$idealPoints}" />
    <text x="{$margin}" y="{$labelY}" font-size="9" fill="#6b7280">{$startLabel}</text>
    <text x="{$labelEndX}" y="{$labelY}" font-size="9" fill="#6b7280">{$endLabel}</text>
    <text x="{$margin}" y="{$titleY}" font-size="9" fill="#6b7280">Horas restantes</text>
</svg>
SVG;
    }

    private function resolveSprintRange(Sprint $sprint): array
    {
        $start = $sprint->start_date
            ? Carbon::parse($sprint->start_date)->startOfDay()
            : $sprint->created_at->copy()->startOfDay();

        $end = $sprint->end_date
            ? Carbon::parse($sprint->end_date)->endOfDay()
            : now()->endOfDay();

        if ($end->lessThan($start)) {
            $end = $start->copy()->endOfDay();
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    private function buildDayRange(Carbon $start, Carbon $end): array
    {
        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        return $days;
    }

    private function buildCompletedByDate(Collection $tasks): array
    {
        $completed = [];

        foreach ($tasks as $task) {
            if (!in_array($task->status, TaskStatusTrackingService::DONE_STATUSES, true) && !$task->completed_at) {
                continue;
            }

            $date = $task->completed_at
                ? Carbon::parse($task->completed_at)->format('Y-m-d')
                : $this->fallbackCompletionDate($task);

            if (!$date) {
                continue;
            }

            $completed[$date] = ($completed[$date] ?? 0.0) + (float) ($task->estimated_hours ?? 0);
        }

        return $completed;
    }

    private function fallbackCompletionDate(Task $task): ?string
    {
        $source = $task->status_changed_at ?? $task->updated_at;
        if (!$source) {
            return null;
        }

        return Carbon::parse($source)->format('Y-m-d');
    }

    private function buildIdealLine(float $total, int $count): array
    {
        $points = [];
        if ($count <= 1) {
            return [$total];
        }

        $step = $total / ($count - 1);
        for ($i = 0; $i < $count; $i++) {
            $points[] = max(0.0, $total - ($step * $i));
        }

        return $points;
    }

    private function buildPolyline(
        array $values,
        int $count,
        int $plotWidth,
        int $plotHeight,
        int $margin,
        float $maxValue
    ): string {
        $points = [];
        $stepX = $count > 1 ? $plotWidth / ($count - 1) : 0;

        foreach ($values as $index => $value) {
            $x = $margin + ($stepX * $index);
            $ratio = $maxValue > 0 ? ($value / $maxValue) : 0;
            $y = $margin + ($plotHeight * (1 - $ratio));
            $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
        }

        return implode(' ', $points);
    }
}
