@php
    $details = $quote->details ?? [];
    $sender = array_filter([
        'Name' => trim(($details['sender_first_name'] ?? '').' '.($details['sender_last_name'] ?? '')),
        'Company' => $details['sender_company'] ?? null,
        'Address' => implode(', ', array_filter([
            $details['sender_address'] ?? null, $details['sender_city'] ?? null,
            $details['sender_state'] ?? null, $details['sender_postal_code'] ?? null,
            $details['sender_country'] ?? null,
        ], fn ($value) => filled($value))),
        'Email' => $details['sender_email'] ?? null,
        'Phone' => $details['sender_phone'] ?? null,
    ], fn ($value) => filled($value));
    $receiver = array_filter([
        'Name' => trim(($details['receiver_first_name'] ?? '').' '.($details['receiver_last_name'] ?? '')),
        'Company' => $details['receiver_company'] ?? null,
        'Address' => implode(', ', array_filter([
            $details['receiver_address'] ?? null, $details['receiver_city'] ?? null,
            $details['receiver_state'] ?? null, $details['receiver_postal_code'] ?? null,
            $details['receiver_country'] ?? null,
        ], fn ($value) => filled($value))),
        'Email' => $details['receiver_email'] ?? null,
        'Phone' => $details['receiver_phone'] ?? null,
    ], fn ($value) => filled($value));
    $route = array_filter([
        'Origin' => implode(', ', array_filter([$details['sender_city'] ?? null, $details['sender_country'] ?? null])),
        'Destination' => implode(', ', array_filter([$details['receiver_city'] ?? null, $details['receiver_country'] ?? null])),
        'Transport mode' => isset($details['transport_mode']) ? ucfirst($details['transport_mode']) : null,
        'Incoterms' => isset($details['incoterms']) ? ($details['incoterms'] === 'unknown' ? 'Not specified' : $details['incoterms']) : null,
        'Required arrival date' => $details['arrival_date'] ?? null,
    ], fn ($value) => filled($value));
    $shipment = array_filter([
        'Commodity type' => isset($details['commodity_type']) ? ucfirst($details['commodity_type']) : null,
        'Packaging' => isset($details['packaging']) ? ucfirst($details['packaging']) : null,
        'Container' => $details['container_type'] ?? null,
        'Temperature range' => $details['temperature_range'] ?? null,
        'Hazardous goods details' => $details['hazardous_details'] ?? null,
        'Stackable' => isset($details['stackable']) ? ($details['stackable'] ? 'Yes' : 'No') : null,
        'Shipment type' => isset($details['shipment_type']) ? ucwords(str_replace('_', ' ', $details['shipment_type'])) : null,
        'Service' => isset($details['service_type']) ? ucwords(str_replace('_', ' ', $details['service_type'])) : null,
        'Quantity' => isset($details['quantity']) ? $details['quantity'].' package(s)' : null,
        'Weight' => isset($details['weight']) ? $details['weight'].' kg' : null,
        'Dimensions' => filled($details['length'] ?? null) && filled($details['width'] ?? null) && filled($details['height'] ?? null)
            ? $details['length'].' × '.$details['width'].' × '.$details['height'].' cm' : null,
        'Declared value' => isset($details['declared_value']) ? 'USD '.number_format((float) $details['declared_value'], 2) : null,
        'Insurance requested' => isset($details['insurance']) ? ($details['insurance'] ? 'Yes' : 'No') : null,
        'Description' => $details['description'] ?? null,
        'Special instructions' => $details['special_instructions'] ?? null,
    ], fn ($value) => filled($value));
@endphp
<x-mail.layout title="Quotation request received" :preheader="'We received your shipment request #'.$quote->id.'. Our team will prepare your quotation.'">
    <div style="padding:0 0 12px;color:#585b6b;font-size:12px;font-weight:400;">REQUEST RECEIVED · REFERENCE #{{ $quote->id }}</div>
    <p style="margin:22px 0 14px;">Hello {{ filled($recipientName ?? null) ? $recipientName : $quote->user->name }},</p>
    <p style="margin:0 0 20px;">We received your shipment request. Please check the details below. Our team will review them and email you a quotation.</p>
    <x-mail.details heading="Shipment route" :rows="$route" />
    <x-mail.details heading="Shipment details" :rows="$shipment" />
    <x-mail.details heading="Sender details" :rows="$sender" />
    <x-mail.details heading="Receiver details" :rows="$receiver" />
    <p style="margin:24px 0 0;">If any detail needs correcting, please reply to this email before the quotation is prepared.</p>
    <p style="margin:18px 0 0;color:#062f3a;font-weight:600;">West East Freight Team</p>
</x-mail.layout>
