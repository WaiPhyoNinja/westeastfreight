<?php

namespace Tests\Feature;

use App\Filament\Resources\WebsiteSubmissionResource\Pages\EditWebsiteSubmission;
use App\Filament\Resources\WebsiteSubmissionResource\Pages\ListWebsiteSubmissions;
use App\Mail\ContactReceivedMail;
use App\Mail\ContactReplyMail;
use App\Jobs\SendContactReceipt;
use App\Jobs\SendContactReply;
use App\Models\User;
use App\Models\WebsiteSubmission;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use RuntimeException;
use Tests\TestCase;

class ContactRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_mailer_keeps_request_and_admin_notification_but_reports_email_not_sent(): void
    {
        Mail::fake();
        config()->set('mail.default', 'log');
        $staff = User::factory()->create();
        $staff->givePermissionTo(Permission::findOrCreate('submissions.manage', 'web'));

        $this->postJson('/api/frontend/items/contact_us', [
            'name' => 'Visitor', 'email' => 'visitor@example.test', 'message' => 'Please call me.',
        ])->assertCreated()->assertJsonPath('email_status', 'not_configured');

        Mail::assertNothingSent();
        $submission = WebsiteSubmission::query()->firstOrFail();
        $this->assertSame('not_configured', $submission->receipt_mail_status);
        $this->assertNull($submission->receipt_mail_sent_at);
        $this->assertCount(1, $staff->notifications);

        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($staff);
        Livewire::test(EditWebsiteSubmission::class, ['record' => $submission->id])
            ->callAction('resendReceipt')
            ->assertHasNoActionErrors();

        $this->assertDatabaseCount('jobs', 1);
        $this->assertSame('queued', $submission->fresh()->receipt_mail_status);
        $this->assertNull($submission->fresh()->receipt_mail_sent_at);
        $this->assertSame(1, WebsiteSubmission::query()->count());
    }

    public function test_mail_transport_failure_reports_failure_without_losing_contact_message(): void
    {
        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);

        $this->postJson('/api/frontend/items/contact_us', [
            'name' => 'Visitor', 'email' => 'visitor@example.test', 'message' => 'Please reply to me.',
        ])->assertCreated()->assertJsonPath('email_status', 'queued');

        $submission = WebsiteSubmission::query()->firstOrFail();
        $this->assertSame('Please reply to me.', $submission->details['message']);
        $this->assertSame('queued', $submission->receipt_mail_status);
        $this->assertNull($submission->receipt_mail_sent_at);
        (new SendContactReceipt($submission->id))->failed(new RuntimeException('SMTP unavailable'));
        $this->assertSame('failed', $submission->fresh()->receipt_mail_status);
    }

    public function test_contact_request_sends_receipt_and_notifies_staff_with_full_message_available_for_reply(): void
    {
        Mail::fake();
        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);
        $staff = User::factory()->create();
        $permission = Permission::findOrCreate('submissions.manage', 'web');
        $staff->givePermissionTo($permission);
        $admin = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo($permission);
        $admin->assignRole($adminRole);
        $customer = User::factory()->create();

        $this->postJson('/api/frontend/items/contact_us', [
            'name' => 'Visitor', 'email' => 'visitor@example.test', 'phone' => '+959123456789',
            'subject' => 'Road freight', 'message' => 'Please contact me about shipping to Yangon.',
        ])->assertCreated()->assertJsonPath('email_status', 'queued');

        $submission = WebsiteSubmission::query()->where('type', 'contact_us')->firstOrFail();
        $this->assertSame('Please contact me about shipping to Yangon.', $submission->details['message']);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertSame('queued', $submission->receipt_mail_status);
        $this->assertCount(1, $staff->notifications);
        $this->assertCount(1, $admin->notifications);
        $this->assertCount(0, $customer->notifications);
        $this->assertStringContainsString('Please contact me about shipping', $staff->notifications->first()->data['body']);
        $this->assertStringContainsString('/admin/website-submissions/'.$submission->id.'/edit', $staff->notifications->first()->data['actions'][0]['url']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($staff);
        Livewire::test(ListWebsiteSubmissions::class)->assertCanSeeTableRecords([$submission]);
        config()->set('mail.default', 'smtp');
        Livewire::test(EditWebsiteSubmission::class, ['record' => $submission->id])
            ->callAction('replyByEmail', ['message' => 'We will call you shortly.'])
            ->assertHasNoActionErrors();

        $this->assertDatabaseCount('jobs', 2);
        $this->assertSame('new', $submission->fresh()->status);
        $this->assertSame('queued', $submission->fresh()->reply_mail_status);
        $this->assertNull($submission->fresh()->replied_at);
        (new SendContactReceipt($submission->id))->handle();
        (new SendContactReply($submission->id, 'We will call you shortly.'))->handle();
        Mail::assertSent(ContactReceivedMail::class, fn (ContactReceivedMail $mail) => $mail->hasTo('visitor@example.test'));
        Mail::assertSent(ContactReplyMail::class, fn (ContactReplyMail $mail) => $mail->hasTo('visitor@example.test'));
        $this->assertSame('sent', $submission->fresh()->receipt_mail_status);
        $this->assertSame('reviewed', $submission->fresh()->status);
        $this->assertNotNull($submission->fresh()->replied_at);

        $this->postJson('/api/frontend/items/newsletter', ['email' => 'subscriber@example.test'])->assertCreated();
        $this->assertCount(1, $staff->fresh()->notifications);
    }
}
