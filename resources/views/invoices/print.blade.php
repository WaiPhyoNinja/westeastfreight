@php
    $number = $invoice->invoice_number ?: '#'.$invoice->id;
    $headerLogoPath = $invoiceSettings?->header_logo ?: $site?->header_logo;
    $logo = filled($headerLogoPath)
        ? (preg_match('#^https?://#i', $headerLogoPath) ? $headerLogoPath : asset('storage/'.ltrim($headerLogoPath, '/')))
        : asset('assets/img/logo.png');
    $footerLogo = filled($invoiceSettings?->footer_logo)
        ? (preg_match('#^https?://#i', $invoiceSettings->footer_logo) ? $invoiceSettings->footer_logo : asset('storage/'.ltrim($invoiceSettings->footer_logo, '/')))
        : null;
    $companyName = $invoiceSettings?->company_name ?: ($site?->brand_name ?: 'West East Freight');
    $phone = $invoiceSettings?->phone ?: ($site?->phone_en ?: $site?->phone);
    $address = $invoiceSettings?->address ?: ($site?->address_en ?: $site?->address);
    $email = $invoiceSettings?->email ?: $site?->email;
    $itemGroups = $invoice->items
        ->groupBy(fn ($item) => filled($item->group_no) ? 'group-'.$item->group_no : 'item-'.$item->id)
        ->values();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $number }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#303030;color:#111;font:11px/1.3 "Times New Roman",Times,serif}.toolbar{display:flex;max-width:210mm;margin:18px auto 0;justify-content:flex-end}.toolbar button{padding:10px 18px;border:0;border-radius:6px;color:#fff;background:#083641;font:700 13px Arial;cursor:pointer}.sheet{width:min(210mm,calc(100% - 24px));min-height:297mm;margin:14px auto 36px;padding:8mm;background:#fff;box-shadow:0 5px 26px #0008}.invoice-frame{border:1px solid #222}.company-head{display:grid;grid-template-columns:125px minmax(0,1fr);gap:12px;align-items:center;padding:6px 16px 0}.logo img{display:block;width:118px;max-height:78px;object-fit:contain}.company-info{text-align:left;font-family:Arial,Helvetica,sans-serif}.company-info>strong{display:block;color:#123765;font-family:"Times New Roman",Times,serif;font-size:17px;text-transform:uppercase}.company-lines,.company-rich{margin-top:3px;font-size:10px;font-weight:600;line-height:1.45}.company-rich :is(p,ul,ol){margin:2px 0}.company-rich :is(ul,ol){padding-left:18px}.invoice-title{grid-column:1/-1;margin-top:-4px;text-align:center}.invoice-title h1{margin:0;color:#e31f16;font-size:20px}.date-row{display:flex;justify-content:flex-end;border-bottom:1px solid #333}.date-row span{min-width:78px;padding:3px 8px;border:1px solid #333;border-bottom:0;font-size:12px;font-weight:700}.date-row span+span{min-width:105px;border-left:0}.section{border-bottom:1px solid #333}.section-title{padding:4px 3px 1px;font-size:12px;font-weight:700}.bill-grid{display:grid;grid-template-columns:130px minmax(0,330px);padding:1px 3px 12px}.bill-grid dt,.shipment-grid dt{font-weight:700}.bill-grid dd,.shipment-grid dd{min-width:0;margin:0;border-bottom:1px dashed #555;overflow-wrap:anywhere}.bill-grid dt,.bill-grid dd{min-height:20px;padding:2px 0}.shipment-grid{display:grid;grid-template-columns:115px minmax(0,1fr) 90px minmax(0,.8fr);column-gap:7px;padding:5px 3px}.shipment-grid dt,.shipment-grid dd{min-height:20px;padding:2px 0}.items{width:100%;margin-top:20px;border-collapse:collapse;table-layout:fixed;border-left:0;border-right:0}.items th,.items td{padding:4px;border:1px solid #333;vertical-align:top;overflow-wrap:anywhere}.items tr>*:first-child{border-left:0}.items tr>*:last-child{border-right:0}.items th{text-align:center;font-size:13px}.items .no{width:6%;text-align:center}.items .description{width:45%}.items .price{width:15%;text-align:right}.items .qty{width:16%;text-align:center}.items .remark{width:18%}.items tbody tr{height:22px}.items .total-row td{height:auto;font-size:13px;font-weight:700}.notes{min-height:115px;padding:3px 44px 8px;border-top:0}.notes>strong{display:block;margin-bottom:3px;font-size:14px;text-decoration:none}.rich-content :is(p,ul,ol){margin:3px 0}.rich-content :is(ul,ol){padding-left:20px}.rich-content li{margin:3px 0}.invoice-footer{display:flex;min-height:34px;align-items:flex-end;justify-content:space-between;gap:20px;padding:0 8px 6px;color:#555;font:9px Arial}.invoice-footer img{display:block;width:125px;max-height:38px;object-fit:contain}
        @media(max-width:640px){body{background:#fff}.toolbar{margin:10px 12px}.sheet{width:100%;min-height:0;margin:0;padding:10px;box-shadow:none}.company-head{grid-template-columns:78px 1fr}.logo img{width:74px}.company-info{text-align:left}.company-info strong{font-size:13px}.invoice-title{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;text-align:left}.invoice-title h1{font-size:20px}.date-row{margin-top:5px}.shipment-grid{grid-template-columns:90px minmax(0,1fr)}.items{font-size:10px}.items th,.items td{padding:4px}.items .description{width:38%}.items .remark{width:20%}}
        @media print{@page{size:A4;margin:8mm}body{background:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact}.toolbar{display:none}.sheet{width:100%;min-height:0;margin:0;padding:0;box-shadow:none}.items tr{break-inside:avoid}.section,.items,.notes{break-inside:avoid}}
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Print / Save PDF</button></div>
    <main class="sheet">
        <header class="company-head">
            <div class="logo"><img src="{{ $logo }}" alt="{{ $companyName }}"></div>
            <div class="company-info"><strong>{{ $companyName }}</strong>@if(filled($invoiceHeaderHtml))<div class="company-rich">{!! $invoiceHeaderHtml !!}</div>@else<div class="company-lines">@if(filled($invoiceAddressHtml))<div class="company-rich">{!! $invoiceAddressHtml !!}</div>@elseif(filled($address)){{ $address }}@endif @if(filled($phone))<br>Tel: {{ $phone }}@endif @if(filled($invoiceSettings?->whatsapp)) · {{ $invoiceSettings->whatsapp }} (WhatsApp)@endif @if(filled($email))<br>Email: {{ $email }}@endif @if(filled($invoiceSettings?->secondary_email)), {{ $invoiceSettings->secondary_email }}@endif @if(filled($invoiceSettings?->website))<br>{{ $invoiceSettings->website }}@endif</div>@endif</div>
            <div class="invoice-title"><h1>{{ $invoiceSettings?->invoice_title ?: 'INVOICE' }}</h1></div>
        </header>
        <div class="invoice-frame">
        <div class="date-row"><span>Date</span><span>{{ $invoice->date?->format('d.m.Y') }}</span></div>
        <section class="section">
            <div class="section-title">ATTENTION TO</div>
            <dl class="bill-grid">
                <dt>Name</dt><dd>{{ $invoice->name }}</dd><dt>Company Name</dt><dd>{{ $invoice->company_name }}</dd>
                <dt>Address</dt><dd>{{ $invoice->address }}</dd><dt>Phone</dt><dd>{{ $invoice->phone }}</dd><dt>Email</dt><dd>{{ $invoice->email }}</dd>
            </dl>
        </section>
        <section class="section" style="border-top:0">
            <dl class="shipment-grid">
                <dt>CARRIER</dt><dd>{{ $invoice->carrier }}</dd><dt>JOB NO.</dt><dd>{{ $number }}</dd>
                <dt>ORIGIN</dt><dd>{{ $invoice->origin }}</dd><dt>ETD POL</dt><dd>{{ $invoice->etd_pol }}</dd>
                <dt>FINAL DESTINATION</dt><dd>{{ $invoice->destination }}</dd><dt>ETA DEST</dt><dd>{{ $invoice->eta_dest }}</dd>
                <dt>VOLUME</dt><dd>{{ $invoice->volume }}</dd><dt>TERMS</dt><dd>{{ $invoice->terms_of_payment }}</dd>
                <dt>WEIGHT</dt><dd>{{ $invoice->weight }}</dd><dt>BL NO.</dt><dd>{{ $invoice->bl_no }}</dd>
                <dt>COMMODITY</dt><dd>{{ $invoice->commodity }}</dd><dt>CARGO TYPE</dt><dd>{{ $invoice->cargo_type }}</dd>
            </dl>
        </section>
        <table class="items">
            <thead>
                <tr>
                    <th class="no">No.</th>
                    <th class="description">Description</th>
                    <th class="price">
                        Unit Price
                        @if ($invoice->currency)
                            <br>({{ $invoice->currency }})
                        @endif
                    </th>
                    <th class="qty">Quantity</th>
                    <th class="remark">Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($itemGroups as $group)
                    @foreach($group as $item)
                        <tr>
                            @if($loop->first)<td class="no" rowspan="{{ $group->count() }}">{{ $item->group_no ?: $loop->parent->iteration }}</td>@endif
                            <td>{{ $item->description }}</td><td class="price">{{ $item->unit_price !== null ? number_format((float)$item->unit_price,2) : '' }}</td><td class="qty">{{ $item->quantity }}@if($item->unit)<br>{{ $item->unit }}@endif</td><td>{{ $item->remark }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td class="no"></td><td></td><td class="price"></td><td class="qty"></td><td></td></tr>
                @endforelse
                <tr class="total-row"><td colspan="2" style="text-align:center">TOTAL</td><td class="price">{{ number_format((float)$invoice->total_amount,2) }}</td><td colspan="2">{{ $invoice->currency ?: '' }}</td></tr>
            </tbody>
        </table>
        <div class="notes">
            <strong>Terms &amp; Conditions</strong>
            @if(filled($invoiceTermsHtml))
                <div class="rich-content">{!! $invoiceTermsHtml !!}</div>
            @elseif(filled($invoiceSettings?->terms_and_conditions))
                <ol>@foreach($invoiceSettings->terms_and_conditions as $term)<li>{{ $term['text'] ?? '' }}</li>@endforeach</ol>
            @elseif($invoice->terms_of_payment)
                <p>{{ $invoice->terms_of_payment }}</p>
            @endif
        </div>
        <div class="invoice-footer"><span>{{ $invoiceSettings?->footer_note ?: 'This invoice was generated from the West East Freight invoice system.' }}</span>@if($footerLogo)<img src="{{ $footerLogo }}" alt="">@endif</div>
        </div>
    </main>
</body>
</html>
