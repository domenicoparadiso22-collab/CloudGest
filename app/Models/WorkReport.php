<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // <--- Importante

class WorkReport extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'number', 'date', 
        'notes', 'private_notes', 'status', 
        'customer_signature_path', 'unique_code' // <--- Aggiungilo qui
    ];

    // Evento automatico alla creazione
    protected static function booted()
    {
        static::creating(function ($report) {
            // Genera un codice casuale di 8 caratteri maiuscoli (es. AB12XY99)
            $report->unique_code = strtoupper(Str::random(8));
        });
    }

    // ... le altre relazioni restano uguali ...
    public function rows() { return $this->hasMany(WorkReportRow::class); }
    public function client() { return $this->belongsTo(Client::class); }
}