<?php

namespace App\Filament\Resources\WebsiteSubmissionResource\Pages;

use App\Filament\Resources\WebsiteSubmissionResource;
use App\Jobs\SendContactReply;
use App\Support\ContactReceiptSender;
use App\Support\MailDelivery;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditWebsiteSubmission extends EditRecord
{
    protected static string $resource = WebsiteSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('callCustomer')
                ->label('Call customer')
                ->icon('heroicon-o-phone')
                ->visible(fn (): bool => $this->record->type === 'contact_us' && filled($this->record->details['phone'] ?? null))
                ->url(fn (): string => 'tel:'.preg_replace('/[^0-9+]/', '', $this->record->details['phone'])),
            Actions\Action::make('resendReceipt')
                ->label('Resend confirmation')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (): bool => $this->record->type === 'contact_us')
                ->requiresConfirmation()
                ->action(function (): void {
                    $status = app(ContactReceiptSender::class)->send($this->record);

                    Notification::make()
                        ->title(match ($status) {
                            'queued' => 'Confirmation email queued for '.$this->record->email,
                            'failed' => 'Email could not be queued. Check the queue connection.',
                            default => 'Email delivery is not configured. Set up SMTP first.',
                        })
                        ->status($status === 'queued' ? 'success' : 'danger')
                        ->send();
                }),
            Actions\Action::make('replyByEmail')
                ->label('Reply by email')
                ->icon('heroicon-o-envelope')
                ->visible(fn (): bool => $this->record->type === 'contact_us')
                ->form([
                    Textarea::make('message')->label('Reply message')->required()->rows(8)->maxLength(10000),
                ])
                ->fillForm(fn (): array => ['message' => $this->record->reply_message ?? ''])
                ->action(function (array $data): void {
                    if (! MailDelivery::isConfigured()) {
                        Notification::make()->title('Configure an SMTP mailer before replying by email.')->danger()->send();

                        return;
                    }

                    try {
                        $this->record->update(['reply_mail_status' => 'queued']);
                        SendContactReply::dispatch($this->record->id, trim($data['message']));
                    } catch (Throwable $exception) {
                        $this->record->update(['reply_mail_status' => 'failed']);
                        report($exception);
                        Notification::make()->title('Reply email could not be queued. Check the queue connection.')->danger()->send();

                        return;
                    }

                    Notification::make()->title('Reply email queued for '.$this->record->email)->success()->send();
                }),
        ];
    }
}
