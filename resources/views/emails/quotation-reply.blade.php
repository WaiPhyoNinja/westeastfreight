@php
    $shipment = $quote->details ?? [];
    $pricing = $quotationDetails ?? [];
    $origin = implode(', ', array_filter([$shipment['sender_city'] ?? null, $shipment['sender_country'] ?? null]));
    $destination = implode(', ', array_filter([$shipment['receiver_city'] ?? null, $shipment['receiver_country'] ?? null]));
    $routeRows = array_filter([
        'Transport mode' => isset($shipment['transport_mode']) ? ucfirst($shipment['transport_mode']) : null,
        'Incoterms' => isset($shipment['incoterms']) ? ($shipment['incoterms'] === 'unknown' ? 'Not specified' : $shipment['incoterms']) : null,
        'Required arrival date' => $shipment['arrival_date'] ?? null,
        'Origin' => $origin,
        'Destination' => $destination,
        'Service' => isset($shipment['service_type']) ? ucwords(str_replace('_', ' ', $shipment['service_type'])) : null,
    ]);
    $cargoRows = array_filter([
        'Packaging' => isset($shipment['packaging']) ? ucfirst($shipment['packaging']) : null,
        'Container' => $shipment['container_type'] ?? null,
        'Temperature range' => $shipment['temperature_range'] ?? null,
        'Shipment type' => isset($shipment['shipment_type']) ? ucwords(str_replace('_', ' ', $shipment['shipment_type'])) : null,
        'Description' => $shipment['description'] ?? null,
        'Quantity' => isset($shipment['quantity']) ? $shipment['quantity'].' package(s)' : null,
        'Weight' => isset($shipment['weight']) ? $shipment['weight'].' kg' : null,
        'Dimensions' => filled($shipment['length'] ?? null) && filled($shipment['width'] ?? null) && filled($shipment['height'] ?? null)
            ? $shipment['length'].' × '.$shipment['width'].' × '.$shipment['height'].' cm' : null,
        'Insurance requested' => isset($shipment['insurance']) ? ($shipment['insurance'] ? 'Yes' : 'No') : null,
    ], fn ($value) => $value !== null && $value !== '');
    $currency = $pricing['currency'] ?? '';
@endphp
<x-mail.layout :title="'Freight quotation #' . $quote->id" preheader="Your freight quotation from West East Freight is ready.">
    <div style="padding:0 0 12px;color:#585b6b;font-size:12px;font-weight:400;">QUOTATION READY · REFERENCE #{{ $quote->id }}</div>
    <p style="margin:22px 0 14px;">Hello {{ filled($recipientName ?? null) ? $recipientName : $quote->user->name }},</p>
    <p style="margin:0 0 24px;">Thank you for your enquiry. Please review the shipment and pricing details below.</p>

    <x-mail.details heading="Shipment route" :rows="$routeRows" />
    <x-mail.details heading="Cargo details" :rows="$cargoRows" />

    @if (isset($pricing['freight_charge'], $pricing['total']))
        <h2 style="margin:24px 0 10px;color:#062f3a;font-size:17px;line-height:1.3;">Quoted charges</h2>
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;border:1px solid #e6e6e6;">
            <tr><td style="padding:11px 13px;border-bottom:1px solid #e6e6e6;font-size:13px;">Freight charge</td><td align="right" style="padding:11px 13px;border-bottom:1px solid #e6e6e6;font-size:13px;">{{ $currency }} {{ number_format((float) $pricing['freight_charge'], 2) }}</td></tr>
            <tr><td style="padding:11px 13px;border-bottom:1px solid #e6e6e6;font-size:13px;">Other charges</td><td align="right" style="padding:11px 13px;border-bottom:1px solid #e6e6e6;font-size:13px;">{{ $currency }} {{ number_format((float) ($pricing['additional_charges'] ?? 0), 2) }}</td></tr>
            <tr style="background-color:#f4f5f9;color:#062f3a;"><th scope="row" align="left" style="padding:13px;font-size:15px;">Total quoted</th><td align="right" style="padding:13px;font-size:15px;font-weight:700;">{{ $currency }} {{ number_format((float) $pricing['total'], 2) }}</td></tr>
        </table>
    @endif

    @if (filled($pricing['estimated_transit'] ?? null) || filled($pricing['valid_until'] ?? null))
        <p style="margin:18px 0 0;font-size:13px;line-height:1.7;">
            @if (filled($pricing['estimated_transit'] ?? null)) <strong>Estimated transit:</strong> {{ $pricing['estimated_transit'] }}<br> @endif
            @if (filled($pricing['valid_until'] ?? null)) <strong>Valid until:</strong> {{ $pricing['valid_until'] }} @endif
        </p>
    @endif

    @if (filled($pricing['terms'] ?? null))
        <h2 style="margin:24px 0 8px;color:#062f3a;font-size:17px;">Services and conditions</h2>
        <p style="margin:0;white-space:pre-line;">{{ $pricing['terms'] }}</p>
    @endif

    <h2 style="margin:24px 0 8px;color:#062f3a;font-size:17px;">Message from our team</h2>
    <p style="margin:0 0 22px;white-space:pre-line;">{{ $messageBody }}</p>
    <p style="margin:0;">Please reply to this email if you would like to discuss this quotation.</p>
    <p style="margin:18px 0 0;">West East Freight Team</p>
</x-mail.layout>
