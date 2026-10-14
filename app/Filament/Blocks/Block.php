<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Tabs;

abstract class Block
{
    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function schema(): array
    {
        return [
            Tabs::make('Translations')
                ->tabs([
                    static::localeTab('English', 'en'),
                    static::localeTab('မြန်မာ', 'my'),
                    static::localeTab('ไทย', 'th'),
                ])
                ->columnSpanFull(),
        ];
    }

    protected static function localeTab(string $label, string $locale): Tabs\Tab
    {
        return Tabs\Tab::make($label)->schema(static::localeSchema($locale));
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    abstract protected static function localeSchema(string $locale): array;
}
