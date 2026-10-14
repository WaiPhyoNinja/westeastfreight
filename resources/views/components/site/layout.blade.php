@props(['page', 'locale', 'settings'])
<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    @php($pageContent = $page->translation($locale))
    <title>{{ $pageContent['meta_title'] ?? $pageContent['title'] ?? config('app.name') }}</title>
    @if (! empty($pageContent['meta_description']))<meta name="description" content="{{ $pageContent['meta_description'] }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ url('/' . $locale) }}">
            @if ($settings?->header_logo)
                <img class="brand-logo" src="{{ asset('storage/' . $settings->header_logo) }}" alt="{{ $settings->brand_name }}">
            @else
                <span class="brand-mark">{{ $settings?->brand_mark ?? 'WE' }}</span><span>{{ $settings?->brand_name ?? 'WEST EAST FREIGHT' }}</span>
            @endif
        </a>
        <nav class="main-nav">
            @foreach ($settings?->header_menu ?? [] as $item)
                <a href="{{ $item['url'] ?? '#' }}">{{ $item['label_' . $locale] ?? $item['label_en'] ?? '' }}</a>
            @endforeach
        </nav>
        <nav class="language-links" aria-label="Language selector">
            @foreach (['en' => 'EN', 'my' => 'မြန်မာ', 'th' => 'ไทย'] as $language => $label)
                @continue($language === $locale)
                @php($path = $page->slug === 'home' ? $language : $language . '/' . $page->slug)
                <a class="language-link" href="{{ url($path) }}">{{ $label }}</a>
            @endforeach
        </nav>
    </header>
    <main>{{ $slot }}</main>
    <footer id="contact" class="site-footer">
        <div class="brand"><span class="brand-mark">{{ $settings?->brand_mark ?? 'WE' }}</span><span>{{ $settings?->brand_name ?? 'WEST EAST FREIGHT' }}</span></div>
        <p>{{ $settings?->{'footer_description_' . $locale} ?? $settings?->footer_description_en }}</p>
        <nav class="footer-menu">
            @foreach ($settings?->footer_menu ?? [] as $item)
                <a href="{{ $item['url'] ?? '#' }}">{{ $item['label_' . $locale] ?? $item['label_en'] ?? '' }}</a>
            @endforeach
        </nav>
        <div class="footer-contact">
            @if ($settings?->email)<a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>@endif
            @php($phone = $settings?->{'phone_' . $locale} ?? $settings?->phone)
            @php($address = $settings?->{'address_' . $locale} ?? $settings?->address)
            @php($openHours = $settings?->{'open_hours_' . $locale} ?? $settings?->open_hours_en)
            @if ($phone)<a href="tel:{{ $phone }}">{{ $phone }}</a>@endif
            @if ($address)<span>{{ $address }}</span>@endif
            @if ($openHours)<span>{{ $openHours }}</span>@endif
        </div>
    </footer>
</body></html>
