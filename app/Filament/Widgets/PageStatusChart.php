<?php

namespace App\Filament\Widgets;

use App\Models\Page;
use Filament\Widgets\ChartWidget;

class PageStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Page publishing overview';

    protected static ?string $description = 'All time · published and draft pages';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '60s';

    protected static ?array $options = ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]], 'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]]];

    public static function canView(): bool
    {
        return auth()->user()?->can('pages.view') ?? false;
    }

    protected function getData(): array
    {
        abort_unless(static::canView(), 403);
        $counts = Page::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return ['datasets' => [['label' => 'Pages', 'data' => [(int) ($counts['published'] ?? 0), (int) ($counts['draft'] ?? 0)], 'backgroundColor' => ['#10b981', '#f59e0b'], 'borderRadius' => 5]], 'labels' => ['Published', 'Draft']];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
