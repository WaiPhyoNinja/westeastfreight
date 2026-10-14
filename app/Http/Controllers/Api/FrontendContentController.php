<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\QuotationCountry;
use App\Models\SiteSetting;
use App\Support\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Maps Filament content into the existing Vue website's component contract. */
class FrontendContentController extends Controller
{
    private const LANGUAGES = ['en' => 'en-US', 'my' => 'mm', 'th' => 'th'];

    public function index(Request $request, string $collection): JsonResponse
    {
        $settings = SiteSetting::query()->first();
        $filter = $request->query('filter', []);
        $filter = is_array($filter) ? $filter : [];

        $data = match ($collection) {
            'pages' => $this->pages(data_get($filter, 'permalink._eq')),
            'services' => $this->services(data_get($filter, 'Permalink._eq')),
            'header' => $settings ? [['logo' => $settings->header_logo]] : [],
            'locations' => $settings ? [['translations' => $this->location($settings)]] : [],
            'footer' => $settings ? [[
                'footer_logo' => $settings->header_logo,
                'translations' => $this->translations(collect(self::LANGUAGES)->mapWithKeys(fn ($code, $locale) => [
                    $locale => ['footer_content' => $settings->{"footer_description_{$locale}"} ?: $settings->footer_description_en,
                        'description' => $settings->{"footer_description_{$locale}"} ?: $settings->footer_description_en],
                ])->all()),
            ]] : [],
            'menu' => $settings ? $this->menu($settings, (string) data_get($filter, 'menu_id._eq', 'header')) : [],
            'social_icons' => $settings ? collect($settings->social_icons ?? [])
                ->filter(fn (array $item): bool => ($item['is_active'] ?? true) && ! empty($item['link']) && ! empty($item['icon']))
                ->values()->map(fn (array $item, int $index): array => [
                    'id' => $index, 'name' => $item['name'] ?? '', 'link' => $item['link'], 'icon' => $item['icon'],
                ])->all() : [],
            'quote_country' => QuotationCountry::where('status', true)->orderBy('name')->get()->map(fn ($country) => [
                'id' => $country->id, 'country_name' => $country->name, 'value' => $country->short_name,
            ])->all(),
            default => abort(404),
        };

        return response()->json(['data' => HtmlSanitizer::cleanMixed($data)]);
    }

    public static function slug(string $path): string
    {
        $slug = trim($path, '/');
        $slug = preg_replace('#^(en|my|th)(/|$)#', '', $slug);

        return match ($slug) {
            '' => 'home', 'solutions' => 'services', default => $slug
        };
    }

    private function pages(?string $path): array
    {
        $query = Page::published();
        if ($path !== null) {
            $slug = self::slug($path);
            $query->where(function ($query) use ($slug) {
                $query->where('slug', $slug);
                if (preg_match('#^(services|solutions)/(.+)$#', $slug, $matches)) {
                    $query->orWhere(fn ($query) => $query->where('page_type', 'service')->where('slug', $matches[2]));
                }
            });
        }

        $pages = $query->orderBy('id')->get()->map(fn (Page $page) => $this->pageData($page))->all();
        if ($pages === [] && $path !== null && ($service = $this->linkedServicePage($path))) {
            return [$service];
        }

        return $pages;
    }

