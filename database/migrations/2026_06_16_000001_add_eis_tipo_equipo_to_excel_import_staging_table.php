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
        if (!Schema::hasTable('excel_import_staging')) {
            return;
        }

        if (!Schema::hasColumn('excel_import_staging', 'eis_tipo_equipo')) {
            Schema::table('excel_import_staging', function (Blueprint $table) {
                $table->string('eis_tipo_equipo')->nullable()->after('eis_tipo_cliente');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('excel_import_staging')) {
            return;
        }

        if (Schema::hasColumn('excel_import_staging', 'eis_tipo_equipo')) {
            Schema::table('excel_import_staging', function (Blueprint $table) {
                $table->dropColumn('eis_tipo_equipo');
            });
        }
    }
};
