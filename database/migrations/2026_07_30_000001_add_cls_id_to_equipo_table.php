<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('equipo')) {
            return;
        }

        Schema::table('equipo', function (Blueprint $table) {
            if (!Schema::hasColumn('equipo', 'cls_id')) {
                $table->unsignedBigInteger('cls_id')->nullable()->after('equ_ubicacion');
                $table->foreign('cls_id')->references('cls_id')->on('cliente_sedes')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('equipo')) {
            return;
        }

        Schema::table('equipo', function (Blueprint $table) {
            if (Schema::hasColumn('equipo', 'cls_id')) {
                $table->dropForeign(['cls_id']);
                $table->dropColumn('cls_id');
            }
        });
    }
};
