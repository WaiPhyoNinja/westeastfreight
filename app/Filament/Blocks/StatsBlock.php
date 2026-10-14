<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Textarea;

class StatsBlock extends Block
{
    protected static function localeSchema(string $locale): array
    {
        return [
            Textarea::make("translations.{$locale}.items")
                ->label('Statistics')
                ->helperText('One per line: Number | Label')
                ->rows(4),
        ];
    }
}
