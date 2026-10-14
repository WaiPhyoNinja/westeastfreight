<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterResource\Pages;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterResource extends WebsiteSubmissionResource
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $modelLabel = 'Newsletter signup';

    protected static ?string $pluralModelLabel = 'Newsletter';

    protected static string $submissionType = 'newsletter';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')->label('Email')->disabled(),
            Select::make('status')->options(['new' => 'New', 'reviewed' => 'Reviewed'])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('created_at')->label('Subscribed at')->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletters::route('/'),
            'edit' => Pages\EditNewsletter::route('/{record}/edit'),
        ];
    }
}
