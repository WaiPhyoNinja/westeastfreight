<?php

namespace App\Filament\Resources\CustomerNavigationResource\Pages;

use App\Filament\Resources\CustomerNavigationResource;
use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;

class EditCustomerNavigation extends EditSiteSetting
{
    protected static string $resource = CustomerNavigationResource::class;

    public function getTitle(): string
    {
        return 'Customer header navigation';
    }
}
