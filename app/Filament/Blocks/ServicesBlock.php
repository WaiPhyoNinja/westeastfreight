<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ServicesBlock extends Block
{
    protected static function localeSchema(string $locale): array
    {
        return [
            TextInput::make("translations.{$locale}.eyebrow")->label('Small heading'),
            TextInput::make("translations.{$locale}.heading")->label('Heading')->required($locale === 'en'),
            RichEditor::make("translations.{$locale}.body")->label('Content')->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
            Textarea::make("translations.{$locale}.items")
                ->label('Service cards')
                ->helperText('One per line: Title | Description')
                ->rows(4),
        ];
    }
}
