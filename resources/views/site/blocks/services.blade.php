<section id="services" class="section services">
    <div class="section-heading">@if(!empty($content['eyebrow']))
    <p class="eyebrow">{{ $content['eyebrow'] }}</p>@endif<h2>{{ $content['heading'] ?? '' }}</h2>
        <div>{!! $content['body'] ?? '' !!}</div>
    </div>
    <div class="service-grid">
        @foreach(preg_split('/\r\n|\r|\n/', $content['items'] ?? '') as $item)@php([$title, $description] = array_pad(explode('|', $item, 2), 2, ''))@if(trim($title))
            <article class="service-card"><span class="card-number">0{{ $loop->iteration }}</span>
                <h3>{{ trim($title) }}</h3>
                <p>{{ trim($description) }}</p><span class="arrow">↗</span>
        </article>@endif@endforeach
    </div>
</section>
