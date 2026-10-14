<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_save_reload_and_clear_own_profile_details(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $details = ['phone' => '+95 9 123 456 789', 'company' => 'Sample Company', 'job_title' => 'Manager', 'address' => "နမူနာလိပ်စာ\nSecond floor", 'city' => 'Yangon', 'state' => 'Yangon Region', 'country' => 'Myanmar', 'postal_code' => '11181'];
        $base = ['first_name' => 'Sample', 'last_name' => 'Customer', 'email' => $user->email];
        $this->actingAs($user)->patchJson('/api/frontend/auth/profile', [...$base, 'profile_details' => $details, 'id' => $other->id])->assertOk()->assertJsonPath('data.profile_details.address', $details['address']);
        $this->assertSame($details, $user->fresh()->profile_details);
        $this->assertNull($other->fresh()->profile_details);
        $this->getJson('/api/frontend/auth/user')->assertOk()->assertJsonPath('data.profile_details.country', 'Myanmar');
        $this->patchJson('/api/frontend/auth/profile', $base)->assertOk();
        $this->assertSame($details, $user->fresh()->profile_details);
        $this->patchJson('/api/frontend/auth/profile', [...$base, 'profile_details' => ['phone' => null]])->assertOk()->assertJsonPath('data.profile_details.phone', null)->assertJsonPath('data.profile_details.city', 'Yangon');
    }

    public function test_profile_rejects_invalid_types_lengths_and_unknown_keys(): void
    {
        $user = User::factory()->create();
        $base = ['first_name' => 'Sample', 'email' => $user->email];
        $this->actingAs($user)->patchJson('/api/frontend/auth/profile', [...$base, 'profile_details' => [
            'phone' => 'not a phone', 'address' => str_repeat('a', 1001), 'city' => ['nested'], 'role' => 'admin',
        ]])->assertUnprocessable()->assertJsonValidationErrors(['profile_details', 'profile_details.phone', 'profile_details.address', 'profile_details.city']);
        $this->assertNull($user->fresh()->profile_details);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->patchJson('/api/frontend/auth/profile', [])->assertUnauthorized();
    }
}
