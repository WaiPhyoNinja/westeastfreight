<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeaderNavigationResource\Pages;
use Filament\Forms\Form;

class HeaderNavigationResource extends SiteSettingResource
{
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'Header navigation';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canAny(['navigation.manage', 'settings.manage']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::headerNavigationSchema());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeaderNavigation::route('/'),
            'edit' => Pages\EditHeaderNavigation::route('/{record}/edit'),
        ];
    }
}
