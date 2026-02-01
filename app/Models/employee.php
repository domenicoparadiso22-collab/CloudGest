<?php

namespace App\Models;

// CAMBIA L'IMPORT QUI:
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable // <--- ESTENDE QUESTO, NON PIÙ "Model"
{
    use Notifiable;

    protected $fillable = [
        'user_id',
        'registration_number',
        'name',
        'email',
        'password',
        'phone',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Relazioni esistenti...
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function absences() { return $this->hasMany(Absence::class); }
    
    // Relazione col datore di lavoro (User)
    public function employer() { return $this->belongsTo(User::class, 'user_id'); }
}