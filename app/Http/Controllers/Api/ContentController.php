<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $this->locale($request);

        $pages = Page::published()
            ->orderBy('id')
            ->get()
            ->map(fn (Page $page): array => $this->pagePayload($page, $locale, false));

        return response()->json(['data' => HtmlSanitizer::cleanMixed($pages->all())]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $locale = $this->locale($request);
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => HtmlSanitizer::cleanMixed($this->pagePayload($page, $locale))]);
    }

    public function settings(Request $request): JsonResponse
    {
        $locale = $this->locale($request);
        $settings = SiteSetting::query()->first();

        if (! $settings) {
            return response()->json(['data' => null]);
        }

        $menu = $this->frontendMenu($settings->header_menu ?? []);

        return response()->json(['data' => [
            ...$settings->toArray(),
            'locale' => $locale,
            'header_logo_url' => $settings->header_logo
                ? url(Storage::disk('public')->url($settings->header_logo))
                : null,
            'header_menu' => $menu,
            'menu' => ['menu_id' => 'header', 'is_active' => true, 'items' => $menu],
            'location' => [
                'phone' => $settings->{"phone_{$locale}"} ?: $settings->phone_en,
                'email' => $settings->email,
                'address' => $settings->{"address_{$locale}"} ?: $settings->address_en,
                'opening_hours' => $settings->{"open_hours_{$locale}"} ?: $settings->open_hours_en,
            ],
        ]]);
    }

    private function locale(Request $request): string
    {
        $locale = (string) $request->query('locale', 'en');

        return in_array($locale, ['en', 'my', 'th'], true) ? $locale : 'en';
    }

    private function pagePayload(Page $page, string $locale, bool $withBlocks = true): array
    {
        $payload = [
            'id' => $page->id,
            'slug' => $page->slug,
            'permalink' => $page->slug === 'home' ? '/' : '/'.$page->slug,
            'page_type' => $page->page_type,
            'status' => $page->status,
            'locale' => $locale,
            'translation' => $page->translation($locale),
            'translations' => $page->translations,
            'updated_at' => $page->updated_at,
        ];

        if ($withBlocks) {
            $payload['blocks'] = collect($page->blocks ?? [])
                ->values()
                ->map(function (array $block, int $index) use ($locale): array {
                    return [
                        ...$block,
                        'id' => $block['id'] ?? 'block-'.$index,
                        'content' => $block['translations'][$locale]
                            ?? $block['translations']['en']
                            ?? [],
                    ];
                });
        }

        return $payload;
    }

    /** @return array<int, array<string, mixed>> */
    private function frontendMenu(array $items): array
    {
        return collect($items)
            ->filter(fn (array $item): bool => $item['is_active'] ?? true)
            ->values()
            ->map(fn (array $item, int $index): array => [
                'id' => $item['id'] ?? 'menu-'.$index,
                'type' => $item['type'] ?? (empty($item['sub_menu']) ? 'URL' : 'Group'),
                'url' => $item['url'] ?? '#',
                'target' => ($item['open_in_new_tab'] ?? false) ? '_blank' : '_self',
                'translations' => [
                    ['languages_code' => 'en-US', 'title' => $item['label_en'] ?? ''],
                    ['languages_code' => 'mm', 'title' => ($item['label_my'] ?? null) ?: ($item['label_en'] ?? '')],
                    ['languages_code' => 'th', 'title' => ($item['label_th'] ?? null) ?: ($item['label_en'] ?? '')],
                ],
                'sub_menu' => $this->frontendMenu($item['sub_menu'] ?? []),
            ])->all();
    }
}
