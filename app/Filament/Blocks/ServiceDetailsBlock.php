<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class ServiceDetailsBlock
{
    public static function schema(): array
    {
        return [
            FileUpload::make('image')->label('Main service image')->image()->disk('public')
                ->directory('page-blocks/services')->visibility('public')->imageEditor()->maxSize(4096)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            Select::make('image_alignment')->label('Image alignment')
                ->options(['center' => 'Center', 'left' => 'Left', 'right' => 'Right', 'top' => 'Top'])
                ->default('center')->helperText('Choose which part of the image stays visible when cropped.'),
            Tabs::make('Service content')->tabs([
                static::languageTab('English', 'en', true),
                static::languageTab('မြန်မာ', 'my'),
                static::languageTab('ไทย', 'th'),
            ])->columnSpanFull(),
            Repeater::make('features')->label('Image cards')->maxItems(2)
                ->schema([
                    FileUpload::make('image')->label('Card image')->image()->disk('public')
                        ->directory('page-blocks/services')->visibility('public')->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    Tabs::make('Card text')->tabs([
                        static::cardTab('English', 'en'),
                        static::cardTab('မြန်မာ', 'my'),
                        static::cardTab('ไทย', 'th'),
                    ])->columnSpanFull(),
                ])->itemLabel(fn (array $state): ?string => $state['translations']['en']['title'] ?? 'Image card')
                ->collapsible()->reorderable()->defaultItems(0)->columnSpanFull(),
        ];
    }

    private static function languageTab(string $label, string $locale, bool $required = false): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema([
            TextInput::make("translations.{$locale}.title")->label('Heading')->required($required),
            RichEditor::make("translations.{$locale}.description")->label('Description'),
            Repeater::make("translations.{$locale}.bullets")
                ->label('Highlights')->simple(TextInput::make('text'))->defaultItems(0),
            Repeater::make("translations.{$locale}.faq")->label('FAQs')
                ->schema([
                    TextInput::make('title')->label('Question')->required(),
                    RichEditor::make('description')->label('Answer')->required(),
                ])->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Question')
                ->collapsible()->defaultItems(0),
        ]);
    }

    private static function cardTab(string $label, string $locale): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema([
            TextInput::make("translations.{$locale}.title")->label('Card title'),
            RichEditor::make("translations.{$locale}.description")->label('Card description'),
        ]);
    }
}
