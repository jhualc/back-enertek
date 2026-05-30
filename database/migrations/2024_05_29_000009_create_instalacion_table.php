<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instalacion', function (Blueprint $table) {
            $table->id('ins_id');
            $table->string('ins_calibre_cable')->nullable();
            $table->integer('ins_cant_cable')->nullable();
            $table->string('ins_breaker')->nullable();
            $table->string('ins_otro')->nullable();
            $table->string('ins_tablero')->nullable();
            $table->unsignedBigInteger('equ_id'); // Foreign key to equipo
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('equ_id')->references('equ_id')->on('equipo');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('instalacion');
    }
};
