<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\ContactPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_is_seeded_with_editable_blocks_and_valid_local_images(): void
    {
        Storage::fake('public');
        $this->seed(ContactPageSeeder::class);

        $page = Page::query()->where('slug', 'contact')->firstOrFail();
        $this->assertSame('Contact Us', $page->translations['en']['title']);
        $this->assertSame(['vue_header', 'vue_contact_card', 'vue_map_content', 'vue_info'], array_column($page->blocks, 'type'));
        $this->assertCount(4, $page->blocks[1]['card']);
        Storage::disk('public')->assertExists($page->blocks[0]['header_image']);
        foreach ($page->blocks[1]['card'] as $card) {
            Storage::disk('public')->assertExists($card['image']);
        }
        $this->assertStringContainsString('</iframe>', $page->blocks[2]['translations']['my']['map_link']);

        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/contact')
            ->assertOk()->assertJsonPath('data.0.blocks.2.collection', 'block_map_content');
    }

    public function test_reseeding_repairs_original_contact_map_without_overwriting_edited_cards(): void
    {
        Storage::fake('public');
        $this->seed(ContactPageSeeder::class);
        $page = Page::query()->where('slug', 'contact')->firstOrFail();
        $translations = $page->translations;
        $translations['en']['title'] = 'Contact';
        $blocks = $page->blocks;
        $blocks[0]['translations']['en']['title'] = 'Contact';
        $blocks[1]['card'][0]['translations']['en']['description'] = '<p>Client-edited phone</p>';
        $blocks[2]['translations']['my']['map_link'] = '<iframe src="incomplete"';
        $page->update(['translations' => $translations, 'blocks' => $blocks]);

        $this->seed(ContactPageSeeder::class);
        $page->refresh();

        $this->assertSame('Contact Us', $page->translations['en']['title']);
        $this->assertSame('<p>Client-edited phone</p>', $page->blocks[1]['card'][0]['translations']['en']['description']);
        $this->assertStringContainsString('</iframe>', $page->blocks[2]['translations']['my']['map_link']);
        $this->assertSame(1, Page::query()->where('slug', 'contact')->count());
    }
}
