<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato', function (Blueprint $table) {
            $table->id('con_id');
            $table->string('con_tipo');
            $table->decimal('con_valor', 10, 2)->nullable();
            $table->string('con_periodicidad')->nullable();
            $table->string('con_estado')->nullable();
            $table->unsignedBigInteger('cli_id'); // Foreign key to cliente
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cli_id')->references('cli_id')->on('cliente');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('contrato');
    }
};
