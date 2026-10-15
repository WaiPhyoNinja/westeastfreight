<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class VueWestBlock
{
    /** @return array<int, Component> */
    public static function schema(string $type): array
    {
        $shared = match ($type) {
            'vue_about_us' => [static::image('image')],
            'vue_faqs' => [static::image('image')],
            'vue_branded' => [static::image('images')
                ->multiple()
                ->reorderable()
                ->panelLayout('grid')
                ->imagePreviewHeight('12rem')
                ->openable()
                ->extraAttributes(['class' => 'brand-logo-upload'])
                ->helperText('Drag logos to reorder them. They appear in one horizontal row on the website.')],
            'vue_masthead' => [static::image('masthead_image')],
            'vue_header' => [static::image('header_image'), static::image('header_bg_image'), static::image('animation_image')],
            'vue_hero' => [static::image('image'), TextInput::make('button_link')->label('Button link')],
            'vue_cta' => [
                ColorPicker::make('background_color')->label('Background colour')->default('#f8f9fa'),
                TextInput::make('button_link')->label('Button link'),
            ],
            'vue_service_image' => [
                Repeater::make('services')->schema([
                    static::image('image'),
                    static::serviceType(),
                    TextInput::make('link')->label('Link'),
                    ...static::translatedRepeaterFields([
                        TextInput::make('title')->required(),
                        RichEditor::make('description'),
                    ]),
                ])->columns(2)->collapsible()->columnSpanFull(),
            ],
            'vue_contact_card' => [
                Repeater::make('card')->schema([
                    static::image('image'),
                    ...static::translatedRepeaterFields([
                        TextInput::make('title')->required(),
                        RichEditor::make('description'),
                    ]),
                ])->columns(2)->collapsible()->columnSpanFull(),
            ],
            'vue_features' => [
                Repeater::make('features')->schema([
                    static::choice('icon', 'Icon', [
                        'icon-truck' => 'Truck', 'icon-plane' => 'Air freight',
                        'icon-shipment' => 'Sea freight', 'icon-worldwide-shipping' => 'Worldwide shipping',
                        'icon-delivery-man' => 'Delivery', 'icon-box1' => 'Box',
                        'icon-packaging' => 'Packaging', 'icon-phone-call' => 'Phone',
                        'icon-24-hours-service' => '24-hour service',
                    ])->searchable(),
                    ...static::translatedRepeaterFields([
                        TextInput::make('title')->required(),
                        Textarea::make('description')->rows(2),
                    ]),
                ])->columns(2)->collapsible()->columnSpanFull(),
            ],
            default => [],
        };

        if ($type === 'vue_branded' || $type === 'vue_service_image') {
            return $shared;
        }

        return [
            ...$shared,
            Tabs::make('Translations')->tabs([
                static::localeTab('English', 'en', $type),
                static::localeTab('မြန်မာ', 'my', $type),
                static::localeTab('ไทย', 'th', $type),
            ])->columnSpanFull(),
        ];
    }

    private static function localeTab(string $label, string $locale, string $type): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema(static::localeSchema($locale, $type));
    }

    /** @return array<int, Component> */
    private static function localeSchema(string $locale, string $type): array
    {
        $field = fn (string $name): string => "translations.{$locale}.{$name}";

        return match ($type) {
            'vue_about_us' => [
                TextInput::make($field('title_tagline'))->label('Tagline'),
                TextInput::make($field('header'))->label('Heading')->required($locale === 'en'),
                RichEditor::make($field('content')),
                TextInput::make($field('animation_text'))->label('Circle animation text'),
                TextInput::make($field('link_text'))->label('Button label'),
                TextInput::make($field('link'))->label('Button link'),
                Repeater::make($field('content_descrption'))->schema([
                    RichEditor::make('content_text')->label('Description'),
                    Repeater::make('content_label')->simple(TextInput::make('label'))->label('Checklist'),
                ])->label('Highlights')->collapsible(),
            ],
            'vue_map_content' => [
                TextInput::make($field('title'))->required($locale === 'en'),
                Textarea::make($field('map_link'))->label('Google Maps iframe')->rows(5),
            ],
            'vue_info' => [
                TextInput::make($field('title'))->required($locale === 'en'),
                RichEditor::make($field('content')),
                TextInput::make($field('link_text'))->label('Button label'),
                TextInput::make($field('cta_link'))->label('Button link'),
                static::image($field('icon')),
            ],
            'vue_quotes' => [
                TextInput::make($field('title'))->label('Tagline'),
                TextInput::make($field('header'))->label('Heading')->required($locale === 'en'),
                RichEditor::make($field('content')),
                TextInput::make($field('animation_text'))->helperText('Comma-separated animated words'),
            ],
            'vue_services' => [
                TextInput::make($field('title'))->label('Tagline'),
                TextInput::make($field('header'))->label('Heading')->required($locale === 'en'),
                Repeater::make($field('services'))->schema([
                    TextInput::make('title')->required(),
                    RichEditor::make('description'),
                    static::serviceType(),
                    TextInput::make('link'),
                ])->columns(2)->collapsible(),
            ],
            'vue_faqs' => [
                TextInput::make($field('title'))->label('Tagline'),
                TextInput::make($field('header'))->label('Heading')->required($locale === 'en'),
                TextInput::make($field('year'))->numeric(),
                TextInput::make($field('year_text'))->label('Experience label'),
                Repeater::make($field('faq_content'))->schema([
                    TextInput::make('accordion_title')->label('Question')->required(),
                    RichEditor::make('accordion_content')->label('Answer')->required(),
                ])->collapsible(),
            ],
            'vue_processflow' => [
                TextInput::make($field('title'))->label('Tagline'),
                TextInput::make($field('header'))->label('Heading')->required($locale === 'en'),
                Repeater::make($field('content'))->schema([
                    static::choice('number', 'Step number / icon', [
                        '01' => '01 — Contact', '02' => '02 — Box',
                        '03' => '03 — Packaging', '04' => '04 — Transport',
                    ])->placeholder('Automatic by position'),
                    TextInput::make('title')->required(),
                    RichEditor::make('description'),
                ])->columns(2)->collapsible(),
            ],
            'vue_textslider' => [
                Repeater::make($field('items'))->schema([
                    TextInput::make('text')->required(),
                ])->label('Sliding text items')->collapsible(),
            ],
            'vue_masthead' => [
                TextInput::make($field('sub_title'))->label('Subtitle'),
                TextInput::make($field('title'))->required($locale === 'en'),
                TextInput::make($field('type_effect'))->helperText('Comma-separated animated words'),
                RichEditor::make($field('content')),
                TextInput::make($field('link_text'))->label('Button label'),
                TextInput::make($field('link'))->label('Button link'),
            ],
            'vue_header' => [
                TextInput::make($field('title'))->required($locale === 'en'),
                Repeater::make($field('link'))->schema([
                    TextInput::make('link_text')->label('Label')->required(),
                    TextInput::make('link')->label('URL'),
                ])->label('Breadcrumbs')->columns(2)->defaultItems(0),
            ],
            'vue_hero' => [
                TextInput::make($field('title'))->required($locale === 'en'),
                Textarea::make($field('description'))->rows(3),
                TextInput::make($field('button_text'))->label('Button label'),
            ],
            'vue_features' => [
                TextInput::make($field('title'))->required($locale === 'en'),
                Textarea::make($field('subtitle'))->rows(2),
            ],
            'vue_content' => [
                TextInput::make($field('heading'))->required($locale === 'en'),
                RichEditor::make($field('content')),
            ],
            'vue_richtext' => [
                TextInput::make($field('tagline'))->label('Tagline'),
                TextInput::make($field('headline'))->label('Heading')->required($locale === 'en'),
                RichEditor::make($field('content')),
                Select::make($field('alignment'))->label('Text alignment')
                    ->options(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])
                    ->default('center')->selectablePlaceholder(false),
            ],
            'vue_cta' => [
                TextInput::make($field('title'))->required($locale === 'en'),
                Textarea::make($field('description'))->rows(3),
                TextInput::make($field('button_text'))->label('Button label'),
            ],
            default => [],
        };
    }

    private static function serviceType(): Select
    {
        return static::choice('service_type', 'Service type', [
            'Air' => 'Air freight', 'Road' => 'Road freight', 'Sea' => 'Sea freight',
            'Ocean' => 'Ocean freight', 'Rail' => 'Rail freight',
            'Local' => 'Local delivery', 'International' => 'International shipping',
            'Personal' => 'Personal delivery', 'Emergency' => 'Emergency delivery',
        ])->searchable();
    }

    private static function choice(string $name, string $label, array $options): Select
    {
        return Select::make($name)->label($label)->options(function ($state) use ($options): array {
            // Keep existing custom values selectable when editing imported blocks.
            if (is_string($state) && $state !== '' && ! array_key_exists($state, $options)) {
                $options[$state] = $state.' (current value)';
            }

            return $options;
        });
    }

    private static function image(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(4096)
            ->disk('public')
            ->directory('page-blocks')
            ->visibility('public');
    }

    /**
     * Make a small translated item editable without duplicating the item itself.
     *
     * @param  array<int, Component>  $fields
     * @return array<int, Component>
     */
    private static function translatedRepeaterFields(array $fields): array
    {
        $make = function (string $locale) use ($fields): array {
            return array_map(function ($component) use ($locale) {
                $clone = clone $component;
                $clone->statePath("translations.{$locale}.".$clone->getName());

                return $clone;
            }, $fields);
        };

        return [
            Tabs::make('Translations')->tabs([
                Tabs\Tab::make('English')->schema($make('en')),
                Tabs\Tab::make('မြန်မာ')->schema($make('my')),
                Tabs\Tab::make('ไทย')->schema($make('th')),
            ])->columnSpanFull(),
        ];
    }
}