    public function pageData(Page $page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->translation('en')['title'] ?? '',
            'permalink' => $page->slug === 'home' ? '/' : ($page->page_type === 'service' ? '/services/' : '/').$page->slug,
            'status' => $page->status,
            'translations' => $this->translations($page->translations ?? []),
            'seo' => [
                'title' => $page->translation('en')['meta_title'] ?? $page->translation('en')['title'] ?? '',
                'meta_description' => $page->translation('en')['meta_description'] ?? '',
            ],
            'blocks' => collect($page->blocks ?? [])->values()->map(fn ($block, $index) => $this->block($block, $index))->all(),
        ];
    }

    /** Service cards imported from the original site may have their own legacy URLs. */
    public function linkedServicePage(string $path): ?array
    {
        $path = '/'.self::slug($path);
        foreach (Page::published()->get() as $page) {
            foreach ($page->blocks ?? [] as $block) {
                if (! in_array($block['type'] ?? null, ['vue_service_image', 'vue_services'], true)) {
                    continue;
                }
                $items = $block['services'] ?? [];
                if (($block['type'] ?? null) === 'vue_services') {
                    $items = collect($block['translations']['en']['services'] ?? [])->map(fn ($item, $i) => [
                        ...$item,
                        'translations' => collect(self::LANGUAGES)->mapWithKeys(fn ($code, $locale) => [
                            $locale => $block['translations'][$locale]['services'][$i] ?? $item,
                        ])->all(),
                    ])->all();
                }
                foreach ($items as $item) {
                    if (($item['link'] ?? '') !== $path) {
                        continue;
                    }
                    $translations = $this->translations($item['translations'] ?? []);

                    return [
                        'id' => 'service-'.md5($path), 'title' => $translations[0]['title'] ?? '',
                        'permalink' => $path, 'status' => 'published', 'translations' => $translations,
                        'seo' => ['title' => $translations[0]['title'] ?? '', 'meta_description' => strip_tags($translations[0]['description'] ?? '')],
                        'blocks' => [[
                            'id' => 'service-content', 'collection' => 'native_content',
                            'data' => [
                                'image_url' => empty($item['image']) ? null : '/storage/'.$item['image'],
                                'translations' => array_map(fn ($translation) => [
                                    'languages_code' => $translation['languages_code'],
                                    'heading' => $translation['title'] ?? '', 'body' => $translation['description'] ?? '',
                                ], $translations),
                            ],
                        ]],
                    ];
                }
            }
        }

        return null;
    }

    private function translations(array $translations): array
    {
        return collect(self::LANGUAGES)->map(fn ($code, $locale) => [
            ...array_replace($translations['en'] ?? [], array_filter($translations[$locale] ?? [],
                fn ($value) => $value !== null && $value !== '' && $value !== [])),
            'languages_code' => $code,
        ])->values()->all();
    }

    private function block(array $block, int $index): array
    {
        $data = $block;
        $data['translations'] = $this->translations($block['translations'] ?? []);
        $type = $block['type'] ?? 'content';

        if ($type === 'vue_contact_card') {
            $data['card'] = collect($block['card'] ?? [])->map(fn ($card, $i) => [
                'id' => $i,
                'contact_card_id' => ['translations' => array_map(fn ($translation) => [
                    ...$translation, 'image' => $card['image'] ?? null,
                ], $this->translations($card['translations'] ?? []))],
            ])->all();
        }
        if ($type === 'vue_services') {
            $items = $block['translations']['en']['services'] ?? [];
            $data['services'] = collect($items)->map(fn ($item, $i) => ['services_id' => [
                'id' => $i, 'services_type' => $item['service_type'] ?? '', 'Permalink' => $item['link'] ?? '#',
                'translations' => collect(self::LANGUAGES)->map(fn ($code, $locale) => [
                    'languages_code' => $code,
                    'title' => $block['translations'][$locale]['services'][$i]['title'] ?? $item['title'] ?? '',
                    'desription_seo' => $block['translations'][$locale]['services'][$i]['description'] ?? $item['description'] ?? '',
                ])->values()->all(),
            ]])->all();
        }
        if ($type === 'vue_service_image') {
            $data['services'] = collect($block['services'] ?? [])->map(fn ($item, $i) => [
                'id' => $i, 'masthead_image' => $item['image'] ?? null,
                'services_type' => $item['service_type'] ?? '', 'Permalink' => $item['link'] ?? '#',
                'translations' => array_map(fn ($translation) => [...$translation,
                    'desription_seo' => $translation['description'] ?? '',
                ], $this->translations($item['translations'] ?? [])),
            ])->all();
        }
        if ($type === 'vue_features') {
            $data['features'] = collect($block['features'] ?? [])->map(fn ($item) => [
                ...$item, 'translations' => $this->translations($item['translations'] ?? []),
            ])->all();
        }
        if ($type === 'vue_service_details') {
            $data['features'] = collect($block['features'] ?? [])->map(fn ($item, $i) => [
                'id' => $i, 'image' => $item['image'] ?? null,
                'translations' => $this->translations($item['translations'] ?? []),
            ])->all();
        }

        return ['id' => $block['id'] ?? 'block-'.$index,
            'collection' => str_starts_with($type, 'vue_') ? 'block_'.substr($type, 4) : 'native_'.$type,
            'data' => $data];
    }

    private function services(?string $path): array
    {
        return Page::published()->where('page_type', 'service')->get()
            ->filter(fn ($page) => $path === null || in_array(self::slug($path), [$page->slug, 'services/'.$page->slug, 'solutions/'.$page->slug], true))
            ->map(fn ($page) => [
                'id' => $page->id, 'Permalink' => '/services/'.$page->slug,
                'translations' => array_map(fn ($translation) => [...$translation,
                    'title_seo' => $translation['title'] ?? '', 'desription_seo' => $translation['meta_description'] ?? '',
                ], $this->translations($page->translations ?? [])),
            ])->values()->all();
    }

    private function location(SiteSetting $settings): array
    {
        return collect(self::LANGUAGES)->map(fn ($code, $locale) => [
            'languages_code' => $code, 'email' => $settings->email,
            'phone' => $settings->{"phone_{$locale}"} ?: $settings->phone_en ?: $settings->phone,
            'address' => $settings->{"address_{$locale}"} ?: $settings->address_en ?: $settings->address,
            'opening_hours' => $settings->{"open_hours_{$locale}"} ?: $settings->open_hours_en,
            'open_hours' => $settings->{"open_hours_{$locale}"} ?: $settings->open_hours_en,
        ])->values()->all();
    }

    private function menu(SiteSetting $settings, string $id): array
    {
        $customerGroups = SiteSetting::customerNavigationGroups($settings->customer_navigation_groups ?? null);
        $legacyGroups = [
            'customer_book_track' => [
                'menu_id' => 'customer_book_track',
                'feature_image' => $settings->customer_book_track_image ? '/storage/'.ltrim($settings->customer_book_track_image, '/') : '/assets/img/banner-two-map-1.png',
                'label_en' => 'Book & Track',
                'items' => $settings->customer_book_track_menu ?? SiteSetting::defaultCustomerMenu('customer_book_track'),
            ],
            'customer_tools' => [
                'menu_id' => 'customer_tools',
                'feature_image' => null,
                'label_en' => 'Tools',
                'items' => $settings->customer_tools_menu ?? SiteSetting::defaultCustomerMenu('customer_tools'),
            ],
        ];

        if ($id === 'customer_navigation') {
            return [[
                'menu_id' => 'customer_navigation',
                'translations' => [],
                'items' => collect($customerGroups)->filter(fn (array $group) => $group['is_active'] ?? true)
                    ->map(fn (array $group) => [
                        'menu_id' => $group['menu_id'],
                        'feature_image' => $group['feature_image']
                            ? '/storage/'.ltrim($group['feature_image'], '/')
                            : '/assets/img/banner-two-map-1.png',
                        'translations' => collect(self::LANGUAGES)->map(fn ($code, $locale) => [
                            'languages_code' => $code,
                            'title' => ($group['label_'.$locale] ?? null) ?: ($group['label_en'] ?? ''),
                            'description' => ($group['description_'.$locale] ?? null) ?: ($group['description_en'] ?? ''),
                        ])->values()->all(),
                        'items' => $this->menuItems($group['items'] ?? []),
                    ])->values()->all(),
            ]];
        }

        $groupIndex = array_search($id, array_column($customerGroups, 'menu_id'), true);
        $group = $groupIndex !== false ? $customerGroups[$groupIndex] : ($legacyGroups[$id] ?? null);
        $items = $group['items'] ?? match ($id) {
            'header' => $settings->header_menu ?? [],
            'quick_links' => $settings->footer_menu ?? [],
            'solutions' => $settings->footer_services_menu ?? [],
            'footer_bottom' => $settings->footer_bottom_menu ?? [],
            default => [],
        };

        $heading = match ($id) {
            'quick_links' => 'footer_quick_links_title',
            'solutions' => 'footer_services_title',
            default => null,
        };

        $translations = [];
        if ($heading) {
            $translations = collect(self::LANGUAGES)->map(fn ($code, $locale) => [
                'languages_code' => $code,
                'menu_header' => $settings->{"{$heading}_{$locale}"} ?: $settings->{"{$heading}_en"},
            ])->values()->all();
        } elseif ($group !== null) {
            $translations = collect(self::LANGUAGES)->map(fn ($code, $locale) => [
                'languages_code' => $code,
                'title' => ($group['label_'.$locale] ?? null) ?: ($group['label_en'] ?? ''),
                            'description' => ($group['description_'.$locale] ?? null) ?: ($group['description_en'] ?? ''),
            ])->values()->all();
        }

        return [[
            'menu_id' => $id,
            'feature_image' => $group['feature_image'] ?? (($id === 'customer_book_track')
                ? ($settings->customer_book_track_image
                    ? '/storage/'.ltrim($settings->customer_book_track_image, '/')
                    : '/assets/img/banner-two-map-1.png')
                : null),
            'translations' => $translations,
            'items' => $this->menuItems($items),
        ]];
    }

    private function menuItems(array $items): array
    {
        return collect($items)->filter(fn ($item) => $item['is_active'] ?? true)->values()->map(fn ($item, $i) => [
            'id' => $i, 'type' => $item['type'] ?? (empty($item['sub_menu']) ? 'URL' : 'Group'),
            'url' => $item['url'] ?? '#', 'target' => ($item['open_in_new_tab'] ?? false) ? '_blank' : '_self',
            'translations' => collect(self::LANGUAGES)->map(fn ($code, $locale) => [
                'languages_code' => $code, 'title' => ($item['label_'.$locale] ?? null) ?: ($item['label_en'] ?? ''),
            ])->values()->all(),
            'sub_menu' => $this->menuItems($item['sub_menu'] ?? []),
        ])->all();
    }
}
