<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_trabajo', function (Blueprint $table) {
            $table->id('otr_id');
            $table->date('otr_fecha_creacion');
            $table->text('otr_descripcion')->nullable();
            $table->decimal('otr_valor_cotizado', 10, 2)->nullable();
            $table->string('otr_estado');
            $table->unsignedBigInteger('cli_id'); // Foreign key to cliente
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cli_id')->references('cli_id')->on('cliente');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('orden_trabajo');
    }
};
