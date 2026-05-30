<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('equipo', function (Blueprint $table) {
            $table->id('equ_id');
            $table->string('equ_modelo');
            $table->string('equ_serial')->unique();
            $table->unsignedBigInteger('mar_id');
            $table->unsignedBigInteger('teq_id');
            $table->integer('equ_cant_baterias');
            $table->string('equ_qr_code')->nullable();
            $table->string('equ_potencia')->nullable();
            $table->string('equ_ubicacion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('mar_id')->references('mar_id')->on('marca');
        });
    }
    public function down() {
        Schema::dropIfExists('equipo');
    }
};
