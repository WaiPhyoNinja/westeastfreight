<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicePageResource\Pages;
use App\Models\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;

class ServicePageResource extends PageResource
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Service Pages';

    protected static ?string $navigationGroup = 'Website';

    protected static ?int $navigationSort = 2;

    protected static function defaultCreationBlock(): string
    {
        return 'vue_service_details';
    }

    protected static function defaultCreationBlocks(): array
    {
        return [['type' => 'vue_header'], ['type' => 'vue_service_details']];
    }

    protected static function creationBlockOptions(): array
    {
        return ['Service page' => ['vue_service_details' => 'Service details']] + parent::creationBlockOptions();
    }

    public static function getEloquentQuery(): Builder
    {
        return Page::query()->where('page_type', 'service');
    }

    protected static function pageTypeField(): Select
    {
        return Select::make('page_type')
            ->default('service')
            ->hidden()
            ->required();
    }

    public static function form(Form $form): Form
    {
        return parent::form($form);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServicePages::route('/'),
            'create' => Pages\CreateServicePage::route('/create'),
            'edit' => Pages\EditServicePage::route('/{record}/edit'),
        ];
    }
}
