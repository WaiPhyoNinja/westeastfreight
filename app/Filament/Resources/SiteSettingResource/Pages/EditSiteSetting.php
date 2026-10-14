<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\SiteSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (['customer_book_track', 'customer_tools'] as $name) {
            $data[$name.'_menu'] ??= \App\Models\SiteSetting::defaultCustomerMenu($name);
        }

        $data['customer_navigation_groups'] ??= \App\Models\SiteSetting::defaultCustomerNavigationGroups();

        return $data;
    }
}
