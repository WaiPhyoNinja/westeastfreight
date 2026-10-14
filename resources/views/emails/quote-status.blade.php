@php
    $invoice = $stage === 'approved' ? $quote->invoice : null;
    $copy = match ($stage) {
        'reviewed' => ['title' => 'Shipment request under review', 'message' => 'Our team is reviewing the shipment information before preparing a quotation.'],
        'approved' => ['title' => 'Quotation approved', 'message' => $invoice
            ? 'Your quotation has been approved and an invoice has been issued.'
            : 'Your quotation has been approved. Our team will contact you about the next steps.'],
        'rejected' => ['title' => 'Quotation status update', 'message' => 'The quotation status has been updated to rejected. Please reply to this email if you would like to discuss the request.'],
        default => ['title' => 'Shipment request update', 'message' => 'The status of this shipment request has changed.'],
    };
    $details = $quote->details ?? [];
    $origin = implode(', ', array_filter([$details['sender_city'] ?? null, $details['sender_country'] ?? null]));
    $destination = implode(', ', array_filter([$details['receiver_city'] ?? null, $details['receiver_country'] ?? null]));
@endphp
<x-mail.layout :title="$copy['title']" :preheader="$copy['message']">
    <div style="padding:0 0 12px;color:#585b6b;font-size:12px;font-weight:400;">{{ strtoupper($stage) }} · REFERENCE #{{ $quote->id }}</div>
    <p style="margin:22px 0 14px;">Hello {{ $recipientName ?: $quote->user->name }},</p>
    <p style="margin:0 0 20px;">{{ $copy['message'] }}</p>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;border:1px solid #e6e6e6;">
        <tr><th scope="row" align="left" width="38%" style="padding:11px 13px;background-color:#f4f5f9;border-bottom:1px solid #e6e6e6;font-size:13px;">From</th><td style="padding:11px 13px;border-bottom:1px solid #e6e6e6;font-size:13px;">{{ $origin ?: '—' }}</td></tr>
        <tr><th scope="row" align="left" style="padding:11px 13px;background-color:#f4f5f9;font-size:13px;">To</th><td style="padding:11px 13px;font-size:13px;">{{ $destination ?: '—' }}</td></tr>
    </table>
    @if ($invoice)
        <h2 style="margin:22px 0 10px;color:#062f3a;font-size:17px;">Invoice issued</h2>
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;border:1px solid #e6e6e6;">
            <tr><th scope="row" align="left" width="38%" style="padding:11px 13px;background-color:#f4f5f9;border-bottom:1px solid #e6e6e6;font-size:13px;">Invoice number</th><td style="padding:11px 13px;border-bottom:1px solid #e6e6e6;font-size:13px;">{{ $invoice->invoice_number }}</td></tr>
            <tr><th scope="row" align="left" style="padding:11px 13px;background-color:#f4f5f9;font-size:13px;">Total</th><td style="padding:11px 13px;font-size:13px;font-weight:700;">{{ $invoice->currency }} {{ number_format((float) $invoice->total_amount, 2) }}</td></tr>
        </table>
    @endif
    <p style="margin:22px 0 0;">West East Freight Team</p>
</x-mail.layout>
