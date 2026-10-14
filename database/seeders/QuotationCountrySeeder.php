<?php

namespace Database\Seeders;

use App\Models\QuotationCountry;
use Illuminate\Database\Seeder;

class QuotationCountrySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Myanmar', 'short_name' => 'MM', 'postal_code' => null],
            ['name' => 'Thailand', 'short_name' => 'TH', 'postal_code' => null],
            ['name' => 'Singapore', 'short_name' => 'SG', 'postal_code' => null],
            ['name' => 'China', 'short_name' => 'CN', 'postal_code' => null],
        ] as $country) {
            QuotationCountry::query()->firstOrCreate(
                ['short_name' => $country['short_name']],
                [...$country, 'status' => true],
            );
        }
    }
}
