<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AspelClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'status',
        'nombre',
        'nombre_comercial',
        'rfc',
        'tipo_empresa',
        'uso_cfdi',
        'regimen_fiscal',
        'email',
        'telefono',
        'calle',
        'num_ext',
        'colonia',
        'municipio',
        'estado',
        'codigo_postal',
        'lista_precio',
        'descuento',
        'cve_vendedor',
        'user_id',
        'remote_updated_at',
        'sync_hash',
    ];

    protected $casts = [
        'remote_updated_at' => 'datetime',
        'lista_precio' => 'integer',
        'descuento' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
