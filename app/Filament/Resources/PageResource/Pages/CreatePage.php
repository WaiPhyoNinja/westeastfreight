<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PageResource::class;

    public function getSteps(): array
    {
        return PageResource::creationSteps();
    }
}
