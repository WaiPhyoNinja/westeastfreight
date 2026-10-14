<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Invoices';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('invoices.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('invoices.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('invoices.update') ?? false;
    }

    public static function canDelete($record): bool
    {
        return (auth()->user()?->can('invoices.delete') ?? false) && ! $record->quote_request_id;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Invoice Information')
                    ->description('Invoice number, date, currency and transport method.')
                    ->schema([
                        TextInput::make('invoice_number')->label('Invoice No.')->placeholder('Generated automatically when left blank'),
                        DatePicker::make('date')->label('Invoice Date')->default(now())->required(),
                        TextInput::make('currency')->label('Currency')->placeholder('THB')->alpha()->length(3)->maxLength(3)->required(),
                        Select::make('carrier')
                            ->label('Carrier')
                            ->options([
                                'AIR' => '✈️ AIR FREIGHT',
                                'SEA' => '🚢 SEA FREIGHT',
                                'LAND' => '🚚 LAND FREIGHT',
                            ])
                            ->native(false)
                            ->searchable(),
                    ])->columns(['default' => 1, 'md' => 2, 'xl' => 4]),

                Section::make('Shipment Details')
                    ->description('Route, schedule and cargo information shown on the invoice.')
                    ->schema([
                        TextInput::make('origin')->label('Origin'),
                        TextInput::make('destination')->label('Final Destination'),
                        TextInput::make('etd_pol')->label('ETD POL'),
                        TextInput::make('eta_dest')->label('ETA DEST'),
                        TextInput::make('volume')->label('Volume')->placeholder('2 CBM'),
                        TextInput::make('weight')->label('Weight')->placeholder('1,000 KGS'),
                        TextInput::make('commodity')->label('Commodity'),
                        TextInput::make('terms_of_payment')->label('Payment Terms')->placeholder('CASH'),
                        TextInput::make('bl_no')->label('BL No.'),
                        TextInput::make('cargo_type')->label('Cargo Type'),
                    ])->columns(['default' => 1, 'md' => 2, 'xl' => 4]),

                Section::make('Attention To / Bill To')
                    ->description('Customer or company receiving this invoice.')
                    ->schema([
                        TextInput::make('name')->label('Name'),
                        TextInput::make('company_name')->label('Company Name'),
                        TextInput::make('email')->label('Email')->email(),
                        TextInput::make('phone')->label('Phone')->tel(),
                        Textarea::make('address')->label('Address')->rows(3)->columnSpanFull(),
                    ])->columns(['default' => 1, 'md' => 2, 'xl' => 4]),

                Section::make('Invoice Line Items')
                    ->description('Use the same Group No. for several descriptions that should appear under one number.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                $set('../total_amount', static::calculateTotal($state ?? []));
                            })
                            ->schema([
                                TextInput::make('group_no')
                                    ->label('No.')
                                    ->placeholder('1')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->columnSpan(2),

                                Select::make('description')
                                    ->label('Description')
                                    ->options(fn (): array => static::invoiceSettingOptions('item_descriptions'))
                                    ->searchable()
                                    ->native(false)
                                    ->getOptionLabelUsing(fn ($value): string => (string) $value)
                                    ->createOptionForm([
                                        TextInput::make('value')->label('New description')->required()->maxLength(500),
                                    ])
                                    ->createOptionUsing(fn (array $data): string => static::addInvoiceSettingOption('item_descriptions', $data['value']))
                                    ->required()
                                    ->columnSpan(5),

                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->columnSpan(2),

                                Select::make('unit')
                                    ->label('Unit')
                                    ->options(fn (): array => static::invoiceSettingOptions('item_units'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->getOptionLabelUsing(fn ($value): string => (string) $value)
                                    ->createOptionForm([
                                        TextInput::make('value')
                                            ->label('Unit name')
                                            ->placeholder('e.g. TRUCK, FILE, BL')
                                            ->required()
                                            ->maxLength(50),
                                    ])
                                    ->createOptionUsing(fn (array $data): string => static::addInvoiceSettingOption('item_units', $data['value']))
                                    ->createOptionAction(fn ($action) => $action->label('Add new unit')->modalHeading('Add invoice unit'))
                                    ->placeholder('Select unit')
                                    ->nullable()
                                    ->columnSpan(3),

                                TextInput::make('remark')
                                    ->label('Remark')
                                    ->columnSpan(2),
                            ])
                            ->columns(16)
                            ->itemLabel(fn (array $state): ?string => filled($state['description'] ?? null)
                                ? 'No. '.($state['group_no'] ?? '—').' — '.$state['description']
                                : 'New line item')
                            ->addActionLabel('Add Line Item')
                            ->reorderable()
                            ->cloneable()
                            ->columnSpanFull(),

                        TextInput::make('total_amount')
                            ->label('Invoice Total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])->columns(['default' => 1, 'md' => 2]),
            ]);
    }

    public static function calculateTotal(array $items): float
    {
        return round(collect($items)->sum(
            fn (array $item): float => (float) ($item['unit_price'] ?? 0) * (int) ($item['quantity'] ?? 1)
        ), 2);
    }

    public static function invoiceSettingOptions(string $field): array
    {
        $items = InvoiceSetting::query()->first()?->{$field} ?? [];

        return collect($items)
            ->pluck('value')
            ->filter(fn ($value): bool => is_string($value) && filled(trim($value)))
            ->mapWithKeys(fn (string $value): array => [$value => $value])
            ->all();
    }

    public static function addInvoiceSettingOption(string $field, string $value): string
    {
        abort_unless(in_array($field, ['item_descriptions', 'item_units'], true), 422);

        $value = trim($value);
        if ($field === 'item_units') {
            $value = mb_strtoupper($value);
        }
        $settings = InvoiceSetting::query()->firstOrCreate([], ['company_name' => 'West East Freight']);
        $items = collect($settings->{$field} ?? [])->pluck('value')->push($value)->filter()->unique()->values();
        $settings->update([$field => $items->map(fn (string $item): array => ['value' => $item])->all()]);

        return $value;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice No.')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Bill To')->searchable(),
                Tables\Columns\TextColumn::make('quote_request_id')->label('Quote #')->placeholder('Manual'),
                Tables\Columns\TextColumn::make('total_amount')->label('Total')
                    ->formatStateUsing(fn ($state, Invoice $record): string => ($record->currency ? $record->currency.' ' : '').number_format((float) $state, 2))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')->label('Print / PDF')->icon('heroicon-o-printer')
                    ->url(fn (Invoice $record): string => route('invoices.print', $record))->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
