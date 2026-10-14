<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;

class ContentBlock extends Block
{
    public static function schema(): array
    {
        return [
            TextInput::make('image_url')->label('Image URL')->url(),
            ...parent::schema(),
        ];
    }

    protected static function localeSchema(string $locale): array
    {
        return [
            TextInput::make("translations.{$locale}.eyebrow")->label('Small heading'),
            TextInput::make("translations.{$locale}.heading")->label('Heading')->required($locale === 'en'),
            RichEditor::make("translations.{$locale}.body")->label('Content')->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
        ];
    }
}
