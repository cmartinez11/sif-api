<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    /**
     * SIF Audit Log uses only created_at timestamp.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'registro_id',
        'descripcion',
        'historial_json',
        'ip_address',
    ];

    protected $casts = [
        'historial_json' => 'array',
    ];

    /**
     * Relación con el usuario que ejecutó la acción.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
