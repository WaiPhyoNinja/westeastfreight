<x-mail.layout title="Thank you for contacting us" preheader="We received your message and will reply by email.">
    <p style="margin:0 0 18px;">Hello {{ $submission->details['name'] ?? 'there' }},</p>
    <p style="margin:0 0 20px;">Thank you for reaching out. We received your message and our team will reply by email.</p>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#ffffff;border-top:1px solid #e6e6e6;border-bottom:1px solid #e6e6e6;">
        <tr><td style="padding:16px 20px;color:#585b6b;font-size:14px;line-height:1.6;">
            <strong style="color:#062f3a;">Message received</strong><br>
            Your request is now with our team.
        </td></tr>
    </table>
    <p style="margin:22px 0 0;">West East Freight Team</p>
</x-mail.layout>
