<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class MonthlyUsersChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly user registrations';

    protected static ?string $description = 'Last 6 calendar months · current month included';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '60s';

    protected static ?array $options = ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]]];

    public static function canView(): bool
    {
        return auth()->user()?->can('users.manage') ?? false;
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
            $values[] = User::where('created_at', '>=', $from)->where('created_at', '<', $from->copy()->addMonth())->count();
        }

        return ['datasets' => [['label' => 'Monthly user registrations', 'data' => $values, 'backgroundColor' => '#3b82f6', 'borderRadius' => 5]], 'labels' => $labels];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
