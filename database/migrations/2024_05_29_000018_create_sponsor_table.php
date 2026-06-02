<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor', function (Blueprint $table) {
            $table->id('spo_id');
            $table->string('spo_logo')->nullable();
            $table->string('spo_empresa');
            $table->string('spo_tipo')->nullable();
            $table->string('spo_web')->nullable();
            $table->string('spo_contacto')->nullable();
            $table->string('spo_telefono')->nullable();
            $table->string('spo_correo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('sponsor');
    }
};
