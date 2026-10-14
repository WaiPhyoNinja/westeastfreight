<x-mail.layout title="Reset your password" preheader="Use this secure link to reset your West East Freight password.">
    <p style="margin:0 0 16px;">Hello {{ $customerName }},</p>
    <p style="margin:0 0 16px;">We received a request to reset the password for your West East Freight account.</p>
    <x-mail.button :url="$resetUrl">Reset Password</x-mail.button>
    <p style="margin:0 0 16px;">This link expires in {{ $expiresIn }} minutes. If you did not request a password reset, you can ignore this email. Your password will remain unchanged.</p>
    <p style="margin:0 0 8px;">If the button does not work, copy this link into your browser:</p>
    <p style="margin:0;word-break:break-all;"><a href="{{ $resetUrl }}" style="color:#062f3a;word-break:break-all;">{{ $resetUrl }}</a></p>
</x-mail.layout>
