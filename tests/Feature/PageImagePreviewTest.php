<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageImagePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_blocks_show_their_names_and_positions(): void
    {
        $page = Page::create([
            'slug' => 'example', 'page_type' => 'page', 'status' => 'draft',
            'translations' => ['en' => ['title' => 'Example']],
            'blocks' => [
                ['type' => 'vue_header', 'translations' => ['en' => ['title' => 'Example']]],
                ['type' => 'vue_richtext', 'translations' => ['en' => ['headline' => 'Example']]],
            ],
        ]);
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'web'));
        $user->givePermissionTo([
            Permission::findOrCreate('pages.update', 'web'),
            Permission::findOrCreate('pages.view', 'web'),
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($user)->test(EditPage::class, ['record' => $page->id])->assertOk()
            ->assertSee('Page banner')
            ->assertSee('Text content')
            ->assertSee('page-block-editor');
    }

    public function test_existing_page_image_is_hydrated_as_same_origin_admin_preview(): void
    {
        Storage::fake('public');
        $path = 'page-blocks/live/masthead.png';
        Storage::disk('public')->put($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL/nwAAAABJRU5ErkJggg=='
        ));
        $page = Page::create(['slug' => 'home', 'page_type' => 'page', 'status' => 'published',
            'translations' => ['en' => ['title' => 'Home']],
            'blocks' => [['type' => 'vue_masthead', 'masthead_image' => $path, 'translations' => ['en' => ['title' => 'Home']]]],
        ]);
        $user = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        Permission::findOrCreate('pages.update', 'web');
        Permission::findOrCreate('pages.view', 'web');
        $user->assignRole('admin');
        $user->givePermissionTo(['pages.update', 'pages.view']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $editor = Livewire::actingAs($user)->test(EditPage::class, ['record' => $page->id])->assertOk();
        $uploads = collect($editor->instance()->form->getFlatComponents())
            ->filter(fn ($component) => $component instanceof FileUpload && $component->getName() === 'masthead_image');

        $this->assertCount(1, $uploads);
        $this->assertSame('/storage/'.$path, array_values($uploads->first()->getUploadedFiles())[0]['url']);
    }
}
