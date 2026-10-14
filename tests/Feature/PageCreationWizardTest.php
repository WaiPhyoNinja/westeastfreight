<?php

namespace Tests\Feature;

use App\Filament\Blocks\BlockRegistry;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Filament\Resources\ServicePageResource\Pages\CreateServicePage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageCreationWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_page_starts_with_a_content_section_and_grouped_layout_choices(): void
    {
        $this->signInAsPageEditor();

        $form = Livewire::test(CreatePage::class)->assertOk();
        $blockKey = array_key_first($form->get('data.blocks'));
        $this->assertSame('vue_richtext', $form->get("data.blocks.{$blockKey}.type"));
        $this->assertArrayHasKey('Basic page content', BlockRegistry::createOptions());
        $this->assertCount(2, $form->instance()->getSteps());

        $form->set('data.translations.en.title', 'Shipping News')
            ->assertSet('data.slug', 'shipping-news')
            ->assertSet("data.blocks.{$blockKey}.translations.en.headline", 'Shipping News')
            ->set("data.blocks.{$blockKey}.translations.en.content", '<p>Page content</p>')
            ->call('create')->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['slug' => 'shipping-news', 'page_type' => 'page', 'status' => 'draft']);
    }

    public function test_service_page_starts_with_banner_and_service_details(): void
    {
        $this->signInAsPageEditor();

        $form = Livewire::test(CreateServicePage::class)->assertOk();
        $blockKeys = array_keys($form->get('data.blocks'));
        $this->assertSame('vue_header', $form->get("data.blocks.{$blockKeys[0]}.type"));
        $this->assertSame('vue_service_details', $form->get("data.blocks.{$blockKeys[1]}.type"));
        $form->set('data.translations.en.title', 'Sea Freight')
            ->assertSet('data.slug', 'sea-freight')
            ->assertSet("data.blocks.{$blockKeys[0]}.translations.en.title", 'Sea Freight')
            ->assertSet("data.blocks.{$blockKeys[1]}.translations.en.title", 'Sea Freight')
            ->set("data.blocks.{$blockKeys[1]}.translations.en.description", '<p>Shipping by sea</p>')
            ->call('create')->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', ['slug' => 'sea-freight', 'page_type' => 'service', 'status' => 'draft']);
    }

    private function signInAsPageEditor(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'web'));
        $user->givePermissionTo([
            Permission::findOrCreate('pages.create', 'web'),
            Permission::findOrCreate('pages.view', 'web'),
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($user);
    }
}
