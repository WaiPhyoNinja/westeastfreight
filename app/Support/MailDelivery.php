<?php

namespace App\Support;

class MailDelivery
{
    public static function isConfigured(): bool
    {
        $mailer = config('mail.default');
        $from = config('mail.from.address');

        if (! is_string($mailer) || in_array($mailer, ['log', 'array'], true)) {
            return false;
        }

        if (in_array($mailer, ['failover', 'roundrobin'], true)) {
            $mailers = config("mail.mailers.{$mailer}.mailers", []);
            if (array_intersect($mailers, ['log', 'array'])) {
                return false;
            }
        }

        return is_string($from)
            && filter_var($from, FILTER_VALIDATE_EMAIL) !== false
            && ! str_ends_with(strtolower($from), '@example.com')
            && ! str_ends_with(strtolower($from), '@example.test');
    }

    public static function statusMessage(): string
    {
        if (! self::isConfigured()) {
            return 'Outgoing email is not configured. Set a delivery mailer and real MAIL_FROM_ADDRESS in the server environment.';
        }

        return 'Mailer: '.config('mail.default').'. From: '.config('mail.from.address').'.';
    }
}
