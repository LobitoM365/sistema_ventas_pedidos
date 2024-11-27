<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Administrador extends Authenticatable
{
    use Notifiable, HasApiTokens;
    public $timestamps = false;
    
    protected $table = 'administradores';

    protected $fillable = [
        'estado',
        'password',
        'nickname',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'fecha_creacion',
        'fecha_actualizacion',
    ];

 

    public function sesiones()
    {
        return $this->hasMany(SesionAdministrador::class, 'administradores_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
