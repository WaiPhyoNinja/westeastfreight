<?php

namespace Tests\Feature;

use App\Mail\QuoteRequestReceivedMail;
use App\Models\QuotationCountry;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QuoteSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        QuotationCountry::firstOrCreate(['short_name' => 'MM'], ['name' => 'Myanmar', 'status' => true]);
        $data = ['shipment_type' => 'freight', 'service_type' => 'standard', 'weight' => 2, 'quantity' => 1, 'description' => 'Audit sample', 'insurance' => false];
        foreach (['sender', 'receiver'] as $party) {
            $data += [$party.'_first_name' => 'Sample', $party.'_last_name' => 'User', $party.'_email' => 'sample@example.test', $party.'_phone' => '0912345678', $party.'_address' => 'Sample address', $party.'_city' => 'Yangon', $party.'_country' => 'Myanmar'];
        }

        return $data;
    }

    public function test_invalid_types_limits_and_enum_values_are_rejected(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $this->postJson('/api/frontend/items/quote_requests', [...$this->payload(),
            'weight' => -1, 'quantity' => 1.5, 'description' => str_repeat('a', 5001),
            'sender_first_name' => ['nested'], 'sender_email' => "sample@example.test\r\nBcc: other@example.test",
            'shipment_type' => 'invalid', 'insurance' => 'yes', 'declared_value' => -1,
            'sender_country' => "Myanmar' OR 1=1 --",
        ])->assertUnprocessable()->assertJsonValidationErrors(['weight', 'quantity', 'description', 'sender_first_name', 'sender_email', 'shipment_type', 'insurance', 'declared_value', 'sender_country']);
        $this->assertDatabaseCount('quote_requests', 0);
    }

    public function test_html_is_stored_as_text_and_escaped_in_receipt(): void
    {
        Queue::fake();
        Mail::fake();
        $this->actingAs(User::factory()->create());
        $payload = '<img src=x onerror="window.__quoteAuditXss=1">';
        $id = $this->postJson('/api/frontend/items/quote_requests', [...$this->payload(), 'description' => $payload])
            ->assertCreated()->assertJsonPath('data.description', $payload)->json('data.id');
        $html = (new QuoteRequestReceivedMail(QuoteRequest::findOrFail($id)->load('user')))->render();
        $this->assertStringNotContainsString($payload, $html);
        $this->assertStringContainsString('&lt;img', $html);
    }

    public function test_quote_rate_limit_blocks_twenty_first_request(): void
    {
        $this->actingAs(User::factory()->create());
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/frontend/items/quote_requests', [])->assertUnprocessable();
        }
        $this->postJson('/api/frontend/items/quote_requests', [])->assertTooManyRequests();
    }

    // Characterization: documents a current validation gap, not desired behavior.
    public function test_legacy_payload_can_omit_all_freight_specific_fields(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $this->postJson('/api/frontend/items/quote_requests', $this->payload())->assertCreated();
    }

    public function test_past_arrival_date_and_non_phone_text_are_currently_accepted(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $this->postJson('/api/frontend/items/quote_requests', [...$this->payload(),
            'transport_mode' => 'road', 'incoterms' => 'DAP', 'departure_type' => 'door', 'arrival_type' => 'door',
            'arrival_date' => '2000-01-01', 'commodity_type' => 'general', 'packaging' => 'boxes', 'sender_phone' => 'not a phone',
        ])->assertCreated();
    }
}
