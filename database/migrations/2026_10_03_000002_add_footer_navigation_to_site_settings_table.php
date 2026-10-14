<?php

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->json('footer_services_menu')->nullable();
            $table->json('footer_bottom_menu')->nullable();
            foreach (['footer_quick_links_title', 'footer_services_title'] as $field) {
                foreach (['en', 'my', 'th'] as $locale) {
                    $table->string("{$field}_{$locale}")->nullable();
                }
            }
        });

        SiteSetting::query()->each(function (SiteSetting $settings): void {
            $updates = [
                'footer_quick_links_title_en' => 'Quick Links',
                'footer_quick_links_title_my' => 'အမြန်လင့်ခ်များ',
                'footer_quick_links_title_th' => 'ลิงก์ด่วน',
                'footer_services_title_en' => 'Solutions',
                'footer_services_title_my' => 'ဝန်ဆောင်မှုများ',
                'footer_services_title_th' => 'บริการ',
                'footer_bottom_menu' => [
                    ['label_en' => 'Support', 'label_my' => 'အကူအညီ', 'label_th' => 'ติดต่อ', 'url' => '/contact', 'type' => 'Pages', 'is_active' => true],
                    ['label_en' => 'Privacy Policy', 'label_my' => 'ကိုယ်ရေးလုံခြုံမှုမူဝါဒ', 'label_th' => 'นโยบายความเป็นส่วนตัว', 'url' => '/privacy-policy', 'type' => 'Pages', 'is_active' => true],
                ],
            ];

            if (empty($settings->footer_menu)) {
                $updates['footer_menu'] = collect($settings->header_menu ?? [])
                    ->filter(fn (array $item): bool => ($item['type'] ?? null) !== 'Group')
                    ->map(fn (array $item): array => [...$item, 'type' => 'Pages'])
                    ->values()->all();
                $updates['footer_menu'][] = [
                    'label_en' => 'Privacy Policy', 'label_my' => 'ကိုယ်ရေးလုံခြုံမှုမူဝါဒ',
                    'label_th' => 'นโยบายความเป็นส่วนตัว', 'url' => '/privacy-policy',
                    'type' => 'Pages', 'is_active' => true,
                ];
            }

            $serviceLinks = collect($settings->header_menu ?? [])
                ->first(fn (array $item): bool => ($item['type'] ?? null) === 'Group')['sub_menu'] ?? [];
            $updates['footer_services_menu'] = collect($serviceLinks)
                ->filter(fn (array $item): bool => ! empty($item['url']))
                ->map(fn (array $item): array => [...$item, 'type' => 'Pages'])
                ->values()->all();
            if ($updates['footer_services_menu'] === []) {
                $updates['footer_services_menu'] = Page::published()->where('page_type', 'service')->get()
                    ->map(fn (Page $page): array => [
                        'label_en' => $page->translation('en')['title'] ?? $page->slug,
                        'label_my' => $page->translation('my')['title'] ?? null,
                        'label_th' => $page->translation('th')['title'] ?? null,
                        'url' => '/services/'.$page->slug, 'type' => 'Pages', 'is_active' => true,
                    ])->all();
            }

            $settings->update($updates);
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'footer_services_menu', 'footer_bottom_menu',
                'footer_quick_links_title_en', 'footer_quick_links_title_my', 'footer_quick_links_title_th',
                'footer_services_title_en', 'footer_services_title_my', 'footer_services_title_th',
            ]);
        });
    }
};
