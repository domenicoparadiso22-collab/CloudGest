<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    // AGGIUNGI 'target_location' QUI SE MANCA
    protected $fillable = [
        'user_id', 
        'employee_id', 
        'message', 
        'is_urgent', 
        'target_location', // <--- FONDAMENTALE
        'target_email', // Nuovo
        'target_phone'  // Nuovo
    ];

    // ...
}