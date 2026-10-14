<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationCountryResource\Pages;
use App\Models\QuotationCountry;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuotationCountryResource extends Resource
{
    protected static ?string $model = QuotationCountry::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Quotation Countries';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('quotation-countries.manage') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('Country name')->required()->maxLength(255),
            TextInput::make('short_name')
                ->label('Short name')
                ->helperText('For example: MM, TH, or SG.')
                ->required()
                ->maxLength(10)
                // ->uppercase()
                ->unique(ignoreRecord: true),
            TextInput::make('postal_code')->label('Postal code')->maxLength(30),
            Toggle::make('status')->label('Active')->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Country name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('short_name')->label('Short name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('postal_code')->label('Postal code')->searchable(),
                Tables\Columns\IconColumn::make('status')->label('Status')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')->label('Status'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotationCountries::route('/'),
            'create' => Pages\CreateQuotationCountry::route('/create'),
            'edit' => Pages\EditQuotationCountry::route('/{record}/edit'),
        ];
    }
}
