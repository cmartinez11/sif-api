<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'ruc',
        'direccion',
        'provincia',
        'distrito',
        'departamento',
        'condicion_pago',
        'contacto_id',
    ];

    public function contacto()
    {
        return $this->belongsTo(Contacto::class);
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function perfil()
    {
        return $this->hasOne(PerfilCliente::class, 'cliente_id');
    }

    public function competencia()
    {
        return $this->hasMany(Competencia::class, 'cliente_id')->orderBy('fecha_dato', 'desc');
    }

}
