<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteSubmissionResource\Pages;
use App\Models\WebsiteSubmission;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteSubmissionResource extends Resource
{
    protected static ?string $model = WebsiteSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Contact Us';

    protected static ?string $modelLabel = 'Contact request';

    protected static ?string $pluralModelLabel = 'Contact Us';

    protected static string $submissionType = 'contact_us';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', static::$submissionType);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canViewAny(): bool
    {
        $permission = static::$submissionType === 'newsletter'
            ? 'newsletters.manage'
            : 'contact-requests.manage';

        return auth()->user()?->canAny([$permission, 'submissions.manage']) ?? false;
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
        return $form->schema([
            Select::make('status')->options(['new' => 'New', 'reviewed' => 'Reviewed'])->required(),
            TextInput::make('email')->label('Email')->disabled(),
            Section::make('Customer contact')->columns(2)->schema([
                TextInput::make('details.name')->label('Name')->disabled(),
                TextInput::make('details.phone')->label('Phone')->disabled(),
                TextInput::make('details.subject')->label('Subject')->disabled(),
                TextInput::make('details.transport_id')->label('Transport ID')->disabled(),
            ])->visible(fn (?WebsiteSubmission $record): bool => $record?->type === 'contact_us'),
            Section::make('Customer message')->schema([
                Textarea::make('details.message')->label('Message')->rows(8)->disabled(),
                Textarea::make('details.address')->label('Message from older contact form')->rows(5)->disabled()
                    ->visible(fn (?WebsiteSubmission $record): bool => filled($record?->details['address'] ?? null)),
            ])->visible(fn (?WebsiteSubmission $record): bool => $record?->type === 'contact_us'),
            Section::make('Reply history')->schema([
                Placeholder::make('receipt_mail_status')->label('Automatic receipt email')
                    ->content(fn (?WebsiteSubmission $record): string => match ($record?->receipt_mail_status) {
                        'sent' => 'Accepted by the mail server at '.$record->receipt_mail_sent_at?->format('Y-m-d H:i'),
                        'queued' => 'Queued for delivery',
                        'failed' => 'Delivery attempt failed',
                        'not_configured' => 'Mail transport is not configured',
                        default => 'Not tracked for this older message',
                    }),
                Placeholder::make('email_reply_status')->label('Email reply')
                    ->content(fn (?WebsiteSubmission $record): string => match ($record?->reply_mail_status) {
                        'queued' => 'Queued for delivery',
                        'failed' => 'Delivery attempt failed',
                        default => $record?->replied_at?->format('Y-m-d H:i') ?? 'Not replied yet',
                    }),
                Textarea::make('reply_message')->label('Last reply')->rows(5)->disabled()->dehydrated(false),
            ])->visible(fn (?WebsiteSubmission $record): bool => $record?->type === 'contact_us'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('email')->searchable()->copyable()
                ->url(fn (WebsiteSubmission $record): string => 'mailto:'.$record->email),
            Tables\Columns\TextColumn::make('details.name')->label('Name'),
            Tables\Columns\TextColumn::make('details.phone')->label('Phone')->copyable()
                ->url(fn (WebsiteSubmission $record): ?string => filled($record->details['phone'] ?? null)
                    ? 'tel:'.preg_replace('/[^0-9+]/', '', $record->details['phone']) : null),
            Tables\Columns\TextColumn::make('details.subject')->label('Subject')->limit(30),
            Tables\Columns\TextColumn::make('details.message')->label('Message')
                ->formatStateUsing(function (?string $state): string {
                    $message = trim(preg_replace('/\s+/u', ' ', strip_tags($state ?? '')) ?? '');

                    return mb_strlen($message) > 50 ? mb_substr($message, 0, 50).'…' : $message;
                }),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('receipt_mail_status')->label('Auto email')->badge()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'sent' => 'Sent', 'queued' => 'Queued', 'failed' => 'Failed', 'not_configured' => 'Not configured', default => '—',
                }),
            Tables\Columns\TextColumn::make('replied_at')->label('Replied')->dateTime()->placeholder('Not replied'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListWebsiteSubmissions::route('/'), 'edit' => Pages\EditWebsiteSubmission::route('/{record}/edit')];
    }
}
