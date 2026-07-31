<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        $duplicates = DB::table('bateria')
            ->select('bat_marca', 'bat_modelo', 'bat_voltaje', 'bat_capacidad')
            ->selectRaw('MIN(bat_id) as keep_id, GROUP_CONCAT(bat_id) as ids')
            ->groupBy('bat_marca', 'bat_modelo', 'bat_voltaje', 'bat_capacidad')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicateGroup) {
            $ids = array_filter(array_map('intval', explode(',', $duplicateGroup->ids)), function ($id) use ($duplicateGroup) {
                return $id !== (int) $duplicateGroup->keep_id;
            });

            if (!empty($ids)) {
                DB::table('bateria_equipo')
                    ->whereIn('bat_id', $ids)
                    ->update(['bat_id' => (int) $duplicateGroup->keep_id]);

                DB::table('bateria')
                    ->whereIn('bat_id', $ids)
                    ->delete();
            }
        }

        $hasUniqueIndex = DB::selectOne("SHOW INDEX FROM bateria WHERE Key_name = 'bateria_unique_signature'");

        if (!$hasUniqueIndex) {
            Schema::table('bateria', function (Blueprint $table) {
                $table->unique(['bat_marca', 'bat_modelo', 'bat_voltaje', 'bat_capacidad'], 'bateria_unique_signature');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('bateria')) {
            return;
        }

        $hasUniqueIndex = DB::selectOne("SHOW INDEX FROM bateria WHERE Key_name = 'bateria_unique_signature'");

        if ($hasUniqueIndex) {
            Schema::table('bateria', function (Blueprint $table) {
                $table->dropUnique('bateria_unique_signature');
            });
        }
    }
};
