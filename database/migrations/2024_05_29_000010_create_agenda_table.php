<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda', function (Blueprint $table) {
            $table->id('eve_id'); // Assuming eve_id is the primary key for Agenda
            $table->string('eve_descripcion')->nullable();
            $table->time('eve_hora')->nullable();
            $table->string('eve_tipo')->nullable();
            $table->date('eve_dia')->nullable();
            $table->integer('eve_orden')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('agenda');
    }
};
