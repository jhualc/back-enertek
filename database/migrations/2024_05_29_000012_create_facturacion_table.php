<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturacion', function (Blueprint $table) {
            $table->id('fac_id');
            $table->date('fac_fecha');
            $table->decimal('fac_total', 10, 2);
            $table->string('fac_estado');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('facturacion');
    }
};
