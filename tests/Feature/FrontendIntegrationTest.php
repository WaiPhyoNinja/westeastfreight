<?php

namespace Tests\Feature;

use App\Filament\Resources\CustomerResource;
use App\Mail\CustomerWelcomeMail;
use App\Models\Page;
use App\Models\QuotationCountry;
use App\Models\QuoteEmailDelivery;
use App\Models\QuoteRequest;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\WebsiteSubmission;
use Database\Seeders\SourceServicePageSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FrontendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vue_content_uses_published_local_blocks_and_localized_nested_items(): void
    {
        Page::create(['slug' => 'home', 'status' => 'published', 'page_type' => 'page',
            'translations' => ['en' => ['title' => 'Home'], 'my' => ['title' => 'ပင်မ']],
            'blocks' => [
                ['type' => 'vue_contact_card', 'card' => [['image' => 'page-blocks/contact.png', 'translations' => ['en' => ['title' => 'Office'], 'my' => ['title' => 'ရုံး']]]]],
                ['type' => 'vue_services', 'translations' => ['en' => ['services' => [['title' => 'Sea', 'description' => 'Shipping', 'link' => '/services/sea']]], 'my' => ['services' => [['title' => 'ရေကြောင်း']]]]],
            ],
        ]);
        Page::create(['slug' => 'hidden', 'status' => 'draft', 'page_type' => 'page', 'translations' => [], 'blocks' => []]);
        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/my')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.blocks.0.collection', 'block_contact_card')
            ->assertJsonPath('data.0.blocks.0.data.card.0.contact_card_id.translations.1.title', 'ရုံး')
            ->assertJsonPath('data.0.blocks.1.data.services.0.services_id.translations.1.title', 'ရေကြောင်း');
        $this->getJson('/api/frontend/items/pages')->assertJsonCount(1, 'data');
        $this->getJson('/api/frontend/items/users')->assertNotFound();
    }

    public function test_shell_supports_deep_links_without_swallowing_admin_or_api_routes(): void
    {
        $this->withoutVite();
        Page::create(['slug' => 'air-freight', 'status' => 'published', 'page_type' => 'service', 'translations' => [], 'blocks' => []]);
        $this->get('/services/air-freight')->assertOk()->assertSee('id="app"', false);
        $this->get('/my/air-freight')->assertOk();
        $this->get('/does-not-exist')->assertNotFound();
        $this->get('/admin/login')->assertOk()->assertDontSee('id="app"', false);
        $this->getJson('/api/no-such-endpoint')->assertNotFound();
        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/services/air-freight')->assertJsonCount(1, 'data');
    }

    public function test_legacy_service_links_use_saved_public_card_content(): void
    {
        $this->withoutVite();
        Page::create(['slug' => 'services', 'status' => 'published', 'page_type' => 'page', 'translations' => [],
            'blocks' => [['type' => 'vue_service_image', 'services' => [[
                'link' => '/solutions/roadfreight', 'image' => 'page-blocks/road.png',
                'translations' => ['en' => ['title' => 'Road Freight', 'description' => '<p>Stored description</p>']],
            ]]]],
        ]);
        $this->get('/solutions/roadfreight')->assertOk();
        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/solutions/roadfreight')
            ->assertOk()->assertJsonPath('data.0.blocks.0.data.translations.0.body', '<p>Stored description</p>');
        $this->get('/solutions')->assertOk();
    }

    public function test_service_details_block_exposes_images_cards_and_localized_faqs(): void
    {
        Page::create([
            'slug' => 'roadfreight', 'page_type' => 'service', 'status' => 'published',
            'translations' => ['en' => ['title' => 'Road Freight']],
            'blocks' => [[
                'type' => 'vue_service_details', 'image' => 'page-blocks/services/road.jpg', 'image_alignment' => 'top',
                'translations' => [
                    'en' => ['title' => 'Road Freight', 'description' => '<p>Road service</p>', 'bullets' => ['Reliable'], 'faq' => [['title' => 'Which route?', 'description' => 'By road']]],
                    'my' => ['title' => 'ကုန်လမ်း', 'description' => '<p>ကုန်လမ်းဝန်ဆောင်မှု</p>'],
                ],
                'features' => [[
                    'image' => 'page-blocks/services/truck.jpg',
                    'translations' => ['en' => ['title' => 'Reliable delivery', 'description' => '<p>On time</p>']],
                ]],
            ]],
        ]);

        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/solutions/roadfreight')
            ->assertOk()
            ->assertJsonPath('data.0.blocks.0.collection', 'block_service_details')
            ->assertJsonPath('data.0.blocks.0.data.image', 'page-blocks/services/road.jpg')
            ->assertJsonPath('data.0.blocks.0.data.image_alignment', 'top')
            ->assertJsonPath('data.0.blocks.0.data.translations.1.title', 'ကုန်လမ်း')
            ->assertJsonPath('data.0.blocks.0.data.features.0.translations.0.title', 'Reliable delivery')
            ->assertJsonPath('data.0.blocks.0.data.translations.0.faq.0.title', 'Which route?');
    }

    public function test_source_service_pages_seed_with_local_images_and_can_be_seeded_again(): void
    {
        Storage::fake('public');

        $this->seed(SourceServicePageSeeder::class);
        $this->seed(SourceServicePageSeeder::class);

        $this->assertSame(6, Page::query()->where('page_type', 'service')->count());
        $road = Page::query()->where('slug', 'roadfreight')->firstOrFail();
        Storage::disk('public')->assertExists($road->blocks[1]['image']);
        $this->assertSame('center', $road->blocks[1]['image_alignment']);
        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/solutions/roadfreight')
            ->assertOk()
            ->assertJsonPath('data.0.blocks.1.collection', 'block_service_details')
            ->assertJsonPath('data.0.blocks.1.data.image_alignment', 'center');
    }

    public function test_settings_and_active_countries_come_from_admin_data(): void
    {
        SiteSetting::create(['header_logo' => 'site/logo.png', 'email' => 'office@example.test', 'phone_en' => '12345',
            'header_menu' => [['label_en' => 'About', 'label_my' => 'အကြောင်း', 'url' => '/about'], ['label_en' => 'Hidden', 'is_active' => false]],
        ]);
        QuotationCountry::create(['name' => 'Myanmar', 'short_name' => 'MM', 'status' => true]);
        QuotationCountry::create(['name' => 'Disabled', 'short_name' => 'XX', 'status' => false]);
        $this->getJson('/api/frontend/items/header')->assertJsonPath('data.0.logo', 'site/logo.png');
        $this->getJson('/api/frontend/items/menu')->assertJsonCount(1, 'data.0.items')->assertJsonPath('data.0.items.0.translations.1.title', 'အကြောင်း');
        $this->getJson('/api/frontend/items/locations')->assertJsonPath('data.0.translations.1.phone', '12345');
        $this->getJson('/api/frontend/items/quote_country')->assertJsonCount(1, 'data')->assertJsonPath('data.0.country_name', 'Myanmar');
    }

    public function test_active_social_links_come_from_site_settings(): void
    {
        SiteSetting::create(['social_icons' => [
            ['name' => 'Facebook', 'link' => 'https://facebook.com/example', 'icon' => 'site/social-icons/facebook.png', 'is_active' => true],
            ['name' => 'Hidden', 'link' => 'https://example.com', 'icon' => 'site/social-icons/hidden.png', 'is_active' => false],
        ]]);

        $this->getJson('/api/frontend/items/social_icons')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Facebook')
            ->assertJsonPath('data.0.icon', 'site/social-icons/facebook.png');
    }

    public function test_customers_register_login_logout_and_cannot_assign_admin_roles(): void
    {
        Mail::fake();
        $this->postJson('/api/frontend/auth/register', ['firstName' => 'Test', 'lastName' => 'Customer', 'email' => 'test@example.test', 'password' => 'valid-password', 'role' => 'admin', 'locale' => 'mm'])
            ->assertOk()->assertJsonPath('data.first_name', 'Test')->assertJsonMissingPath('data.password');
        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('customer'));
        Mail::assertQueued(CustomerWelcomeMail::class, function (CustomerWelcomeMail $mail) use ($user): bool {
            $this->assertSame('mm', $mail->language);
            $this->assertSame($user->name, $mail->customerName);
            $this->assertStringContainsString($user->name, $mail->render());

            return $mail->hasTo($user->email);
        });
        $this->get('/admin')->assertForbidden();
        $this->postJson('/api/frontend/auth/logout')->assertOk();
        $this->assertGuest();
        $this->postJson('/api/frontend/auth/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/api/frontend/auth/login', ['email' => $user->email, 'password' => 'valid-password'])->assertOk();
        $this->assertAuthenticatedAs($user);
        Mail::assertQueuedCount(2);
    }

    public function test_customer_list_contains_registered_customers_only(): void
    {
        Mail::fake();
        $this->postJson('/api/frontend/auth/register', [
            'firstName' => 'Sample', 'lastName' => 'Customer', 'email' => 'customer@example.test',
            'password' => 'valid-password',
        ])->assertOk();
        $customer = User::where('email', 'customer@example.test')->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $this->assertTrue(CustomerResource::getEloquentQuery()->whereKey($customer->id)->exists());
        $this->assertFalse(CustomerResource::getEloquentQuery()->whereKey($admin->id)->exists());
    }

    public function test_profile_and_password_changes_are_persisted_and_validated(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $this->actingAs($user)->patchJson('/api/frontend/auth/profile', ['first_name' => 'Updated', 'last_name' => 'Name', 'email' => 'updated@example.test'])->assertOk();
        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->postJson('/api/frontend/auth/password', ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertUnprocessable();
        $this->postJson('/api/frontend/auth/password', ['current_password' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertOk();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_reset_link_targets_this_application_and_token_can_be_used_once(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->postJson('/api/frontend/auth/password/request', ['email' => $user->email])->assertOk();
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, &$token) {
            $token = $notification->token;
            $url = $notification->toMail($user)->actionUrl;
            $this->assertStringStartsWith(url('/reset-password').'?token=', $url);
            $this->assertStringContainsString('email='.urlencode($user->email), $url);

            return true;
        });
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'reset-password'];
        $this->postJson('/api/frontend/auth/password/reset', $data)->assertOk();
        $this->assertTrue(Hash::check('reset-password', $user->fresh()->password));
        $this->postJson('/api/frontend/auth/password/reset', $data)->assertUnprocessable();
    }

    public function test_quotes_are_owned_by_the_session_user_and_only_pending_quotes_can_be_changed(): void
    {
        Mail::fake();
        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);
        $user = User::factory()->create();
        $other = User::factory()->create();
        $payload = $this->quotePayload();
        $response = $this->actingAs($user)->postJson('/api/frontend/items/quote_requests', [...$payload, 'user_created' => $other->id, 'status' => 'approved'])->assertCreated();
        $id = $response->json('data.id');
        $this->assertDatabaseHas('quote_requests', ['id' => $id, 'user_id' => $user->id, 'status' => 'pending']);
        $this->assertSame(1, QuoteEmailDelivery::where('quote_request_id', $id)->where('stage', 'pending')->count());
        $this->patchJson('/api/frontend/items/quote_requests/'.$id, [...$payload, 'weight' => 20])->assertOk()->assertJsonPath('data.weight', 20);
        QuoteRequest::findOrFail($id)->update(['quotation_mail_status' => 'queued']);
        $this->patchJson('/api/frontend/items/quote_requests/'.$id, $payload)->assertStatus(409);
        QuoteRequest::findOrFail($id)->update(['quotation_mail_status' => 'failed']);
        $this->patchJson('/api/frontend/items/quote_requests/'.$id, [...$payload, 'weight' => 20])->assertOk();
        $this->actingAs($other)->getJson('/api/frontend/items/quote_requests?filter[user_created][_eq]='.$user->id)->assertOk()->assertJsonCount(0, 'data');
        $this->patchJson('/api/frontend/items/quote_requests/'.$id, $payload)->assertNotFound();
        QuoteRequest::findOrFail($id)->update(['status' => 'rejected']);
        $this->actingAs($user)->patchJson('/api/frontend/items/quote_requests/'.$id, $payload)->assertStatus(409);
    }

    public function test_quote_validation_and_guest_access(): void
    {
        $this->getJson('/api/frontend/items/quote_requests')->assertUnauthorized();
        $this->postJson('/api/frontend/items/quote_requests', [])->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson('/api/frontend/items/quote_requests', [...$this->quotePayload(), 'weight' => -1, 'sender_country' => 'Invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors(['weight', 'sender_country']);
        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_contact_and_newsletter_submissions_are_saved_without_exposing_inbox(): void
    {
        Mail::fake();
        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);
        $this->postJson('/api/frontend/items/contact_us', ['name' => 'Sample', 'email' => 'sample@example.test', 'message' => 'Test message', 'status' => 'reviewed'])->assertCreated();
        $this->assertSame('queued', WebsiteSubmission::where('email', 'sample@example.test')->firstOrFail()->receipt_mail_status);
        $this->assertDatabaseHas('website_submissions', ['type' => 'contact_us', 'email' => 'sample@example.test', 'status' => 'new']);
        $this->postJson('/api/frontend/items/newsletter', ['email' => 'sample@example.test'])->assertCreated();
        Mail::assertNothingSent();
        $this->postJson('/api/frontend/items/contact_us', ['email' => 'invalid'])->assertUnprocessable();
        $this->getJson('/api/frontend/items/contact_us')->assertStatus(405);
    }

    public function test_freight_quote_details_are_saved_and_validated(): void
    {
        $payload = [...$this->quotePayload(),
            'transport_mode' => 'sea', 'incoterms' => 'FOB',
            'departure_type' => 'port', 'arrival_type' => 'door', 'arrival_date' => '2027-01-15',
            'commodity_type' => 'perishable', 'temperature_range' => '2–8 °C',
            'packaging' => 'container', 'container_type' => "20' Reefer", 'stackable' => false,
        ];
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/api/frontend/items/quote_requests', $payload)->assertCreated()
            ->assertJsonPath('data.transport_mode', 'sea')->assertJsonPath('data.container_type', "20' Reefer")
            ->json('data.id');
        $this->assertSame('2–8 °C', QuoteRequest::findOrFail($id)->details['temperature_range']);
        $this->patchJson('/api/frontend/items/quote_requests/'.$id, [...$payload, 'incoterms' => 'CIF'])
            ->assertOk()->assertJsonPath('data.incoterms', 'CIF');
        $this->postJson('/api/frontend/items/quote_requests', [...$payload, 'transport_mode' => 'invalid', 'temperature_range' => '', 'container_type' => null])
            ->assertUnprocessable()->assertJsonValidationErrors(['transport_mode', 'temperature_range', 'container_type']);
    }

    private function quotePayload(): array
    {
        QuotationCountry::firstOrCreate(['short_name' => 'MM'], ['name' => 'Myanmar', 'status' => true]);
        $data = ['shipment_type' => 'parcel', 'service_type' => 'standard', 'weight' => 2, 'quantity' => 1, 'description' => 'Sample shipment', 'insurance' => false];
        foreach (['sender', 'receiver'] as $party) {
            $data += [$party.'_first_name' => 'Sample', $party.'_last_name' => 'User', $party.'_email' => 'sample@example.test', $party.'_phone' => '0912345678', $party.'_address' => 'Sample address', $party.'_city' => 'Yangon', $party.'_country' => 'Myanmar'];
        }

        return $data;
    }
}
