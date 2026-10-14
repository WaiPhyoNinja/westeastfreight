<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Page;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Models\WebsiteSubmission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $stats = [];
        if (auth()->user()?->can('pages.view')) {
            $stats[] = Stat::make('Pages', Page::count())->description(Page::published()->count().' published')->descriptionIcon('heroicon-o-document-text')->color('info');
        }
        if (auth()->user()?->can('quote-requests.manage')) {
            $stats[] = Stat::make('Quote requests', QuoteRequest::count())->description(QuoteRequest::where('status', 'pending')->count().' awaiting review')->descriptionIcon('heroicon-o-clipboard-document-list')->color('warning');
        }
        if (auth()->user()?->can('users.manage')) {
            $stats[] = Stat::make('Users', User::count())->description('All registered accounts')->descriptionIcon('heroicon-o-users')->color('success');
        }
        if (auth()->user()?->can('invoices.view')) {
            $stats[] = Stat::make('Invoices', Invoice::count())->description('Total invoices issued')->descriptionIcon('heroicon-o-document-currency-dollar')->color('primary');
        }
        if (auth()->user()?->can('submissions.manage')) {
            $stats[] = Stat::make('Contact requests', WebsiteSubmission::where('type', 'contact_us')->count())->description('Customer enquiries')->descriptionIcon('heroicon-o-chat-bubble-left-right')->color('info');
            $stats[] = Stat::make('Newsletter signups', WebsiteSubmission::where('type', 'newsletter')->count())->description('Total signup records')->descriptionIcon('heroicon-o-envelope')->color('success');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        return auth()->user()?->canAny(['pages.view', 'quote-requests.manage', 'users.manage', 'invoices.view', 'submissions.manage']) ?? false;
    }
}
