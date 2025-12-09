<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AspelSync extends Model
{
    use HasFactory;

    protected $table = 'aspel_products';
    
    protected $fillable = [
        // Identificación y descripción
        'cve_art', 'descr', 'lin_prod',
        
        // Control de inventario
        'con_serie', 'uni_med', 'uni_emp', 'ctrl_alm', 'tiem_surt',
        
        // Stock y límites
        'stock_min', 'stock_max', 'exist', 'pend_surt', 'apart', 'comp_x_rec',
        
        // Costos y precios
        'tip_costeo', 'num_mon', 'costo_prom', 'ult_costo',
        
        // Fechas
        'fch_ultcom', 'fch_ultvta',
        
        // Clasificación
        'cve_obs', 'tipo_ele',
        
        // Unidades alternativas
        'uni_alt', 'fac_conv',
        
        // Control especial
        'con_lote', 'con_pedimento',
        
        // Dimensiones
        'peso', 'volumen',
        
        // Referencias contables y fiscales
        'cve_esqimpu', 'cve_bita', 'cuent_cont', 'cve_prodserv', 'cve_unidad',
        
        // Estadísticas anuales
        'vtas_anl_c', 'vtas_anl_m', 'comp_anl_c', 'comp_anl_m',
        
        // Atributos del producto
        'prefijo', 'talla', 'color', 'cve_imagen',
        
        // Estado y controles
        'blk_cst_ext', 'status', 'man_ieps', 'apl_man_imp', 'cuota_ieps', 'apl_man_ieps',
        
        // Sincronización SAE Móvil
        'uuid', 'version_sinc', 'version_sinc_fecha_img',
        
        // Mercado Libre
        'edo_publ_ml', 'cve_publ_ml', 'condicion_ml', 'tipo_publ_ml', 'categ_ml',
        'cve_cate_ml', 'titulo_ml', 'modo_envio_ml', 'envio_ml', 'largo_ml',
        'alto_ml', 'ancho_ml', 'campos_categ_ml', 'disponible_publ', 'last_update_ml',
        'f_crea_ml', 'imagen_ml', 'en_catalogo', 'id_catalogo',
        
        // Transporte
        'mat_peli',
        
        // Compatibilidad
        'nombre', 'price', 'stock', 'remote_updated_at', 'sync_hash'
    ];

    protected $casts = [
        'fch_ultcom' => 'datetime',
        'fch_ultvta' => 'datetime',
        'version_sinc' => 'datetime',
        'version_sinc_fecha_img' => 'datetime',
        'last_update_ml' => 'datetime',
        'f_crea_ml' => 'datetime',
        'remote_updated_at' => 'datetime',
        'campos_categ_ml' => 'json',
        'stock_min' => 'decimal:4',
        'stock_max' => 'decimal:4',
        'exist' => 'decimal:4',
        'pend_surt' => 'decimal:4',
        'apart' => 'decimal:4',
        'comp_x_rec' => 'decimal:4',
        'uni_emp' => 'decimal:4',
        'fac_conv' => 'decimal:4',
        'peso' => 'decimal:4',
        'volumen' => 'decimal:4',
        'costo_prom' => 'decimal:4',
        'ult_costo' => 'decimal:4',
        'cuota_ieps' => 'decimal:4',
        'vtas_anl_c' => 'decimal:2',
        'vtas_anl_m' => 'decimal:2',
        'comp_anl_c' => 'decimal:2',
        'comp_anl_m' => 'decimal:2',
        'largo_ml' => 'decimal:2',
        'alto_ml' => 'decimal:2',
        'ancho_ml' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    // Relaciones si las necesitas
    // public function scopeActive($query)
    // {
    //     return $query->where('status', 'A');
    // }

    // public function scopeWithStock($query)
    // {
    //     return $query->where('exist', '>', 0);
    // }

    // public function scopeByType($query, $type)
    // {
    //     return $query->where('tipo_ele', $type);
    // }
}
?>