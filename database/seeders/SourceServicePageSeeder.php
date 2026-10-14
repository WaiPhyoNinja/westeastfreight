<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SourceServicePageSeeder extends Seeder
{
    public function run(): void
    {
        $services = json_decode(file_get_contents(__DIR__.'/data/source-service-pages.json'), true, 512, JSON_THROW_ON_ERROR);
        $assets = __DIR__.'/assets/service-pages';

        foreach (glob($assets.'/*') ?: [] as $asset) {
            $path = 'page-blocks/services/'.basename($asset);
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, file_get_contents($asset));
            }
        }

        foreach ($services as $service) {
            $pageTranslations = [];
            $headerTranslations = [];
            $detailsTranslations = [];

            foreach ($service['translations'] as $locale => $translation) {
                $title = $translation['title'];
                $pageTranslations[$locale] = [
                    'title' => $title,
                    'meta_title' => $translation['meta_title'] ?: $title,
                    'meta_description' => trim(preg_replace('/\s+/u', ' ', strip_tags($translation['meta_description']))),
                ];
                $headerTranslations[$locale] = [
                    'title' => $title,
                    'link' => [
                        ['link_text' => $locale === 'my' ? 'ပင်မ' : 'Home', 'link' => '/'],
                        ['link_text' => $locale === 'my' ? 'ဝန်ဆောင်မှုများ' : 'Solutions', 'link' => '/solutions'],
                        ['link_text' => $title, 'link' => null],
                    ],
                ];
                $detailsTranslations[$locale] = [
                    'title' => $title,
                    'description' => $translation['description'],
                    'bullets' => $translation['bullets'],
                    'faq' => $translation['faq'],
                ];
            }

            Page::query()->firstOrCreate(
                ['slug' => $service['slug']],
                [
                    'page_type' => 'service',
                    'status' => 'published',
                    'translations' => $pageTranslations,
                    'blocks' => [
                        [
                            'type' => 'vue_header',
                            'header_bg_image' => 'page-blocks/services/page-header-background.jpg',
                            'header_image' => 'page-blocks/services/page-header-foreground.png',
                            'animation_image' => 'page-blocks/services/page-header-animation.png',
                            'translations' => $headerTranslations,
                        ],
                        [
                            'type' => 'vue_service_details',
                            'image' => $service['image'],
                            'image_alignment' => 'center',
                            'translations' => $detailsTranslations,
                            'features' => $service['features'],
                        ],
                    ],
                ],
            );
        }
    }
}
