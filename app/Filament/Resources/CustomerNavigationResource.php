<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerNavigationResource\Pages;
use Filament\Forms\Form;

class CustomerNavigationResource extends SiteSettingResource
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Customer header navigation';

    protected static ?string $pluralModelLabel = 'Customer header navigation';

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
        return $form->schema([static::customerNavigationSection()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerNavigation::route('/'),
            'edit' => Pages\EditCustomerNavigation::route('/{record}/edit'),
        ];
    }
}
