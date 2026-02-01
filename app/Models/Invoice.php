<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'number', 'date', 'due_date',
        'payment_method', 'notes', 'private_notes', 'status',
        'total_net', 'total_vat', 'total_gross'
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(InvoiceRow::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}