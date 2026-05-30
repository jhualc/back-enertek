<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_equipo', function (Blueprint $table) {
            $table->id('ceq_id');
            $table->unsignedBigInteger('cli_id'); // Foreign key to cliente
            $table->unsignedBigInteger('equ_id'); // Foreign key to equipo
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cli_id')->references('cli_id')->on('cliente');
            $table->foreign('equ_id')->references('equ_id')->on('equipo');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cliente_equipo');
    }
};
