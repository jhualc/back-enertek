<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_persona', function (Blueprint $table) {
            $table->id('evp_id');
            $table->unsignedBigInteger('eve_id'); // Foreign key to agenda
            $table->unsignedBigInteger('per_id'); // Foreign key to persona
            $table->string('eve_descripcion')->nullable(); // Duplicated from Agenda, consider removing
            $table->time('eve_hora')->nullable(); // Duplicated from Agenda, consider removing
            $table->string('eve_tipo')->nullable(); // Duplicated from Agenda, consider removing
            $table->date('eve_dia')->nullable(); // Duplicated from Agenda, consider removing
            $table->integer('eve_orden')->nullable(); // Duplicated from Agenda, consider removing
            $table->text('eve_resumen')->nullable();
            // Fields from Persona are likely redundant here, consider removing
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('eve_id')->references('eve_id')->on('agenda');
            $table->foreign('per_id')->references('per_id')->on('persona');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('evento_persona');
    }
};
