<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $resetUrl = fn ($user, string $token) => url('/reset-password').'?'.http_build_query([
            'token' => $token, 'email' => $user->getEmailForPasswordReset(),
        ]);
        ResetPassword::createUrlUsing($resetUrl);
        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject('Reset your West East Freight password')
            ->action('Reset Password', $resetUrl($user, $token))
            ->view('emails.password-reset', [
                'customerName' => $user->name,
                'resetUrl' => $resetUrl($user, $token),
                'expiresIn' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]));
    }
}
