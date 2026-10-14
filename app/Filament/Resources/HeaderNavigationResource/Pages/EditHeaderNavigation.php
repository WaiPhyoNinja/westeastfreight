<?php

namespace App\Filament\Resources\HeaderNavigationResource\Pages;

use App\Filament\Resources\HeaderNavigationResource;
use Filament\Resources\Pages\EditRecord;

class EditHeaderNavigation extends EditRecord
{
    protected static string $resource = HeaderNavigationResource::class;
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (['customer_book_track', 'customer_tools'] as $name) {
            $data[$name.'_menu'] ??= \App\Models\SiteSetting::defaultCustomerMenu($name);
        }

        return $data;
    }
}
