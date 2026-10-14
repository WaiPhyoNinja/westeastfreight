<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_amount'] = $data['total_amount'] ?? 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->recalculateTotal();

        if (blank($this->record->invoice_number)) {
            $this->record->forceFill([
                'invoice_number' => sprintf('WEF-INV-%s-%06d', now('Asia/Yangon')->format('Y'), $this->record->id),
            ])->saveQuietly();
        }
    }
}
