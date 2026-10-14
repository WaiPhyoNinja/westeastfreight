<x-mail.layout :title="$greeting . ', ' . $customerName" :preheader="$welcomeText" :language="$language === 'mm' ? 'my' : ($language === 'th' ? 'th' : 'en')">
    <p style="margin:0 0 20px;">{{ $welcomeText }}</p>
    <x-mail.button :url="url('/login')">{{ match ($language) { 'mm' => 'အကောင့်ဝင်ရန်', 'th' => 'เข้าสู่ระบบ', default => 'Log In' } }}</x-mail.button>
    <p style="margin:0;">{{ $closing }}</p>
</x-mail.layout>
