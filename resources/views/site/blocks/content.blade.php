<section class="section content-block">
    <div>
        <p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p>
        <h2>{{ $content['heading'] ?? '' }}</h2>
        <div>{!! $content['body'] ?? '' !!}</div>
    </div>@if(!empty($block['image_url']))<img src="{{ $block['image_url'] }}" alt="">@endif
</section>
