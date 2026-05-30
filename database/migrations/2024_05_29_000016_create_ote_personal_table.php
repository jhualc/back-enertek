<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ote_personal', function (Blueprint $table) {
            $table->id('otp_id');
            $table->unsignedBigInteger('per_id'); // Foreign key to persona
            $table->unsignedBigInteger('otr_id'); // Foreign key to orden_trabajo (corrected from ote_id)
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('per_id')->references('per_id')->on('persona');
            $table->foreign('otr_id')->references('otr_id')->on('orden_trabajo');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ote_personal');
    }
};
