<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationCountry extends Model
{
    protected $fillable = ['name', 'short_name', 'postal_code', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
