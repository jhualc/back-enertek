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

        Schema::table('bateria', function (Blueprint $table) {
            if (!Schema::hasColumn('bateria', 'mar_id')) {
                $table->unsignedBigInteger('mar_id')->nullable()->after('bat_capacidad');
            }
        });

        $brandRows = DB::table('bateria')
            ->select('bat_marca')
            ->distinct()
            ->get();

        foreach ($brandRows as $brandRow) {
            $brandName = trim((string) $brandRow->bat_marca);
            if ($brandName === '') {
                continue;
            }

            $marca = DB::table('marca')->where('mar_descripcion', $brandName)->first();
            if (!$marca) {
                $marcaId = DB::table('marca')->insertGetId([
                    'mar_descripcion' => $brandName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $marcaId = $marca->mar_id;
            }

            DB::table('bateria')
                ->where('bat_marca', $brandName)
                ->update(['mar_id' => $marcaId]);
        }

        Schema::table('bateria', function (Blueprint $table) {
            if (Schema::hasColumn('bateria', 'mar_id')) {
                $table->foreign('mar_id')->references('mar_id')->on('marca')->onDelete('set null');
            }
        });

        if (DB::selectOne("SHOW INDEX FROM bateria WHERE Key_name = 'bateria_unique_signature'")) {
            Schema::table('bateria', function (Blueprint $table) {
                $table->dropUnique('bateria_unique_signature');
            });
        }

        Schema::table('bateria', function (Blueprint $table) {
            if (Schema::hasColumn('bateria', 'bat_marca')) {
                $table->dropColumn('bat_marca');
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
            if (!Schema::hasColumn('bateria', 'bat_marca')) {
                $table->string('bat_marca')->nullable()->after('bat_capacidad');
            }
        });

        $bateriaRows = DB::table('bateria')
            ->select('bat_id', 'mar_id')
            ->whereNotNull('mar_id')
            ->get();

        foreach ($bateriaRows as $bateriaRow) {
            $marcaDescripcion = DB::table('marca')
                ->where('mar_id', $bateriaRow->mar_id)
                ->value('mar_descripcion');

            if ($marcaDescripcion !== null) {
                DB::table('bateria')
                    ->where('bat_id', $bateriaRow->bat_id)
                    ->update(['bat_marca' => $marcaDescripcion]);
            }
        }

        Schema::table('bateria', function (Blueprint $table) {
            if (Schema::hasColumn('bateria', 'mar_id')) {
                $table->dropForeign(['mar_id']);
                $table->dropColumn('mar_id');
            }
        });
    }
};
