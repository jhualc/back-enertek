<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_fotografico', function (Blueprint $table) {
            $table->id('ref_id');
            $table->string('ref_ubicacion')->nullable();
            $table->unsignedBigInteger('ins_id'); // Foreign key to instalacion
            $table->unsignedBigInteger('otr_id'); // Foreign key to orden_trabajo (corrected from ote_id)
            $table->date('ref_fecha');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('ins_id')->references('ins_id')->on('instalacion');
            $table->foreign('otr_id')->references('otr_id')->on('orden_trabajo');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('registro_fotografico');
    }
};
