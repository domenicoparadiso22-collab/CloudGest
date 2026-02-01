<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',     // Ora ingresso
        'clock_out',    // Ora uscita
        'location_in',  // GPS ingresso
        'location_out', // GPS uscita
    ];

    // Questo dice a Laravel di trattare questi campi come Date/Orari veri
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    // Relazione inversa: Una timbratura appartiene a un dipendente
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}