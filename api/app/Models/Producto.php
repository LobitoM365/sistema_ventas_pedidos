<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    public $timestamps = false;

    protected $fillable = [
        'estado',
        'nombre',
        'descripcion',
        'precio_venta',
        'precio_base',
        'medida',
    ];

    protected $dates = ['fecha_creacion', 'fecha_actualizacion'];
}
