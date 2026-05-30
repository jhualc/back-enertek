<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bateria_equipo', function (Blueprint $table) {
            $table->id('beq_id');
            $table->unsignedBigInteger('equ_id'); // Foreign key to equipo
            $table->unsignedBigInteger('bat_id'); // Foreign key to bateria
            $table->date('beq_fecha')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('equ_id')->references('equ_id')->on('equipo');
            $table->foreign('bat_id')->references('bat_id')->on('bateria');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bateria_equipo');
    }
};
