@props(['title', 'preheader' => '', 'language' => 'en', 'logoSrc' => \App\Support\MailBranding::logoUrl()])
<!doctype html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title }}</title>
    <style>
        body, table, td { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
        table { mso-table-lspace:0pt; mso-table-rspace:0pt; }
        img { -ms-interpolation-mode:bicubic; }
        .mail-content { overflow-wrap:anywhere; word-wrap:break-word; }
        .mail-content table { table-layout:fixed; }
        .mail-content td, .mail-content th { overflow-wrap:anywhere; word-break:break-word; }
        @media only screen and (max-width:600px) {
            .mail-outer { padding:16px 8px !important; }
            .mail-header, .mail-content, .mail-footer { padding-left:8px !important; padding-right:8px !important; }
            .mail-title { font-size:22px !important; }
            .mail-content td, .mail-content th { padding:10px 9px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#ffffff;color:#062f3a;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none;font-size:1px;line-height:1px;color:#f4f5f9;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">{{ $preheader }}</div>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#ffffff;">
        <tr><td class="mail-outer" align="center" style="padding:24px 16px;">
            <!--[if mso]><table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;max-width:640px;background-color:#ffffff;">
                <tr><td class="mail-header" style="padding:0 0 20px;border-bottom:1px solid #e6e6e6;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td bgcolor="#062f3a" style="padding:12px 16px;background-color:#062f3a;">
                        <img src="{{ $logoSrc }}" alt="West East Freight Forwarding" width="180" style="display:block;width:180px;max-width:100%;height:auto;border:0;color:#ffffff;font-size:14px;">
                    </td></tr></table>
                </td></tr>
                <tr><td class="mail-content" style="padding:24px 0;">
                    <h1 class="mail-title" style="margin:0 0 20px;color:#062f3a;font-size:24px;line-height:1.3;font-weight:700;">{{ $title }}</h1>
                    <div style="color:#585b6b;font-size:14px;line-height:1.7;">{{ $slot }}</div>
                </td></tr>
                <tr><td class="mail-footer" style="padding:18px 0;background-color:#ffffff;border-top:1px solid #e6e6e6;color:#585b6b;font-size:12px;line-height:1.7;">
                    <strong style="color:#062f3a;font-size:14px;">West East Freight</strong><br>
                    This email was sent in response to your activity with West East Freight.
                </td></tr>
            </table>
            <!--[if mso]></td></tr></table><![endif]-->
        </td></tr>
    </table>
</body>
</html>
