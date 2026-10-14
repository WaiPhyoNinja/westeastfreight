<?php

namespace Tests\Feature;

use App\Filament\Blocks\BlockRegistry;
use Tests\TestCase;

class VueWestBlockRegistryTest extends TestCase
{
    public function test_all_vue_west_blocks_are_registered_with_form_schemas(): void
    {
        $types = [
            'vue_service_image',
            'vue_about_us',
            'vue_map_content',
            'vue_info',
            'vue_contact_card',
            'vue_quotes',
            'vue_services',
            'vue_faqs',
            'vue_textslider',
            'vue_processflow',
            'vue_branded',
            'vue_masthead',
            'vue_header',
            'vue_hero',
            'vue_features',
            'vue_content',
            'vue_cta',
            'vue_richtext',
        ];

        foreach ($types as $type) {
            $this->assertArrayHasKey($type, BlockRegistry::options());
            $this->assertNotEmpty(BlockRegistry::schemaFor($type), "{$type} must have a form schema.");
        }
    }
}
