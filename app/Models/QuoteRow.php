<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRow extends Model
{
    protected $fillable = [
        'quote_id', 'description', 'quantity', 'unit', 'price', 'total'
    ];
}