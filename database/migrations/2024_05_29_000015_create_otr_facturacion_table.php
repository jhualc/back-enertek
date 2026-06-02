<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otr_facturacion', function (Blueprint $table) {
            $table->id('otf_id');
            $table->unsignedBigInteger('fac_id'); // Foreign key to facturacion
            $table->unsignedBigInteger('otr_id'); // Foreign key to orden_trabajo
            $table->decimal('otf_valor', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('fac_id')->references('fac_id')->on('facturacion');
            $table->foreign('otr_id')->references('otr_id')->on('orden_trabajo');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('otr_facturacion');
    }
};
