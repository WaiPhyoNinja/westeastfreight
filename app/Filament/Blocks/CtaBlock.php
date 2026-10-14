<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;

class CtaBlock extends Block
{
    protected static function localeSchema(string $locale): array
    {
        return [
            TextInput::make("translations.{$locale}.heading")->label('Heading')->required($locale === 'en'),
            RichEditor::make("translations.{$locale}.body")->label('Content')->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
            TextInput::make("translations.{$locale}.button_label")->label('Button label'),
            TextInput::make("translations.{$locale}.button_url")->label('Button URL')->url(),
        ];
    }
}
