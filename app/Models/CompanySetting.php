<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    // Aggiungi questa lista per autorizzare il salvataggio dei dati
    protected $fillable = [
        'user_id',
    'company_name',
    'subtitle',      // Nuovo
    'vat_number',
    'fiscal_code',   // Nuovo
    'address',
    'phone',
    'email',
    'pec',
    'logo_path',
    'stamp_path',
    ];

    // Relazione: appartiene a un utente
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}