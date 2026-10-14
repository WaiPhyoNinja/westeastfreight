<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_is_available_to_the_site_and_content_api(): void
    {
        Page::query()->create([
            'slug' => 'home',
            'page_type' => 'page',
            'status' => 'published',
            'translations' => [
                'en' => ['title' => 'Home'],
                'my' => ['title' => 'ပင်မစာမျက်နှာ'],
            ],
            'blocks' => [[
                'type' => 'hero',
                'translations' => [
                    'en' => ['heading' => 'Welcome'],
                    'my' => ['heading' => 'ကြိုဆိုပါတယ်'],
                ],
            ]],
        ]);

        $this->get('/')->assertOk();

        $this->getJson('/api/v1/pages/home?locale=my')
            ->assertOk()
            ->assertJsonPath('data.translation.title', 'ပင်မစာမျက်နှာ')
            ->assertJsonPath('data.blocks.0.content.heading', 'ကြိုဆိုပါတယ်');
    }

    public function test_site_settings_header_is_available_to_vue(): void
    {
        SiteSetting::query()->create([
            'brand_name' => 'West East Freight',
            'brand_mark' => 'WE',
            'header_logo' => 'site/logo/logo.png',
            'phone_my' => '၀၉ ၁၂၃ ၄၅၆',
            'email' => 'hello@example.com',
            'header_menu' => [[
                'label_en' => 'About',
                'label_my' => 'ကျွန်ုပ်တို့အကြောင်း',
                'type' => 'URL',
                'url' => '/about',
                'is_active' => true,
            ]],
        ]);

        $this->getJson('/api/v1/site-settings?locale=my')
            ->assertOk()
            ->assertJsonPath('data.brand_name', 'West East Freight')
            ->assertJsonPath('data.location.phone', '၀၉ ၁၂၃ ၄၅၆')
            ->assertJsonPath('data.header_menu.0.translations.1.title', 'ကျွန်ုပ်တို့အကြောင်း')
            ->assertJsonPath('data.header_logo_url', 'http://localhost/storage/site/logo/logo.png');
    }
}
