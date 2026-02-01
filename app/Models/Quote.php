<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'number', 'date', 'valid_until',
        'notes', 'private_notes', 'status',
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(QuoteRow::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}