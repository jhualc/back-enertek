<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_equipo', function (Blueprint $table) {
            $table->id('coe_id');
            $table->unsignedBigInteger('equ_id'); // Foreign key to equipo
            $table->unsignedBigInteger('con_id'); // Foreign key to contrato
            $table->string('coe_periodicidad')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('equ_id')->references('equ_id')->on('equipo');
            $table->foreign('con_id')->references('con_id')->on('contrato');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('contrato_equipo');
    }
};
