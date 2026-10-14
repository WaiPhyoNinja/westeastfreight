<x-mail.layout title="Reply to your message" preheader="The West East Freight team has replied to your message.">
    <p style="margin:0 0 18px;">Hello {{ $submission->details['name'] ?? 'there' }},</p>
    <div style="margin:0 0 22px;padding:18px 20px;background-color:#ffffff;border-top:1px solid #e6e6e6;border-bottom:1px solid #e6e6e6;white-space:pre-line;">{{ $messageBody }}</div>
    <p style="margin:0;">West East Freight Team</p>
</x-mail.layout>
