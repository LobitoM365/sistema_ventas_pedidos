<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesionAdministrador extends Model
{
    use HasFactory;

    protected $table = 'sesiones_administradores';
    public $timestamps = false;

    protected $fillable = [
        'administradores_id',
        'token',
    ];

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'administradores_id');
    }
}
