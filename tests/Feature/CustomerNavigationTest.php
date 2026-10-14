<?php

namespace Tests\Feature;

use App\Filament\Resources\CustomerNavigationResource;
use App\Filament\Resources\CustomerNavigationResource\Pages\EditCustomerNavigation;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_image_is_exposed_and_removal_restores_default(): void
    {
        $settings = SiteSetting::create(['brand_name' => 'WEF', 'brand_mark' => 'WE',
            'customer_navigation_groups' => [[
                'label_en' => 'Book & Track',
                'feature_image' => 'site/navigation/shipping.png',
                'items' => [[
                    'label_en' => 'Quote',
                    'url' => '/request-quote',
                    'type' => 'URL',
                    'is_active' => true,
                ]],
            ]],
        ]);
        $url = '/api/frontend/items/menu?filter[menu_id][_eq]=customer_navigation';
        $this->getJson($url)->assertOk()->assertJsonPath('data.0.items.0.feature_image', '/storage/site/navigation/shipping.png');
        $settings->update(['customer_navigation_groups' => [[
            'label_en' => 'Book & Track',
            'feature_image' => null,
            'items' => [[
                'label_en' => 'Quote',
                'url' => '/request-quote',
                'type' => 'URL',
                'is_active' => true,
            ]],
        ]]]);
        $this->getJson($url)->assertOk()->assertJsonPath('data.0.items.0.feature_image', '/assets/img/banner-two-map-1.png');
    }

    public function test_customer_menus_can_be_saved_and_api_respects_visibility_order_and_empty_list(): void
    {
        $settings = SiteSetting::create(['brand_name' => 'WEF', 'brand_mark' => 'WE']);
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('settings.manage', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
        $form = Livewire::test(EditCustomerNavigation::class, ['record' => $settings->id]);
        $this->assertCount(2, $form->get('data.customer_navigation_groups'));
        $form->set('data.customer_navigation_groups', [
            ['label_en' => 'Book & Track', 'feature_image' => null, 'items' => [
                ['label_en' => 'Hidden', 'url' => '/hidden', 'type' => 'URL', 'is_active' => false],
                ['label_en' => 'Quote', 'label_my' => 'ဈေးနှုန်း', 'url' => '/request-quote', 'type' => 'URL', 'is_active' => true, 'open_in_new_tab' => true],
                ['label_en' => 'Requests', 'url' => '/my-orders', 'type' => 'URL', 'is_active' => true],
            ]],
            ['label_en' => 'Tools', 'feature_image' => null, 'items' => []],
            ['label_en' => 'Support', 'feature_image' => null, 'items' => [
                ['label_en' => 'Contact', 'url' => '/contact', 'type' => 'URL', 'is_active' => true],
            ]],
        ])->call('save')->assertHasNoFormErrors();
        $this->assertSame('WEF', $settings->fresh()->brand_name);
        $this->assertSame(
            url('/admin/customer-navigations/'.$settings->id.'/edit'),
            CustomerNavigationResource::getNavigationUrl(),
        );
        $this->get(CustomerNavigationResource::getNavigationUrl())
            ->assertOk()->assertSee('Customer header navigation')->assertSee('Book &amp; Track', false);
        $this->getJson('/api/frontend/items/menu?filter[menu_id][_eq]=customer_navigation')->assertOk()
            ->assertJsonCount(3, 'data.0.items')
            ->assertJsonPath('data.0.items.0.items.0.url', '/request-quote')
            ->assertJsonPath('data.0.items.0.items.0.target', '_blank')
            ->assertJsonPath('data.0.items.0.items.0.translations.1.title', 'ဈေးနှုန်း');
        $settings->update(['customer_navigation_groups' => [
            [
                'label_en' => 'Book & Track',
                'feature_image' => null,
                'items' => [
                    ['label_en' => 'Hidden', 'url' => '/hidden', 'type' => 'URL', 'is_active' => false],
                    ['label_en' => 'Quote', 'label_my' => 'ဈေးနှုန်း', 'url' => '/request-quote', 'type' => 'URL', 'is_active' => true, 'open_in_new_tab' => true],
                    ['label_en' => 'Requests', 'url' => '/my-orders', 'type' => 'URL', 'is_active' => true],
                ],
            ],
            [
                'label_en' => 'Tools',
                'feature_image' => null,
                'items' => [],
            ],
        ]]);
        $this->getJson('/api/frontend/items/menu?filter[menu_id][_eq]=customer_book_track')->assertOk()
            ->assertJsonCount(2, 'data.0.items');
        $this->getJson('/api/frontend/items/menu?filter[menu_id][_eq]=customer_tools')->assertJsonCount(0, 'data.0.items');
    }
}
