<?php

namespace Tests\Feature;

use App\Filament\Resources\NewsletterResource;
use App\Filament\Resources\NewsletterResource\Pages\EditNewsletter;
use App\Filament\Resources\NewsletterResource\Pages\ListNewsletters;
use App\Filament\Resources\WebsiteSubmissionResource;
use App\Filament\Resources\WebsiteSubmissionResource\Pages\EditWebsiteSubmission;
use App\Filament\Resources\WebsiteSubmissionResource\Pages\ListWebsiteSubmissions;
use App\Models\User;
use App\Models\WebsiteSubmission;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WebsiteInboxNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_separate_lists_and_edit_routes_are_scoped_to_their_submission_type(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $staff = User::factory()->create();
        $staff->givePermissionTo(Permission::findOrCreate('submissions.manage', 'web'));
        $this->actingAs($staff);
        $contact = WebsiteSubmission::create(['type' => 'contact_us', 'email' => 'contact@example.test', 'status' => 'new', 'details' => []]);
        $newsletter = WebsiteSubmission::create(['type' => 'newsletter', 'email' => 'subscriber@example.test', 'status' => 'new', 'details' => []]);

        Livewire::test(ListWebsiteSubmissions::class)->assertCanSeeTableRecords([$contact])->assertCanNotSeeTableRecords([$newsletter]);
        Livewire::test(ListNewsletters::class)->assertCanSeeTableRecords([$newsletter])->assertCanNotSeeTableRecords([$contact]);
        $this->assertNull(WebsiteSubmissionResource::resolveRecordRouteBinding($newsletter->id));
        $this->assertNull(NewsletterResource::resolveRecordRouteBinding($contact->id));
        Livewire::test(EditNewsletter::class, ['record' => $newsletter->id])
            ->fillForm(['status' => 'reviewed'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('reviewed', $newsletter->fresh()->status);
        $this->assertSame('newsletter', $newsletter->fresh()->type);
        $this->assertSame('1', WebsiteSubmissionResource::getNavigationBadge());
        $this->assertNull(NewsletterResource::getNavigationBadge());
        $this->assertSame('Contact Us', WebsiteSubmissionResource::getNavigationLabel());
        $this->assertSame('Newsletter', NewsletterResource::getNavigationLabel());
    }

    public function test_both_inboxes_require_submission_permission(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Permission::findOrCreate('submissions.manage', 'web');
        $this->actingAs(User::factory()->create());
        Livewire::test(ListWebsiteSubmissions::class)->assertForbidden();
        Livewire::test(ListNewsletters::class)->assertForbidden();
    }
}
