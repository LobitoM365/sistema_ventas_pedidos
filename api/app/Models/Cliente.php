<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Cliente extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'clientes';
    public $timestamps = false;
    

    protected $fillable = [
        'estado',
        'nombre',
        'cedula',
        'telefono',
        'password',
        'nickname'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function sesiones()
    {
        return $this->hasMany(SesionCliente::class, 'clientes_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
