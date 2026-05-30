<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excel_import_staging', function (Blueprint $table) {
            $table->id();
            
            // Columna 0-2: Datos generales empresa
            $table->string('eis_sector_empresa')->nullable();
            $table->string('eis_tipo_cliente')->nullable();
            $table->string('eis_sigla')->nullable();
            
            // Columna 3-11: Cliente y Sede
            $table->string('eis_nombre_empresa_persona')->nullable();
            $table->string('eis_tipo_identificacion')->nullable();
            $table->string('eis_identificacion')->nullable();
            $table->string('eis_dv')->nullable();
            $table->string('eis_departamento')->nullable();
            $table->string('eis_ciudad')->nullable();
            $table->string('eis_direccion')->nullable();
            $table->string('eis_sede')->nullable();
            $table->string('eis_ubicacion_equipo')->nullable();
            
            // Columna 12-17: Contactos
            $table->string('eis_nombre_contacto_1')->nullable();
            $table->string('eis_correo_contacto_1')->nullable();
            $table->string('eis_telefono_contacto_1')->nullable();
            $table->string('eis_nombre_contacto_2')->nullable();
            $table->string('eis_correo_contacto_2')->nullable();
            $table->string('eis_telefono_contacto_2')->nullable();
            
            // Columna 18-19: Estado y relación
            $table->string('eis_estado_cliente')->nullable();
            $table->string('eis_tipo_relacion_comercial')->nullable();
            
            // Columna 20-25: Equipo
            $table->string('eis_marca_equipo')->nullable();
            $table->string('eis_modelo_equipo')->nullable();
            $table->string('eis_potencia_kva')->nullable();
            $table->string('eis_serial_equipo')->nullable();
            $table->integer('eis_cantidad_baterias_int')->nullable();
            $table->integer('eis_cantidad_baterias_ext')->nullable();
            
            // Columna 26-30: Batería
            $table->string('eis_marca_bateria')->nullable();
            $table->string('eis_referencia_bateria')->nullable();
            $table->string('eis_voltaje_bateria')->nullable();
            $table->string('eis_amperaje_bateria')->nullable();
            $table->string('eis_snmps')->nullable();
            
            // Control de importación
            $table->enum('import_status', ['pendiente', 'procesado', 'error'])->default('pendiente');
            $table->text('import_error')->nullable();
            $table->foreignId('import_batch_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_import_staging');
    }
};
