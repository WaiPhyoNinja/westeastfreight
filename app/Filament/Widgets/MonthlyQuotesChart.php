<?php

namespace App\Filament\Widgets;

use App\Models\QuoteRequest;
use Filament\Widgets\ChartWidget;

class MonthlyQuotesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly quote requests';

    protected static ?string $description = 'Last 6 calendar months · current month included';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '60s';

    protected static ?array $options = ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]]];

    public static function canView(): bool
    {
        return auth()->user()?->can('quote-requests.manage') ?? false;
    }

    protected function getData(): array
    {
        abort_unless(static::canView(), 403);
        $labels = [];
        $values = [];
        $start = now()->startOfMonth()->subMonths(5);
        for ($month = 0; $month < 6; $month++) {
            $from = $start->copy()->addMonths($month);
            $labels[] = $from->format('M Y');
            $values[] = QuoteRequest::where('created_at', '>=', $from)->where('created_at', '<', $from->copy()->addMonth())->count();
        }

        return ['datasets' => [['label' => 'Monthly quote requests', 'data' => $values, 'backgroundColor' => '#f59e0b', 'borderRadius' => 5]], 'labels' => $labels];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
