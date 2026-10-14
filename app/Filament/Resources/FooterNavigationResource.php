<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FooterNavigationResource\Pages;
use Filament\Forms\Form;

class FooterNavigationResource extends SiteSettingResource
{
    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Footer navigation';

    protected static ?int $navigationSort = 3;

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
        return $form->schema(static::footerNavigationSchema());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFooterNavigation::route('/'),
            'edit' => Pages\EditFooterNavigation::route('/{record}/edit'),
        ];
    }
}
