<?php

namespace Tests\Feature;

use App\Filament\Blocks\BlockRegistry;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBlockAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_registered_block_is_exposed_with_a_schema_and_renderable_collection(): void
    {
        $types = array_keys(BlockRegistry::options());
        Page::create(['slug' => 'block-audit', 'status' => 'published', 'page_type' => 'page',
            'translations' => ['en' => ['title' => 'Audit']],
            'blocks' => array_map(fn ($type) => ['type' => $type], $types),
        ]);
        $blocks = $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/block-audit')
            ->assertOk()->json('data.0.blocks');
        $renderer = file_get_contents(resource_path('frontend/components/BlockRenderer.vue'));
        foreach ($types as $index => $type) {
            $this->assertNotEmpty(BlockRegistry::schemaFor($type), $type);
            $collection = $blocks[$index]['collection'];
            if (str_starts_with($collection, 'native_')) {
                $this->assertStringContainsString("startsWith('native_')", $renderer);
            } else {
                $this->assertStringContainsString($collection, $renderer);
            }
        }
    }

    public function test_service_permalink_does_not_conflict_with_storage_assets(): void
    {
        $this->withoutVite();
        Page::create(['slug' => 'storage', 'status' => 'published', 'page_type' => 'service', 'translations' => [], 'blocks' => []]);
        $this->getJson('/api/frontend/items/pages')->assertJsonPath('data.0.permalink', '/services/storage');
        $this->get('/services/storage')->assertOk()->assertSee('id="app"', false);
    }

    public function test_blank_translations_fall_back_without_losing_translated_fields(): void
    {
        Page::create(['slug' => 'block-audit', 'status' => 'published', 'page_type' => 'page',
            'translations' => [], 'blocks' => [[
                'type' => 'vue_features',
                'translations' => ['en' => ['title' => 'Features', 'subtitle' => 'Our services'], 'my' => ['title' => '', 'subtitle' => 'မြန်မာ']],
                'features' => [['translations' => ['en' => ['title' => 'Delivery'], 'my' => ['title' => null]]]],
            ]],
        ]);
        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/block-audit')->assertOk()
            ->assertJsonPath('data.0.blocks.0.data.translations.1.title', 'Features')
            ->assertJsonPath('data.0.blocks.0.data.translations.1.subtitle', 'မြန်မာ')
            ->assertJsonPath('data.0.blocks.0.data.features.0.translations.1.title', 'Delivery');
    }
}
