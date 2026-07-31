<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_sedes', function (Blueprint $table) {
            $table->id('cls_id');
            $table->string('cls_descripcion');
            $table->string('cls_ubicacion')->nullable();
            $table->string('cls_direccion')->unique();
            $table->string('cls_ciudad')->nullable();
            $table->string('cls_departamento')->nullable();
            $table->string('cls_telefono')->nullable();
            $table->string('cls_correo')->nullable();
            $table->unsignedBigInteger('cli_id'); // Foreign key to cliente
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cli_id')->references('cli_id')->on('cliente');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cliente_sedes');
    }
};
