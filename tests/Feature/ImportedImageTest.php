<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\PublicImagePath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportedImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_extensionless_imported_images_get_recognizable_urls_and_admin_paths(): void
    {
        Storage::fake('public');
        $path = 'page-blocks/live/example';
        Storage::disk('public')->put($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL/nwAAAABJRU5ErkJggg=='
        ));
        Page::create([
            'slug' => 'home', 'page_type' => 'page', 'status' => 'published',
            'translations' => ['en' => ['title' => 'Home']],
            'blocks' => [['type' => 'vue_masthead', 'masthead_image' => $path,
                'translations' => ['en' => ['title' => 'Home']]]],
        ]);

        $this->artisan('site:normalize-images')->assertSuccessful();
        $newPath = $path.'.png';
        $this->assertSame($newPath, Page::firstOrFail()->blocks[0]['masthead_image']);
        $this->assertTrue(Storage::disk('public')->exists($newPath));
        $this->assertTrue(Storage::disk('public')->exists($path));
        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/')
            ->assertJsonPath('data.0.blocks.0.data.masthead_image', $newPath);
        $this->artisan('site:normalize-images')->assertSuccessful();
        $this->assertSame($newPath, PublicImagePath::withExtension($newPath));
    }
}
