<?php

namespace App\Filament\Resources\InvoiceSettingResource\Pages;

use App\Filament\Resources\InvoiceSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceSettings extends ListRecords
{
    protected static string $resource = InvoiceSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Set up invoice')];
    }
}
