<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Component;

class BlockRegistry
{
    public static function labelFor(?string $type): string
    {
        foreach (static::createOptions() as $options) {
            if (isset($options[$type ?? ''])) {
                return $options[$type];
            }
        }

        return str_replace('Vue West · ', '', static::options()[$type ?? ''] ?? 'New block');
    }

    /** @return array<string, array<string, string>> */
    public static function createOptions(): array
    {
        return [
            'Basic page content' => [
                'vue_richtext' => 'Text content',
                'vue_header' => 'Page banner',
                'content' => 'Text with image URL',
            ],
            'Content sections' => [
                'vue_about_us' => 'About section',
                'vue_services' => 'Services list',
                'vue_service_image' => 'Service cards with images',
                'vue_faqs' => 'Frequently asked questions',
                'vue_contact_card' => 'Contact cards',
                'vue_info' => 'Contact information',
                'vue_map_content' => 'Map and contact form',
                'vue_branded' => 'Brand logos',
            ],
            'Home page sections' => [
                'vue_masthead' => 'Home banner',
                'vue_quotes' => 'Quote banner',
                'vue_features' => 'Features',
                'vue_processflow' => 'Process steps',
                'vue_cta' => 'Call to action',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            'hero' => 'Hero',
            'services' => 'Service cards',
            'content' => 'Text + image',
            'stats' => 'Statistics',
            'cta' => 'Call to action',
            'simple_text' => 'Simple text',
            'vue_service_image' => 'Vue West · Service image grid',
            'vue_about_us' => 'Vue West · About us',
            'vue_map_content' => 'Vue West · Map & contact form',
            'vue_info' => 'Vue West · Contact information banner',
            'vue_contact_card' => 'Vue West · Contact cards',
            'vue_quotes' => 'Vue West · Quote request banner',
            'vue_services' => 'Vue West · Services',
            'vue_faqs' => 'Vue West · FAQs',
            'vue_textslider' => 'Vue West · Text slider',
            'vue_processflow' => 'Vue West · Process flow',
            'vue_branded' => 'Vue West · Brand logos',
            'vue_masthead' => 'Vue West · Home masthead',
            'vue_header' => 'Vue West · Page masthead',
            'vue_hero' => 'Vue West · Hero',
            'vue_features' => 'Vue West · Features',
            'vue_content' => 'Vue West · Content',
            'vue_cta' => 'Vue West · Call to action',
            'vue_richtext' => 'Vue West · Rich text',
            'vue_service_details' => 'Service details',
        ];
    }

    /** @return array<int, Component> */
    public static function schemaFor(?string $type): array
    {
        return match ($type) {
            'hero' => HeroBlock::schema(),
            'services' => ServicesBlock::schema(),
            'content' => ContentBlock::schema(),
            'stats' => StatsBlock::schema(),
            'cta' => CtaBlock::schema(),
            'simple_text' => SimpleTextBlock::schema(),
            'vue_service_image', 'vue_about_us', 'vue_map_content', 'vue_info',
            'vue_contact_card', 'vue_quotes', 'vue_services', 'vue_faqs',
            'vue_textslider', 'vue_processflow', 'vue_branded', 'vue_masthead',
            'vue_header', 'vue_hero', 'vue_features', 'vue_content', 'vue_cta',
            'vue_richtext' => VueWestBlock::schema($type),
            'vue_service_details' => ServiceDetailsBlock::schema(),
            default => [],
        };
    }
}
