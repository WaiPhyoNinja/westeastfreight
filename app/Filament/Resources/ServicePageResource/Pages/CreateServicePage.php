<?php

namespace App\Filament\Resources\ServicePageResource\Pages;

use App\Filament\Resources\ServicePageResource;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;

class CreateServicePage extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ServicePageResource::class;

    public function getSteps(): array
    {
        return ServicePageResource::creationSteps();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['page_type'] = 'service';

        return $data;
    }
}
