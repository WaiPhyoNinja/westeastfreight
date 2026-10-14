<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'group_no', 'description', 'unit_price', 'quantity', 'unit', 'remark'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
