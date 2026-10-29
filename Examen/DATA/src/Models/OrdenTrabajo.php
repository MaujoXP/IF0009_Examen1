<?php
declare(strict_types=1);

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    protected $table = 'ordenes_trabajo';
    public $timestamps = false;

    protected $fillable = [
        'placa_vehiculo',
        'propietario',
        'descripcion_falla',
        'costo_estimado',
        'estado',
    ];

    protected $casts = [
        'id' => 'integer',
        'costo_estimado' => 'decimal:2',
    ];
}
