<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'terms_and_conditions' => 'array',
            'item_descriptions' => 'array',
            'item_units' => 'array',
        ];
    }
}
