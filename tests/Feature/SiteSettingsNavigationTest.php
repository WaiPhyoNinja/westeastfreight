<?php

namespace Tests\Feature;

use App\Filament\Resources\CustomerNavigationResource;
use App\Filament\Resources\CustomerNavigationResource\Pages\EditCustomerNavigation;
use App\Filament\Resources\FooterNavigationResource;
use App\Filament\Resources\FooterNavigationResource\Pages\EditFooterNavigation;
use App\Filament\Resources\HeaderNavigationResource;
use App\Filament\Resources\HeaderNavigationResource\Pages\EditHeaderNavigation;
use App\Filament\Resources\SiteSettingResource;
use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SiteSettingsNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_uses_the_site_settings_logo(): void
    {
        SiteSetting::create([
            'brand_name' => 'West East Freight',
            'header_logo' => 'site/logo/admin-logo.png',
        ]);

        $panel = Filament::getPanel('admin');

        $this->assertSame('West East Freight', $panel->getBrandName());
        $this->assertSame(
            'http://localhost/storage/site/logo/admin-logo.png',
            $panel->getBrandLogo(),
        );
        $this->assertSame('2.5rem', $panel->getBrandLogoHeight());
    }

    public function test_sections_have_separate_edit_pages_and_only_save_their_own_fields(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('settings.manage', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        $settings = SiteSetting::create([
            'brand_name' => 'West East Freight',
            'brand_mark' => 'WE',
            'header_menu' => [['label_en' => 'Home', 'type' => 'URL', 'url' => '/', 'is_active' => true]],
            'footer_menu' => [['label_en' => 'About', 'type' => 'URL', 'url' => '/about', 'is_active' => true]],
            'footer_quick_links_title_en' => 'Quick Links',
            'footer_services_title_en' => 'Solutions',
        ]);

        $this->get('/admin/site-settings/'.$settings->id.'/edit')->assertOk();
        $this->get('/admin/header-navigations/'.$settings->id.'/edit')->assertOk();
        $this->get('/admin/footer-navigations/'.$settings->id.'/edit')->assertOk();

        Livewire::test(EditSiteSetting::class, ['record' => $settings->id])
            ->assertFormFieldDoesNotExist('customer_navigation_groups');
        Livewire::test(EditHeaderNavigation::class, ['record' => $settings->id])
            ->assertFormFieldDoesNotExist('customer_navigation_groups');
        Livewire::test(EditCustomerNavigation::class, ['record' => $settings->id])
            ->assertFormFieldExists('customer_navigation_groups');

        $headerForm = Livewire::test(EditHeaderNavigation::class, ['record' => $settings->id]);
        $headerKey = array_key_first($headerForm->get('data.header_menu'));
        $headerForm->set("data.header_menu.{$headerKey}.url", '/new-home')
            ->call('save')->assertHasNoFormErrors();

        $settings->refresh();
        $this->assertSame('/new-home', $settings->header_menu[0]['url']);
        $this->assertSame('/about', $settings->footer_menu[0]['url']);

        $footerForm = Livewire::test(EditFooterNavigation::class, ['record' => $settings->id]);
        $footerKey = array_key_first($footerForm->get('data.footer_menu'));
        $footerForm->set("data.footer_menu.{$footerKey}.url", '/company')
            ->call('save')->assertHasNoFormErrors();

        $settings->refresh();
        $this->assertSame('/new-home', $settings->header_menu[0]['url']);
        $this->assertSame('/company', $settings->footer_menu[0]['url']);

        Livewire::test(EditSiteSetting::class, ['record' => $settings->id])
            ->set('data.brand_name', 'West East')
            ->call('save')->assertHasNoFormErrors();

        $settings->refresh();
        $this->assertSame('West East', $settings->brand_name);
        $this->assertSame('/new-home', $settings->header_menu[0]['url']);
        $this->assertSame('/company', $settings->footer_menu[0]['url']);
    }

    public function test_site_setting_navigation_opens_each_edit_page_directly(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('settings.manage', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);

        $settings = SiteSetting::create(['brand_name' => 'West East Freight', 'brand_mark' => 'WE']);

        foreach ([
            SiteSettingResource::class => 'site-settings',
            HeaderNavigationResource::class => 'header-navigations',
            CustomerNavigationResource::class => 'customer-navigations',
            FooterNavigationResource::class => 'footer-navigations',
        ] as $resource => $slug) {
            $url = url('/admin/'.$slug.'/'.$settings->id.'/edit');
            $this->assertSame($url, $resource::getNavigationUrl());
            $this->get($url)->assertOk();
        }
    }
}
