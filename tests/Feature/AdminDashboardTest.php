<?php

namespace Tests\Feature;

use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\MonthlyQuotesChart;
use App\Filament\Widgets\MonthlyUsersChart;
use App\Filament\Widgets\PageStatusChart;
use App\Filament\Widgets\QuoteStatusChart;
use App\Models\Page;
use App\Models\QuoteRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function staff(array $permissions): User
    {
        $user = User::factory()->create();
        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $user;
    }

    private function chart(string $class): array
    {
        return (new \ReflectionMethod($class, 'getData'))->invoke(new $class);
    }

    public function test_dashboard_widgets_render_real_counts_and_chart_types(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 13));
        $user = $this->staff(['pages.view', 'quote-requests.manage', 'users.manage', 'invoices.view', 'submissions.manage']);
        foreach (['published', 'published', 'draft'] as $index => $status) {
            Page::create(['slug' => 'sample-'.$index, 'status' => $status, 'page_type' => 'page', 'translations' => [], 'blocks' => []]);
        }
        QuoteRequest::create(['user_id' => $user->id, 'status' => 'pending', 'details' => []]);
        $old = QuoteRequest::create(['user_id' => $user->id, 'status' => 'reviewed', 'details' => []]);
        $old->forceFill(['created_at' => now()->startOfMonth()->subMonth()])->save();
        $this->assertSame([1, 1, 0, 0, 0], $this->chart(QuoteStatusChart::class)['datasets'][0]['data']);
        $this->assertSame([2, 1], $this->chart(PageStatusChart::class)['datasets'][0]['data']);
        $this->assertSame([0, 0, 0, 0, 1, 1], $this->chart(MonthlyQuotesChart::class)['datasets'][0]['data']);
        $this->assertSame(['May 2026', 'Jun 2026', 'Jul 2026', 'Aug 2026', 'Sep 2026', 'Oct 2026'], $this->chart(MonthlyQuotesChart::class)['labels']);
        $this->assertSame([0, 0, 0, 0, 0, 1], $this->chart(MonthlyUsersChart::class)['datasets'][0]['data']);
        $stats = (new \ReflectionMethod(DashboardOverview::class, 'getStats'))->invoke(new DashboardOverview);
        $this->assertSame([3, 2, 1, 0, 0, 0], array_map(fn ($stat) => $stat->getValue(), $stats));
        Livewire::test(DashboardOverview::class)->assertSee('Pages')->assertSee('Quote requests')->assertSee('Invoices')->assertSee('Newsletter signups');
        foreach ([QuoteStatusChart::class, MonthlyQuotesChart::class, MonthlyUsersChart::class, PageStatusChart::class] as $widget) {
            Livewire::test($widget)->assertSuccessful();
        }
        $this->get('/admin')->assertOk();
        $this->assertContains(QuoteStatusChart::class, Filament::getPanel('admin')->getWidgets());
    }

    public function test_empty_charts_use_zero_counts_without_sample_data(): void
    {
        $this->staff(['pages.view', 'quote-requests.manage']);
        $this->assertSame([0, 0, 0, 0, 0], $this->chart(QuoteStatusChart::class)['datasets'][0]['data']);
        $this->assertSame([0, 0], $this->chart(PageStatusChart::class)['datasets'][0]['data']);
        $this->assertSame([0, 0, 0, 0, 0, 0], $this->chart(MonthlyQuotesChart::class)['datasets'][0]['data']);
    }

    public function test_page_editor_cannot_read_quote_or_user_statistics(): void
    {
        $this->staff(['pages.view']);
        $this->assertTrue(PageStatusChart::canView());
        $this->assertFalse(QuoteStatusChart::canView());
        $this->assertFalse(MonthlyUsersChart::canView());
        Livewire::test(DashboardOverview::class)->assertSee('Pages')->assertDontSee('Quote requests')->assertDontSee('Users')->assertDontSee('Invoices');
        Livewire::test(QuoteStatusChart::class)->assertForbidden();
        Livewire::test(MonthlyUsersChart::class)->assertForbidden();
    }
}
