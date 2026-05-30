<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bateria', function (Blueprint $table) {
            $table->id('bat_id');
            $table->string('bat_modelo');
            $table->string('bat_voltaje')->nullable();
            $table->string('bat_capacidad')->nullable();
            $table->foreignId('mar_id')->constrained('marca', 'mar_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bateria');
    }
};
