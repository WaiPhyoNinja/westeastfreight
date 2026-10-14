<?php

namespace Tests\Feature;

use App\Mail\AccountActivityMail;
use App\Mail\CustomerWelcomeMail;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthMailDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_templates_use_site_logo_and_escape_customer_names(): void
    {
        SiteSetting::create(['header_logo' => 'site/logo.png']);
        $user = User::factory()->make(['name' => '<script>alert(1)</script>', 'email' => 'customer@example.test']);
        $reset = (new ResetPassword('sample-token'))->toMail($user);
        $templates = [
            (new CustomerWelcomeMail($user->name))->render(),
            (new AccountActivityMail($user->name, 'login', '2026-10-13 12:00 UTC'))->render(),
            (new AccountActivityMail($user->name, 'password_changed', '2026-10-13 12:00 UTC'))->render(),
            view($reset->view, $reset->viewData)->render(),
        ];
        foreach ($templates as $html) {
            $this->assertStringContainsString(url('/storage/site/logo.png'), $html);
            $this->assertStringNotContainsString('<script>', $html);
            $this->assertStringContainsString('&lt;script&gt;', $html);
        }
        $this->assertStringContainsString('sample-token', $templates[3]);
        $this->assertStringContainsString('60 minutes', $templates[3]);
    }

    public function test_only_successful_login_and_password_changes_queue_activity_mail(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => 'old-password']);
        $this->postJson('/api/frontend/auth/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnprocessable();
        Mail::assertNothingQueued();
        $this->postJson('/api/frontend/auth/login', ['email' => $user->email, 'password' => 'old-password'])->assertOk();
        Mail::assertQueued(AccountActivityMail::class, fn ($mail) => $mail->activity === 'login' && $mail->hasTo($user->email));
        $this->postJson('/api/frontend/auth/password', ['current_password' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertOk();
        Mail::assertQueued(AccountActivityMail::class, fn ($mail) => $mail->activity === 'password_changed' && $mail->queue === 'mail');
        Mail::assertQueuedCount(2);
    }
}
