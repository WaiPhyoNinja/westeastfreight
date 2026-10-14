<?php

namespace App\Filament\Resources\QuotationCountryResource\Pages;

use App\Filament\Resources\QuotationCountryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuotationCountry extends EditRecord
{
    protected static string $resource = QuotationCountryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
