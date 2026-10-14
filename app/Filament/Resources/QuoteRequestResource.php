<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Models\QuoteRequest;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Invoices';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('quote-requests.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $sections = [Select::make('status')->options([
            'pending' => 'Pending', 'reviewed' => 'Reviewed', 'quoted' => 'Quoted',
            'approved' => 'Approved', 'rejected' => 'Rejected',
        ])->required()
            ->disableOptionWhen(fn (string $value, ?QuoteRequest $record): bool => $value === 'quoted' && $record?->status !== $value)
            ->disabled(fn (?QuoteRequest $record): bool => $record?->quotation_mail_status === 'queued')
            ->helperText('Use “Send quotation email” for Quoted. Approved changes the request status without creating an invoice.')];
        foreach (['sender', 'receiver'] as $party) {
            $sections[] = Section::make(ucfirst($party))->columns(2)->schema(
                collect(['first_name', 'last_name', 'company', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code'])
                    ->map(fn ($field) => TextInput::make('details.'.$party.'_'.$field)->label(ucwords(str_replace('_', ' ', $field)))->disabled())->all()
            );
        }
        $sections[] = Section::make('Shipment')->columns(3)->schema([
            ...collect(['transport_mode', 'incoterms', 'departure_type', 'arrival_type', 'arrival_date', 'commodity_type', 'hazardous_details', 'temperature_range', 'packaging', 'container_type', 'shipment_type', 'service_type', 'weight', 'length', 'width', 'height', 'quantity', 'declared_value'])
                ->map(fn ($field) => TextInput::make('details.'.$field)->label(ucwords(str_replace('_', ' ', $field)))->disabled())->all(),
            Toggle::make('details.insurance')->disabled(),
            Toggle::make('details.stackable')->disabled(),
            Textarea::make('details.description')->disabled()->columnSpanFull(),
            Textarea::make('details.special_instructions')->disabled()->columnSpanFull(),
        ]);
        $sections[] = Section::make('Quotation email')->schema([
            Placeholder::make('quotation_sent_status')->label('Last sent')
                ->content(fn (?QuoteRequest $record): string => match ($record?->quotation_mail_status) {
                    'queued' => 'Queued for delivery',
                    'failed' => 'Delivery attempt failed',
                    default => $record?->quotation_sent_at?->format('Y-m-d H:i') ?? 'Not sent yet',
                }),
            Textarea::make('quotation_message')->label('Last quotation message')->disabled()->dehydrated(false),
        ]);

        return $form->schema($sections);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('Request #')->sortable(),
            Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable(),
            Tables\Columns\TextColumn::make('details.sender_country')->label('From'),
            Tables\Columns\TextColumn::make('details.receiver_country')->label('To'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('invoice.invoice_number')
                ->label('Invoice No.')
                ->placeholder('Not created'),
            Tables\Columns\TextColumn::make('quotation_mail_status')->label('Email')->badge()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'sent' => 'Sent', 'queued' => 'Queued', 'failed' => 'Failed', default => '—',
                }),
            Tables\Columns\TextColumn::make('quotation_sent_at')->label('Quotation emailed')->dateTime()->placeholder('Not sent'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('viewInvoice')
                ->label('View invoice')
                ->icon('heroicon-o-eye')
                ->visible(fn (QuoteRequest $record): bool => $record->invoice()->exists())
                ->url(fn (QuoteRequest $record): string => route('invoices.print', $record->invoice()->firstOrFail()))
                ->openUrlInNewTab(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListQuoteRequests::route('/'), 'edit' => Pages\EditQuoteRequest::route('/{record}/edit')];
    }
}
