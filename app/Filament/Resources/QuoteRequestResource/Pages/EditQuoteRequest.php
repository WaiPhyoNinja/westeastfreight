<?php

namespace App\Filament\Resources\QuoteRequestResource\Pages;

use App\Filament\Resources\QuoteRequestResource;
use App\Support\MailDelivery;
use App\Support\QuoteMailDispatcher;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditQuoteRequest extends EditRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ['status' => $data['status']];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sendQuotation')
                ->label('Send quotation email')
                ->icon('heroicon-o-envelope')
                ->form([
                    TextInput::make('currency')->label('Currency code')->required()->default('USD')
                        ->alpha()->length(3)->maxLength(3)->helperText('Three-letter currency code, for example USD or MMK.'),
                    TextInput::make('freight_charge')->label('Freight charge')->required()->numeric()->minValue(0.01)->step(0.01),
                    TextInput::make('additional_charges')->label('Other charges')->numeric()->minValue(0)->step(0.01)
                        ->default(0)->helperText('Enter 0 if no additional charges apply. The email total is calculated automatically.'),
                    TextInput::make('estimated_transit')->label('Estimated transit time')->maxLength(100)
                        ->placeholder('e.g. 3–5 business days'),
                    DatePicker::make('valid_until')->label('Quote valid until')->minDate(today()),
                    Textarea::make('terms')->label('Included services, exclusions and conditions')->rows(4)->maxLength(5000),
                    Textarea::make('message')->label('Message to customer')->required()->rows(5)->maxLength(10000),
                ])
                ->fillForm(fn (): array => [
                    'message' => $this->record->quotation_message ?? '',
                    'currency' => $this->record->quotation_details['currency'] ?? 'USD',
                    'freight_charge' => $this->record->quotation_details['freight_charge'] ?? null,
                    'additional_charges' => $this->record->quotation_details['additional_charges'] ?? 0,
                    'estimated_transit' => $this->record->quotation_details['estimated_transit'] ?? null,
                    'valid_until' => $this->record->quotation_details['valid_until'] ?? null,
                    'terms' => $this->record->quotation_details['terms'] ?? null,
                ])
                ->action(function (array $data): void {
                    if (! MailDelivery::isConfigured()) {
                        Notification::make()->title('Configure an SMTP mailer before sending quotations.')->danger()->send();

                        return;
                    }

                    $freight = round((float) $data['freight_charge'], 2);
                    $additional = round((float) ($data['additional_charges'] ?? 0), 2);
                    $quotationDetails = [
                        'currency' => strtoupper($data['currency']),
                        'freight_charge' => number_format($freight, 2, '.', ''),
                        'additional_charges' => number_format($additional, 2, '.', ''),
                        'total' => number_format($freight + $additional, 2, '.', ''),
                        'estimated_transit' => trim((string) ($data['estimated_transit'] ?? '')),
                        'valid_until' => $data['valid_until'] ?? null,
                        'terms' => trim((string) ($data['terms'] ?? '')),
                    ];

                    try {
                        $count = app(QuoteMailDispatcher::class)->queueQuotation($this->record, trim($data['message']), $quotationDetails);
                    } catch (Throwable $exception) {
                        report($exception);
                        Notification::make()->title('Quotation email could not be queued. Check the queue connection.')->danger()->send();

                        return;
                    }

                    Notification::make()->title('Quotation email queued for '.$count.' recipient(s).')->success()->send();
                }),
            Actions\Action::make('printInvoice')
                ->label('Print invoice')
                ->icon('heroicon-o-printer')
                ->visible(fn (): bool => $this->record->invoice()->exists())
                ->url(fn (): string => route('invoices.print', $this->record->invoice()->firstOrFail()))
                ->openUrlInNewTab(),
        ];
    }
}
