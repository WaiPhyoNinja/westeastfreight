<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerQuoteDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_can_read_quote_details_and_deep_link_loads(): void
    {
        $this->withoutVite();
        $owner = User::factory()->create();
        $quote = QuoteRequest::create(['user_id' => $owner->id, 'status' => 'pending', 'details' => ['description' => '<script>alert(1)</script>']]);
        $url = '/api/frontend/items/quote_requests/'.$quote->id;
        $this->getJson($url)->assertUnauthorized();
        $this->actingAs(User::factory()->create())->getJson($url)->assertNotFound();
        $this->actingAs($owner)->getJson($url)->assertOk()->assertJsonPath('data.id', $quote->id)
            ->assertJsonPath('data.description', '<script>alert(1)</script>');
        $this->getJson('/api/frontend/items/quote_requests/999999')->assertNotFound();
        $this->get('/my-orders/'.$quote->id)->assertOk()->assertSee('id="app"', false);
    }
}
