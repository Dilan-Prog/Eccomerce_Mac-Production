<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspel_products', function (Blueprint $table) {
            $table->id();

            // Identificación y descripción principal
            $table->string('cve_art', 50)->unique()->comment('Clave del artículo (CVE_ART)');
            $table->string('descr', 255)->nullable()->comment('Descripción del producto');
            $table->string('lin_prod', 50)->nullable()->comment('Clave de línea de producto');
            
            // Control de inventario
            $table->char('con_serie', 1)->nullable()->comment('Con número de serie [S/N]');
            $table->string('uni_med', 50)->nullable()->comment('Unidad de medida de entrada');
            $table->decimal('uni_emp', 10, 4)->nullable()->comment('Unidad de empaque');
            $table->string('ctrl_alm', 50)->nullable()->comment('Control de almacén');
            $table->decimal('tiem_surt', 10, 2)->nullable()->comment('Tiempo de surtido en días');
            
            // Stock y límites
            $table->decimal('stock_min', 15, 4)->nullable()->comment('Stock mínimo');
            $table->decimal('stock_max', 15, 4)->nullable()->comment('Stock máximo');
            $table->decimal('exist', 15, 4)->nullable()->comment('Existencias actuales');
            $table->decimal('pend_surt', 15, 4)->nullable()->comment('Pendientes por surtir');
            $table->decimal('apart', 15, 4)->nullable()->comment('Apartados');
            $table->decimal('comp_x_rec', 15, 4)->nullable()->comment('Compras pendientes por recibir');
            
            // Costos y precios
            $table->char('tip_costeo', 1)->nullable()->comment('Tipo de costeo: P=Promedio, I=Identificado, U=UEPS, S=Estándar, E=PEPS');
            $table->string('num_mon', 10)->nullable()->comment('Clave de moneda');
            $table->decimal('costo_prom', 15, 4)->nullable()->comment('Costo promedio');
            $table->decimal('ult_costo', 15, 4)->nullable()->comment('Último costo');
            
            // Fechas importantes
            $table->dateTime('fch_ultcom')->nullable()->comment('Fecha de última compra');
            $table->dateTime('fch_ultvta')->nullable()->comment('Fecha de última venta');
            
            // Clasificación y tipo
            $table->string('cve_obs', 50)->nullable()->comment('Clave de observaciones');
            $table->char('tipo_ele', 1)->nullable()->comment('Tipo de elemento: P=Producto, K=Kit, G=Grupo, S=Servicio');
            
            // Unidades alternativas
            $table->string('uni_alt', 50)->nullable()->comment('Unidad de salida alternativa');
            $table->decimal('fac_conv', 10, 4)->nullable()->comment('Factor de conversión entre unidades');
            
            // Control especial
            $table->char('con_lote', 1)->nullable()->comment('Control por lote [S/N]');
            $table->char('con_pedimento', 1)->nullable()->comment('Requiere pedimento [S/N]');
            
            // Dimensiones físicas
            $table->decimal('peso', 15, 4)->nullable()->comment('Peso del producto');
            $table->decimal('volumen', 15, 4)->nullable()->comment('Volumen del producto');
            
            // Referencias contables y fiscales
            $table->string('cve_esqimpu', 50)->nullable()->comment('Clave de esquema de impuestos');
            $table->string('cve_bita', 50)->nullable()->comment('Clave de bitácora');
            $table->string('cuent_cont', 50)->nullable()->comment('Cuenta contable');
            $table->string('cve_prodserv', 50)->nullable()->comment('Clave producto/servicio SAT');
            $table->string('cve_unidad', 50)->nullable()->comment('Clave de unidad SAT');
            
            // Estadísticas anuales
            $table->decimal('vtas_anl_c', 15, 2)->nullable()->comment('Ventas anuales (cantidad)');
            $table->decimal('vtas_anl_m', 15, 2)->nullable()->comment('Ventas anuales (monto)');
            $table->decimal('comp_anl_c', 15, 2)->nullable()->comment('Compras anuales (cantidad)');
            $table->decimal('comp_anl_m', 15, 2)->nullable()->comment('Compras anuales (monto)');
            
            // Atributos del producto
            $table->string('prefijo', 50)->nullable()->comment('Modelo o prefijo');
            $table->string('talla', 50)->nullable()->comment('Talla');
            $table->string('color', 50)->nullable()->comment('Color');
            $table->string('cve_imagen', 255)->nullable()->comment('Nombre del archivo de imagen');
            
            // Estado y controles
            $table->char('blk_cst_ext', 1)->default('N')->comment('Bloqueado por costos-existencias [S/N]');
            $table->char('status', 1)->default('A')->comment('Estatus: A=Activo, B=Baja');
            
            // IEPS (Impuesto Especial sobre Producción y Servicios)
            $table->char('man_ieps', 1)->default('N')->comment('Maneja IEPS [S/N]');
            $table->integer('apl_man_imp')->nullable()->comment('Número de impuesto para IEPS (1-8)');
            $table->decimal('cuota_ieps', 15, 4)->nullable()->comment('Cuota del IEPS');
            $table->char('apl_man_ieps', 1)->nullable()->comment('Aplicación IEPS: C=Cuota, M=Más alto, A=Ambos');
            
            // Sincronización SAE Móvil
            $table->string('uuid', 50)->nullable()->comment('UUID para sincronización con SAE Móvil');
            $table->dateTime('version_sinc')->nullable()->comment('Fecha/hora última sincronización');
            $table->dateTime('version_sinc_fecha_img')->nullable()->comment('Fecha/hora sincronización de imagen');
            
            // Mercado Libre - Estado y clasificación
            $table->string('edo_publ_ml', 50)->nullable()->comment('Estado de publicación en Mercado Libre');
            $table->string('cve_publ_ml', 50)->nullable()->comment('Clave de publicación en ML');
            $table->string('condicion_ml', 50)->nullable()->comment('Condición: Nuevo/Usado');
            $table->string('tipo_publ_ml', 50)->nullable()->comment('Tipo: Gratis/Clásica/Premium');
            $table->string('categ_ml', 255)->nullable()->comment('Categoría en Mercado Libre');
            $table->string('cve_cate_ml', 50)->nullable()->comment('Clave de categoría ML');
            $table->string('titulo_ml', 255)->nullable()->comment('Título personalizado para ML');
            
            // Mercado Libre - Envío
            $table->string('modo_envio_ml', 50)->nullable()->comment('Modo de envío: Mercado Envío/No especificado');
            $table->char('envio_ml', 1)->nullable()->comment('Envío gratis [S/N]');
            $table->decimal('largo_ml', 10, 2)->nullable()->comment('Largo del paquete (cm)');
            $table->decimal('alto_ml', 10, 2)->nullable()->comment('Alto del paquete (cm)');
            $table->decimal('ancho_ml', 10, 2)->nullable()->comment('Ancho del paquete (cm)');
            
            // Mercado Libre - Configuración
            $table->longText('campos_categ_ml')->nullable()->comment('Campos adicionales de categoría ML (JSON)');
            $table->char('disponible_publ', 1)->nullable()->comment('Disponible para publicar [S/N]');
            $table->dateTime('last_update_ml')->nullable()->comment('Fecha última actualización en ML');
            $table->dateTime('f_crea_ml')->nullable()->comment('Fecha de creación de publicación ML');
            $table->string('imagen_ml', 255)->nullable()->comment('URL de imagen en Mercado Libre');
            
            // Catálogo
            $table->string('en_catalogo', 50)->nullable()->comment('Indicador de catálogo');
            $table->string('id_catalogo', 50)->nullable()->comment('ID del catálogo');
            
            // Transporte
            $table->char('mat_peli', 1)->nullable()->comment('Material peligroso [S/N] (Carta Porte)');
            
            // Compatibilidad con versión anterior
            $table->string('nombre')->nullable()->comment('Alias de descr (compatibilidad)');
            $table->decimal('price', 10, 2)->nullable()->comment('Alias de ult_costo (compatibilidad)');
            $table->integer('stock')->nullable()->comment('Alias de exist (compatibilidad)');
            $table->timestamp('remote_updated_at')->nullable()->comment('Fecha última actualización remota');
            $table->string('sync_hash')->nullable()->comment('Hash para control de cambios');

            $table->timestamps();

            // Índices para optimizar consultas
            $table->index('cve_art', 'idx_cve_art');
            $table->index('status', 'idx_status');
            $table->index('tipo_ele', 'idx_tipo_elemento');
            $table->index('lin_prod', 'idx_linea_producto');
            $table->index('con_serie', 'idx_con_serie');
            $table->index('con_lote', 'idx_con_lote');
        });

        // Agregar comentario a la tabla completa
        DB::statement("ALTER TABLE aspel_products COMMENT = 'Productos sincronizados desde Aspel SAE (tabla INVE01)'");
    }

    public function down(): void
    {
        Schema::dropIfExists('aspel_products');
    }
};
?>