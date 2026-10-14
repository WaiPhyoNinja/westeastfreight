<?php

namespace App\Support;

use App\Models\SiteSetting;

class MailBranding
{
    public static function logoUrl(): string
    {
        $logo = SiteSetting::query()->value('header_logo');

        if (! $logo) {
            return (string) config('mail.brand_logo_url');
        }

        return preg_match('#^https?://#i', $logo) ? $logo : url('/storage/'.ltrim($logo, '/'));
    }
}
