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
        if (!Schema::hasTable('bateria')) {
            return;
        }

        Schema::table('bateria', function (Blueprint $table) {
            if (Schema::hasColumn('bateria', 'mar_id')) {
                $table->dropForeign(['mar_id']);
                $table->dropColumn('mar_id');
            }

            if (!Schema::hasColumn('bateria', 'bat_marca')) {
                $table->string('bat_marca')->nullable()->after('bat_capacidad');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('bateria')) {
            return;
        }

        Schema::table('bateria', function (Blueprint $table) {
            if (Schema::hasColumn('bateria', 'bat_marca')) {
                $table->dropColumn('bat_marca');
            }

            if (!Schema::hasColumn('bateria', 'mar_id')) {
                $table->foreignId('mar_id')->constrained('marca', 'mar_id');
            }
        });
    }
};
