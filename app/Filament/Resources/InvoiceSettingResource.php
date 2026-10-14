<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceSettingResource\Pages;
use App\Models\InvoiceSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceSettingResource extends Resource
{
    protected static ?string $model = InvoiceSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Invoices';

    protected static ?string $navigationLabel = 'Invoice Settings';

    protected static ?string $modelLabel = 'Invoice Settings';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canAny(['settings.manage', 'invoices.update']) ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny() && ! InvoiceSetting::query()->exists();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Invoice branding')->description('These details appear only on printed invoices and saved PDFs.')->schema([
                TextInput::make('company_name')->required()->maxLength(255),
                TextInput::make('invoice_title')->required()->default('INVOICE')->maxLength(100),
                FileUpload::make('header_logo')->label('Header logo')->image()->disk('public')->directory('invoice/branding')
                    ->visibility('public')->maxSize(4096)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                FileUpload::make('footer_logo')->label('Footer logo')->image()->disk('public')->directory('invoice/branding')
                    ->visibility('public')->maxSize(4096)->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
            ])->columns(2),
            Section::make('Company contact details')->schema([
                RichEditor::make('address')->label('Address')
                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                    ->columnSpanFull(),
                TextInput::make('phone')->tel()->maxLength(100),
                TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(100),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('secondary_email')->label('Secondary email')->email()->maxLength(255),
                TextInput::make('website')->url()->maxLength(2048)->columnSpanFull(),
                RichEditor::make('company_header_html')->label('Formatted company header (optional)')
                    ->helperText('When filled, this formatted content replaces the contact lines in the invoice header.')
                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                    ->columnSpanFull(),
            ])->columns(2),
            Section::make('Terms and footer')->schema([
                RichEditor::make('terms_and_conditions_html')->label('Terms & Conditions')
                    ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                    ->columnSpanFull(),
                Textarea::make('footer_note')->label('Footer note')->rows(2)->maxLength(1000)->columnSpanFull(),
            ]),
            Section::make('Invoice line item options')
                ->description('These values appear as searchable choices while creating or editing an invoice.')
                ->schema([
                    Repeater::make('item_descriptions')->label('Description options')->schema([
                        TextInput::make('value')->label('Description')->required()->maxLength(500),
                    ])->addActionLabel('Add description')->reorderable()->collapsible()->defaultItems(0),
                    Repeater::make('item_units')->label('Unit options')->schema([
                        TextInput::make('value')->label('Unit')->placeholder('FILE / TRUCK / BL')
                            ->helperText('Short uppercase labels are easiest to read on the invoice.')
                            ->required()->maxLength(50),
                    ])->addActionLabel('Add unit')->reorderable()->collapsible()->defaultItems(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('header_logo')->label('Logo')->disk('public')->square(),
            Tables\Columns\TextColumn::make('company_name')->searchable(),
            Tables\Columns\TextColumn::make('email'),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoiceSettings::route('/'),
            'create' => Pages\CreateInvoiceSetting::route('/create'),
            'edit' => Pages\EditInvoiceSetting::route('/{record}/edit'),
        ];
    }
}
