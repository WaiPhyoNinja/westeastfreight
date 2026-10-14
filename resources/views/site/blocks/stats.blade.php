<section class="stats">
    <div class="stats-grid">
        @foreach(preg_split('/\r\n|\r|\n/', $content['items'] ?? '') as $item)@php([$number, $label] = array_pad(explode('|', $item, 2), 2, ''))@if(trim($number))
        <div><strong>{{ trim($number) }}</strong><span>{{ trim($label) }}</span></div>@endif@endforeach
    </div>
</section>
