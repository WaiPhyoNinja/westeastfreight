<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ContactPageSeeder extends Seeder
{
    public function run(): void
    {
        $template = json_decode(file_get_contents(__DIR__.'/data/contact-page.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach (glob(__DIR__.'/assets/contact-page/*') ?: [] as $asset) {
            $path = 'page-blocks/contact/'.basename($asset);
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, file_get_contents($asset));
            }
        }

        $page = Page::query()->firstOrCreate(['slug' => 'contact'], [
            'page_type' => 'page',
            'status' => 'published',
            'translations' => $template['translations'],
            'blocks' => $template['blocks'],
        ]);

        if ($page->wasRecentlyCreated) {
            return;
        }

        // Repair the original imported page without replacing client-edited content.
        $translations = $page->translations ?? [];
        $blocks = $page->blocks ?? [];
        $changed = false;

        if (($translations['en']['title'] ?? null) === 'Contact') {
            $translations['en']['title'] = 'Contact Us';
            $changed = true;
        }
        if (($blocks[0]['type'] ?? null) === 'vue_header' && ($blocks[0]['translations']['en']['title'] ?? null) === 'Contact') {
            $blocks[0]['translations']['en']['title'] = 'Contact Us';
            $blocks[0]['translations']['en']['link'][1]['link_text'] = 'Contact Us';
            $changed = true;
        }
        if (($blocks[2]['type'] ?? null) === 'vue_map_content') {
            $map = $blocks[2]['translations']['my']['map_link'] ?? '';
            if ($map !== '' && ! str_contains($map, '</iframe>')) {
                $blocks[2]['translations']['my']['map_link'] = $template['blocks'][2]['translations']['my']['map_link'];
                $changed = true;
            }
        }

        if ($changed) {
            $page->update(['translations' => $translations, 'blocks' => $blocks]);
        }
    }
}
