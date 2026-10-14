@php($isLogin = $activity === 'login')
<x-mail.layout :title="$isLogin ? 'New account sign-in' : 'Password changed successfully'" :preheader="$isLogin ? 'Your West East Freight account was signed in to.' : 'Your new password is ready to use.'">
    <p style="margin:0 0 16px;">Hello {{ $customerName }},</p>
    <p style="margin:0 0 16px;">{{ $isLogin ? 'A successful sign-in was recorded for your West East Freight account.' : 'The password for your West East Freight account has been changed successfully.' }}</p>
    <p style="margin:0 0 16px;"><strong>Time:</strong> {{ $occurredAt }}</p>
    <p style="margin:0 0 16px;">If this was you, no further action is needed. If you did not do this, reset your password and contact our team.</p>
    <x-mail.button :url="url('/login')">Go to Login</x-mail.button>
</x-mail.layout>
