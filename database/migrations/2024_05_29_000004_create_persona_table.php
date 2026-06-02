<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id('per_id');
            $table->string('per_nombre');
            $table->string('per_correo')->nullable();
            $table->string('per_cargo')->nullable();
            $table->string('per_empresa')->nullable();
            $table->string('per_tipo_persona')->nullable();
            $table->text('per_bio')->nullable();
            $table->string('per_foto')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
