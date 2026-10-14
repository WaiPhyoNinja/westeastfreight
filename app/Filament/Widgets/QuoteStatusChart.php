<?php

namespace App\Filament\Widgets;

use App\Models\QuoteRequest;
use Filament\Widgets\ChartWidget;

class QuoteStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Quote requests by status';

    protected static ?string $description = 'All time · current request status';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '60s';

    protected static ?array $options = ['plugins' => ['legend' => ['position' => 'bottom']]];

    public static function canView(): bool
    {
        return auth()->user()?->can('quote-requests.manage') ?? false;
    }

    protected function getData(): array
    {
        abort_unless(static::canView(), 403);
        $counts = QuoteRequest::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $statuses = ['pending', 'reviewed', 'quoted', 'approved', 'rejected'];

        return ['datasets' => [['label' => 'Quote requests', 'data' => array_map(fn ($status) => (int) ($counts[$status] ?? 0), $statuses), 'backgroundColor' => ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#ef4444'], 'borderWidth' => 0]], 'labels' => array_map('ucfirst', $statuses)];
    }

    public function getDescription(): ?string
    {
        return array_sum($this->getCachedData()['datasets'][0]['data']) === 0
            ? 'No quote requests yet.'
            : static::$description;
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
