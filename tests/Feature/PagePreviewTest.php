<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PagePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_preview_requires_staff_permission_and_does_not_publish(): void
    {
        $this->withoutVite();
        $page = Page::create(['slug' => 'private-draft', 'status' => 'draft', 'page_type' => 'page',
            'translations' => ['en' => ['title' => 'Draft title']],
            'blocks' => [['type' => 'vue_richtext', 'translations' => ['en' => ['headline' => 'Draft content']]]],
        ]);
        $this->get(route('pages.preview', $page))->assertForbidden();
        $staff = User::factory()->create();
        Permission::findOrCreate('pages.view', 'web');
        $this->actingAs($staff)->get(route('pages.preview', $page))->assertForbidden();
        $staff->givePermissionTo('pages.view');
        $this->get(route('pages.preview', $page))->assertOk()->assertSee('__PAGE_PREVIEW__', false)
            ->assertSee('Draft content')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertSame('draft', $page->fresh()->status);
        $this->getJson('/api/frontend/items/pages?filter[permalink][_eq]=/private-draft')->assertJsonCount(0, 'data');
        $this->get('/private-draft')->assertNotFound();
    }
}
