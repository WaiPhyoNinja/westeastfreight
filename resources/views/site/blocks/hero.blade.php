<section class="hero" @if(!empty($block['image_url'])) style="--hero-image: url('{{ $block['image_url'] }}')" @endif>
    <div class="hero-copy">@if(!empty($content['eyebrow']))
    <p class="eyebrow">{{ $content['eyebrow'] }}</p>@endif<h1>{{ $content['heading'] ?? '' }}</h1>
        <div class="lead">{!! $content['body'] ?? '' !!}</div>@if(!empty($content['button_label']))<a class="button"
        href="{{ $content['button_url'] ?? '#' }}">{{ $content['button_label'] }} <span>→</span></a>@endif
    </div>
    <div class="hero-art" aria-hidden="true"><span>01</span><i></i><b></b></div>
</section>
