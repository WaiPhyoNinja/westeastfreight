<?php

namespace Tests\Feature;

use App\Filament\Resources\QuoteRequestResource\Pages\EditQuoteRequest;
use App\Filament\Resources\QuoteRequestResource\Pages\ListQuoteRequests;
use App\Filament\Resources\WebsiteSubmissionResource\Pages\EditWebsiteSubmission;
use App\Mail\ContactReplyMail;
use App\Mail\ContactReceivedMail;
use App\Jobs\SendContactReply;
use App\Jobs\SendQuoteEmailDelivery;
use App\Mail\QuotationReplyMail;
use App\Mail\QuoteRequestReceivedMail;
use App\Mail\QuoteStatusMail;
use App\Models\QuoteEmailDelivery;
use App\Models\QuoteRequest;
use App\Models\Invoice;
use App\Support\QuoteMailDispatcher;
use App\Models\User;
use App\Models\WebsiteSubmission;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use RuntimeException;
use Tests\TestCase;

class CustomerEmailWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_request_pages_do_not_offer_invoice_creation(): void
    {
        Mail::fake();
        Queue::fake();

        $staff = User::factory()->create();
        $staff->givePermissionTo(Permission::findOrCreate('quote-requests.manage', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($staff);

        $quote = QuoteRequest::create([
            'user_id' => User::factory()->create()->id,
            'status' => 'quoted',
            'quotation_mail_status' => 'sent',
            'quotation_details' => [
                'currency' => 'USD',
                'freight_charge' => '100.00',
                'additional_charges' => '20.00',
                'total' => '120.00',
            ],
            'details' => [
                'sender_first_name' => 'Sender',
                'receiver_first_name' => 'Receiver',
                'receiver_email' => 'receiver@example.test',
            ],
        ]);

        Livewire::test(ListQuoteRequests::class)->assertDontSee('Approve & create invoice');
        Livewire::test(EditQuoteRequest::class, ['record' => $quote->id])->assertDontSee('Approve & create invoice');
        $this->assertSame(0, Invoice::where('quote_request_id', $quote->id)->count());
    }

    public function test_staff_can_send_quotation_and_contact_replies_without_crossing_recipients(): void
    {
        Mail::fake();
        Queue::fake();
        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);

        $staff = User::factory()->create();
        $staff->givePermissionTo([
            Permission::findOrCreate('quote-requests.manage', 'web'),
            Permission::findOrCreate('submissions.manage', 'web'),
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($staff);

        $customer = User::factory()->create(['email' => 'customer@example.test']);
        $quote = QuoteRequest::create(['user_id' => $customer->id, 'status' => 'pending', 'details' => [
            'sender_email' => 'other@example.test', 'receiver_email' => 'receiver@example.test',
            'sender_first_name' => 'Sender', 'receiver_first_name' => 'Receiver',
            'sender_city' => 'Yangon', 'sender_country' => 'Myanmar',
            'receiver_city' => 'Bangkok', 'receiver_country' => 'Thailand', 'weight' => 10,
            'description' => '<Fragile cargo>',
        ]]);
        $contact = WebsiteSubmission::create([
            'type' => 'contact_us', 'email' => 'visitor@example.test',
            'details' => ['name' => 'Visitor', 'subject' => 'Shipment question'],
        ]);

        Livewire::test(EditQuoteRequest::class, ['record' => $quote->id])
            ->callAction('sendQuotation', [
                'message' => 'Please review this quotation.', 'currency' => 'USD',
                'freight_charge' => 100, 'additional_charges' => 20,
                'estimated_transit' => '3–5 business days', 'valid_until' => now()->addDays(7)->toDateString(),
                'terms' => 'Includes door-to-door transport.',
            ])
            ->assertHasNoActionErrors();

        Queue::assertPushed(SendQuoteEmailDelivery::class, 2);
        $this->assertEqualsCanonicalizing(
            ['other@example.test', 'receiver@example.test'],
            QuoteEmailDelivery::where('quote_request_id', $quote->id)->pluck('recipient_email')->all(),
        );
        $this->assertSame('pending', $quote->fresh()->status);
        $this->assertSame('queued', $quote->fresh()->quotation_mail_status);
        $queuedJobs = Queue::pushed(SendQuoteEmailDelivery::class);
        foreach ($queuedJobs as $queuedJob) {
            $queuedJob->handle();
        }
        Mail::assertSent(QuotationReplyMail::class, 2);
        Mail::assertNotSent(QuotationReplyMail::class, fn (QuotationReplyMail $mail) => $mail->hasTo('customer@example.test'));
        foreach (['other@example.test', 'receiver@example.test'] as $recipient) {
            Mail::assertSent(QuotationReplyMail::class, fn (QuotationReplyMail $mail) => $mail->hasTo($recipient)
                && count($mail->to) === 1 && $mail->attachments === []);
        }
        $this->assertSame(2, QuoteEmailDelivery::where('quote_request_id', $quote->id)->where('status', 'sent')->count());
        $this->assertSame(0, QuoteEmailDelivery::where('quote_request_id', $quote->id)->where('type', 'status')->count());
        $this->assertSame('quoted', $quote->fresh()->status);
        $this->assertNotNull($quote->fresh()->quotation_sent_at);
        $this->assertSame('120.00', $quote->fresh()->quotation_details['total']);

        Livewire::test(EditQuoteRequest::class, ['record' => $quote->id])
            ->fillForm(['status' => 'approved'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('approved', $quote->fresh()->status);
        $this->assertSame(2, QuoteEmailDelivery::where('quote_request_id', $quote->id)->where('stage', 'approved')->count());
        $this->assertSame(0, Invoice::where('quote_request_id', $quote->id)->count());
        $approvedHtml = (new QuoteStatusMail($quote->fresh()->load('user'), 'approved', 'Receiver'))->render();
        $this->assertStringNotContainsString('Invoice issued', $approvedHtml);

        Livewire::test(EditWebsiteSubmission::class, ['record' => $contact->id])
            ->callAction('replyByEmail', ['message' => 'We can help with your shipment.'])
            ->assertHasNoActionErrors();

        Queue::assertPushed(SendContactReply::class, fn (SendContactReply $job) => $job->submissionId === $contact->id);
        $this->assertSame('new', $contact->fresh()->status);
        (new SendContactReply($contact->id, 'We can help with your shipment.'))->handle();
        Mail::assertSent(ContactReplyMail::class, fn (ContactReplyMail $mail) => $mail->hasTo('visitor@example.test') && $mail->messageBody === 'We can help with your shipment.');
        $this->assertSame('reviewed', $contact->fresh()->status);
        $this->assertNotNull($contact->fresh()->replied_at);

        $this->assertStringContainsString('Quotation request received', (new QuoteRequestReceivedMail($quote->load('user')))->render());
        $quotationHtml = (new QuotationReplyMail($quote, 'Please review this quotation.', $quote->fresh()->quotation_details))->render();
        $this->assertStringContainsString('USD 120.00', $quotationHtml);
        $this->assertStringContainsString('Quoted charges', $quotationHtml);
        $this->assertStringContainsString('Yangon, Myanmar', $quotationHtml);
        $this->assertStringContainsString('Bangkok, Thailand', $quotationHtml);
        $this->assertStringContainsString('&lt;Fragile cargo&gt;', $quotationHtml);
        $this->assertStringContainsString('Includes door-to-door transport.', $quotationHtml);
        $this->assertStringContainsString('src="https://westeastfreight.com/assets/img/logo.png?v=20261006"', $quotationHtml);
        $this->assertStringNotContainsString('attachment', strtolower($quotationHtml));
        $receiptHtml = (new ContactReceivedMail($contact))->render();
        $this->assertStringContainsString('Thank you for contacting us', $receiptHtml);
        $this->assertStringContainsString('alt="West East Freight Forwarding"', $receiptHtml);
        $this->assertStringContainsString('src="https://westeastfreight.com/assets/img/logo.png?v=20261006"', $receiptHtml);
        $this->assertStringNotContainsString('cid:', $receiptHtml);
        $this->assertStringNotContainsString('data:image/png', $receiptHtml);
        $this->assertStringContainsString('role="presentation"', $receiptHtml);

        $replyHtml = (new ContactReplyMail($contact, 'We can help with <your> shipment.'))->render();
        $this->assertStringContainsString('We can help with &lt;your&gt; shipment.', $replyHtml);
    }

    public function test_log_mailer_cannot_mark_an_unsent_quotation_as_sent(): void
    {
        Mail::fake();
        Queue::fake();
        config()->set('mail.default', 'log');

        $staff = User::factory()->create();
        $staff->givePermissionTo(Permission::findOrCreate('quote-requests.manage', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($staff);

        $quote = QuoteRequest::create(['user_id' => User::factory()->create()->id, 'status' => 'pending', 'details' => []]);
        Livewire::test(EditQuoteRequest::class, ['record' => $quote->id])
            ->callAction('sendQuotation', ['message' => 'Draft quotation.', 'currency' => 'USD', 'freight_charge' => 100, 'additional_charges' => 0])
            ->assertHasNoActionErrors();

        Queue::assertNotPushed(SendQuoteEmailDelivery::class);
        $this->assertSame('pending', $quote->fresh()->status);
        $this->assertNull($quote->fresh()->quotation_sent_at);
    }

    public function test_each_quotation_status_change_queues_one_private_email_per_unique_party(): void
    {
        Mail::fake();
        Queue::fake();
        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);

        $customer = User::factory()->create(['email' => 'sender@example.test', 'name' => 'Sender']);
        $quote = QuoteRequest::create(['user_id' => $customer->id, 'status' => 'pending', 'details' => [
            'sender_email' => 'SENDER@example.test', 'sender_first_name' => 'Sender',
            'receiver_email' => 'receiver@example.test', 'receiver_first_name' => 'Receiver',
            'sender_city' => 'Yangon', 'sender_country' => 'Myanmar',
            'receiver_city' => 'Bangkok', 'receiver_country' => 'Thailand',
            'quantity' => 2, 'weight' => 10, 'length' => 30, 'width' => 20, 'height' => 15,
            'description' => '<fragile> goods', 'insurance' => true,
        ]]);

        app(QuoteMailDispatcher::class)->queueStatus($quote, 'pending');
        $this->assertSame(2, QuoteEmailDelivery::where('quote_request_id', $quote->id)->where('stage', 'pending')->count());
        foreach (Queue::pushed(SendQuoteEmailDelivery::class) as $job) {
            $job->handle();
        }
        Mail::assertSent(QuoteRequestReceivedMail::class, 2);
        Mail::assertSent(QuoteRequestReceivedMail::class, fn (QuoteRequestReceivedMail $mail) =>
            $mail->hasTo('receiver@example.test') && count($mail->to) === 1 && $mail->recipientName === 'Receiver');
        $confirmationHtml = (new QuoteRequestReceivedMail($quote->load('user'), 'Receiver'))->render();
        $this->assertStringContainsString('Sender details', $confirmationHtml);
        $this->assertStringContainsString('Receiver details', $confirmationHtml);
        $this->assertStringContainsString('Shipment details', $confirmationHtml);
        $this->assertStringContainsString('10 kg', $confirmationHtml);
        $this->assertStringContainsString('30 × 20 × 15 cm', $confirmationHtml);
        $this->assertStringContainsString('&lt;fragile&gt; goods', $confirmationHtml);
        $this->assertStringContainsString('alt="West East Freight Forwarding"', $confirmationHtml);
        $this->assertStringContainsString('src="https://westeastfreight.com/assets/img/logo.png?v=20261006"', $confirmationHtml);
        $this->assertStringContainsString('REQUEST RECEIVED · REFERENCE', $confirmationHtml);

        $staff = User::factory()->create();
        $staff->givePermissionTo(Permission::findOrCreate('quote-requests.manage', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($staff);

        foreach (['reviewed', 'rejected'] as $stage) {
            if ($stage === 'reviewed') {
                Livewire::test(EditQuoteRequest::class, ['record' => $quote->id])
                    ->fillForm(['status' => $stage])->call('save')->assertHasNoFormErrors();
                $this->assertSame('receiver@example.test', $quote->fresh()->details['receiver_email'] ?? null);
            } else {
                $quote->update(['status' => $stage]);
            }
            $this->assertSame(2, QuoteEmailDelivery::where('quote_request_id', $quote->id)->where('stage', $stage)->count(), 'New '.$stage.' deliveries');
            foreach (QuoteEmailDelivery::where('quote_request_id', $quote->id)->where('stage', $stage)->get() as $delivery) {
                (new SendQuoteEmailDelivery($delivery->id))->handle();
            }
            $quote->refresh()->update(['status' => $stage]);
            $this->assertSame(2, QuoteEmailDelivery::where('quote_request_id', $quote->id)->where('stage', $stage)->count(), 'Repeated '.$stage.' deliveries');
        }
        Mail::assertSent(QuoteStatusMail::class, 4);

        $statusHtml = (new QuoteStatusMail($quote->load('user'), 'reviewed', 'Receiver'))->render();
        $this->assertStringContainsString('Shipment request under review', $statusHtml);
        $this->assertStringContainsString('Hello Receiver', $statusHtml);
        $this->assertStringContainsString('src="https://westeastfreight.com/assets/img/logo.png?v=20261006"', $statusHtml);
    }

    public function test_quotation_stays_unsent_until_both_parties_receive_their_email(): void
    {
        Mail::fake();
        Queue::fake();
        $customer = User::factory()->create(['email' => 'sender@example.test']);
        $quote = QuoteRequest::create(['user_id' => $customer->id, 'status' => 'pending', 'details' => [
            'sender_email' => 'sender@example.test', 'receiver_email' => 'receiver@example.test',
        ]]);

        app(QuoteMailDispatcher::class)->queueQuotation($quote, 'Quotation details.', [
            'currency' => 'USD', 'freight_charge' => '100.00', 'additional_charges' => '0.00', 'total' => '100.00',
        ]);
        $jobs = Queue::pushed(SendQuoteEmailDelivery::class)->values();
        $this->assertCount(2, $jobs);

        $jobs[0]->handle();
        $this->assertSame('pending', $quote->fresh()->status);
        $this->assertSame('queued', $quote->fresh()->quotation_mail_status);
        $jobs[1]->failed(new RuntimeException('SMTP unavailable'));
        $this->assertSame('failed', $quote->fresh()->quotation_mail_status);
        $this->assertNull($quote->fresh()->quotation_sent_at);

        $jobs[1]->handle();
        $this->assertSame('quoted', $quote->fresh()->status);
        $this->assertSame('sent', $quote->fresh()->quotation_mail_status);
        Mail::assertSent(QuotationReplyMail::class, 2);
    }

    public function test_approving_a_priced_quote_does_not_create_an_invoice(): void
    {
        config()->set('mail.default', 'array');
        $quote = QuoteRequest::create([
            'user_id' => User::factory()->create()->id,
            'status' => 'quoted',
            'details' => ['sender_first_name' => 'Test', 'sender_last_name' => 'Sender', 'sender_email' => 'sender@example.test'],
            'quotation_mail_status' => 'sent',
            'quotation_details' => ['currency' => 'USD', 'freight_charge' => '75.00', 'additional_charges' => '5.00', 'total' => '80.00'],
        ]);

        $quote->update(['status' => 'approved']);
        $this->assertSame('approved', $quote->fresh()->status);
        $this->assertNull($quote->invoice()->first());
        $quote->update(['status' => 'approved']);
        $this->assertSame(0, Invoice::where('quote_request_id', $quote->id)->count());
    }

    public function test_quote_without_sent_pricing_can_change_status_without_creating_an_invoice(): void
    {
        $quote = QuoteRequest::create(['user_id' => User::factory()->create()->id, 'status' => 'pending', 'details' => []]);
        $quote->update(['status' => 'approved']);
        $this->assertSame('approved', $quote->fresh()->status);
        $this->assertNull($quote->invoice()->first());
    }

    public function test_transport_failure_keeps_contact_reply_unsent(): void
    {
        config()->set(['mail.default' => 'smtp', 'mail.from.address' => 'noreply@westeastfreight.com']);
        Queue::fake();

        $staff = User::factory()->create();
        $staff->givePermissionTo(Permission::findOrCreate('submissions.manage', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($staff);

        $contact = WebsiteSubmission::create([
            'type' => 'contact_us', 'email' => 'visitor@example.test', 'details' => ['name' => 'Visitor'],
        ]);
        Livewire::test(EditWebsiteSubmission::class, ['record' => $contact->id])
            ->callAction('replyByEmail', ['message' => 'A reply.'])
            ->assertHasNoActionErrors();

        $this->assertSame('new', $contact->fresh()->status);
        $this->assertSame('queued', $contact->fresh()->reply_mail_status);
        (new SendContactReply($contact->id, 'A reply.'))->failed(new RuntimeException('Transport unavailable'));
        $this->assertSame('failed', $contact->fresh()->reply_mail_status);
        $this->assertNull($contact->fresh()->replied_at);
    }
}
