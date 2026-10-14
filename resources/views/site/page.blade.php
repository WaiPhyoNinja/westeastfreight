<x-site.layout :page="$page" :locale="$locale" :settings="$settings">
    @foreach ($page->blocks ?? [] as $block)
        @php($content = $block['translations'][$locale] ?? $block['translations']['en'] ?? [])
        @includeIf('site.blocks.' . ($block['type'] ?? ''), ['block' => $block, 'content' => $content])
    @endforeach
</x-site.layout>
