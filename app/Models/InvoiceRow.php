<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceRow extends Model
{
    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit', 'price', 'vat_rate', 'total'
    ];
}