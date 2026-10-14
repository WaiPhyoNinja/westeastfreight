<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\PublicImagePath;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LiveSitePageSeeder extends Seeder
{
    private const BASE_URL = 'https://admin.westeastfreight.com';

    private const LOCALES = ['en-US' => 'en', 'mm' => 'my', 'th' => 'th'];

    /** @var array<string, array<string, mixed>> */
    private array $collections = [];

    public function run(): void
    {
        $pages = $this->get('/items/pages', [
            'fields' => '*,translations.*,blocks.id,blocks.collection,blocks.item.*',
            'filter' => ['status' => ['_eq' => 'published']],
            'limit' => -1,
        ]);

        foreach ($pages as $page) {
            $slug = $page['permalink'] === '/' ? 'home' : trim((string) $page['permalink'], '/');

            Page::updateOrCreate(['slug' => $slug], [
                'page_type' => 'page',
                'status' => 'published',
                'translations' => $this->pageTranslations($page),
                'blocks' => collect($page['blocks'] ?? [])
                    ->map(fn (array $block): ?array => $this->block($block))
                    ->filter()->values()->all(),
            ]);
        }
    }

    /** @return array<string, array<string, mixed>> */
    private function pageTranslations(array $page): array
    {
        $seo = $page['seo'] ?? [];
        $title = (string) ($page['title'] ?? '');

        return collect(['en', 'my', 'th'])->mapWithKeys(fn (string $locale): array => [
            $locale => [
                'title' => $title,
                'meta_title' => $seo['title'] ?? $title,
                'meta_description' => $seo['meta_description'] ?? null,
            ],
        ])->all();
    }

    private function block(array $junction): ?array
    {
        $collection = $junction['collection'] ?? null;
        $id = Arr::get($junction, 'item.id');
        if (! is_string($collection) || ! $id) {
            return null;
        }

        if ($collection === 'block_richtext') {
            return [
                'id' => $junction['id'],
                'type' => 'vue_richtext',
                'translations' => ['en' => Arr::only($junction['item'], ['tagline', 'headline', 'content', 'alignment'])],
            ];
        }

        $item = $this->collectionItem($collection, (string) $id);
        $block = ['id' => $junction['id'], 'type' => 'vue_'.str_replace('block_', '', $collection)];

        foreach (['image', 'masthead_image', 'header_image', 'header_bg_image', 'animation_image'] as $field) {
            if (! empty($item[$field])) {
                $block[$field] = $this->downloadAsset((string) $item[$field]);
            }
        }

        $block['translations'] = $this->translations($item['translations'] ?? []);

        return match ($collection) {
            'block_branded' => [...$block, 'images' => collect($item['images'] ?? [])
                ->map(fn (array $image): ?string => $this->downloadAsset((string) ($image['directus_files_id'] ?? '')))
                ->filter()->values()->all()],
            'block_contact_card' => [...$block, 'card' => $this->contactCards($item['card'] ?? [])],
            'block_services' => $this->servicesBlock($block, $item['services'] ?? []),
            'block_service_image' => [...$block, 'services' => $this->services($item['services'] ?? [])],
            default => $block,
        };
    }

    /** @return array<string, mixed> */
    private function collectionItem(string $collection, string $id): array
    {
        if (! isset($this->collections[$collection])) {
            $fields = match ($collection) {
                'block_branded' => '*,images.directus_files_id',
                'block_contact_card' => '*,card.contact_card_id.*,card.contact_card_id.translations.*',
                'block_services', 'block_service_image' => '*,translations.*,services.services_id.*,services.services_id.translations.*',
                default => '*,translations.*',
            };
            $items = $this->get("/items/{$collection}", ['fields' => $fields, 'limit' => -1]);
            $this->collections[$collection] = collect($items)->keyBy('id')->all();
        }

        return $this->collections[$collection][$id]
            ?? throw new RuntimeException("Missing {$collection} item {$id}.");
    }

    /** @return array<string, array<string, mixed>> */
    private function translations(array $translations): array
    {
        return collect($translations)->mapWithKeys(function (array $translation): array {
            $locale = self::LOCALES[$translation['languages_code'] ?? ''] ?? null;
            if (! $locale) {
                return [];
            }

            $foreignKeys = array_filter(array_keys($translation), fn (string $key): bool => str_ends_with($key, '_id'));
            $content = Arr::except($translation, ['id', 'languages_code', ...$foreignKeys]);
            if (isset($content['link_url']) && ! isset($content['link'])) {
                $content['link'] = $content['link_url'];
                unset($content['link_url']);
            }

            return [$locale => $content];
        })->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function contactCards(array $cards): array
    {
        return collect($cards)->map(function (array $junction): ?array {
            $card = $junction['contact_card_id'] ?? null;
            if (! is_array($card)) {
                return null;
            }
            $translations = $this->translations($card['translations'] ?? []);
            $imageId = collect($card['translations'] ?? [])->pluck('image')->filter()->first();

            return [
                'image' => $imageId ? $this->downloadAsset((string) $imageId) : null,
                'translations' => collect($translations)->map(
                    fn (array $translation): array => Arr::only($translation, ['title', 'description'])
                )->all(),
            ];
        })->filter()->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function services(array $services): array
    {
        return collect($services)->map(function (array $junction): ?array {
            $service = $junction['services_id'] ?? null;
            if (! is_array($service)) {
                return null;
            }

            return [
                'image' => ! empty($service['masthead_image']) ? $this->downloadAsset((string) $service['masthead_image']) : null,
                'service_type' => $service['services_type'] ?? null,
                'link' => $service['Permalink'] ?? null,
                'translations' => collect($this->translations($service['translations'] ?? []))
                    ->map(fn (array $translation): array => [
                        'title' => $translation['title_seo'] ?? $translation['title'] ?? '',
                        'description' => $translation['desription_seo'] ?? $translation['description'] ?? '',
                    ])->all(),
            ];
        })->filter()->values()->all();
    }

    /** @param array<string, mixed> $block */
    private function servicesBlock(array $block, array $services): array
    {
        $items = $this->services($services);
        foreach (array_keys($block['translations']) as $locale) {
            $block['translations'][$locale]['services'] = collect($items)->map(fn (array $item): array => [
                'title' => Arr::get($item, "translations.{$locale}.title", Arr::get($item, 'translations.en.title', '')),
                'description' => Arr::get($item, "translations.{$locale}.description", Arr::get($item, 'translations.en.description', '')),
                'service_type' => $item['service_type'],
                'link' => $item['link'],
            ])->all();
        }

        return $block;
    }

    private function downloadAsset(string $id): ?string
    {
        if ($id === '') {
            return null;
        }
        $path = "page-blocks/live/{$id}";
        if (! Storage::disk('public')->exists($path)) {
            $response = Http::timeout(60)->retry(3, 500)->get(self::BASE_URL."/assets/{$id}");
            $response->throw();
            Storage::disk('public')->put($path, $response->body());
        }

        return PublicImagePath::withExtension($path);
    }

    /** @return array<int, array<string, mixed>> */
    private function get(string $path, array $query = []): array
    {
        $response = Http::timeout(60)->retry(3, 500)->get(self::BASE_URL.$path, $query);
        $response->throw();

        return $response->json('data', []);
    }
}
