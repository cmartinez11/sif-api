<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilCliente extends Model
{
    protected $table = 'crm_perfil_clientes';

    protected $fillable = [
        'cliente_id',
        'tipo_preforma',
        'gramaje',
        'cuello',
        'aplicacion',
        'cant_maquinas',
        'vol_mensual',
        'vol_proyectado',
        'frecuencia_compra',
        'proveedor_actual',
        'problemas_proveedor',
        'urgencias_frecuentes',
        'observaciones',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
