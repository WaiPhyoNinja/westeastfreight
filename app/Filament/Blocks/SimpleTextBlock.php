<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;

class SimpleTextBlock extends Block
{
    protected static function localeSchema(string $locale): array
    {
        return [
            TextInput::make("translations.{$locale}.heading")->label('Heading')->required($locale === 'en'),
            RichEditor::make("translations.{$locale}.body")->label('Content')->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
        ];
    }
}
