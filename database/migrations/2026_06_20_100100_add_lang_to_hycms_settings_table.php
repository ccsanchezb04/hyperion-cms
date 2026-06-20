<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hycms_settings', function (Blueprint $table) {
            $table->string('sett_cdlang', 10)->nullable()->after('sett_nmgrou');
            $table->index('sett_cdlang');
        });

        // Si la tabla tiene un unique en sett_cdkeys, hay que reemplazarlo por
        // un unique compuesto (sett_cdkeys, sett_cdlang). En SQLite el constraint
        // sin nombre se autogenera distinto y dropUnique falla; lo intentamos
        // con un nombre explícito y caemos al swap manual si no existe.
        try {
            Schema::table('hycms_settings', function (Blueprint $table) {
                $table->dropUnique('hycms_settings_sett_cdkeys_unique');
            });
        } catch (\Throwable $e) {
            // unique no existía; seguir
        }

        Schema::table('hycms_settings', function (Blueprint $table) {
            $table->unique(['sett_cdkeys', 'sett_cdlang']);
        });
    }

    public function down(): void
    {
        Schema::table('hycms_settings', function (Blueprint $table) {
            $table->dropUnique(['sett_cdkeys', 'sett_cdlang']);
            $table->dropIndex(['sett_cdlang']);
            $table->dropColumn('sett_cdlang');
            $table->unique('sett_cdkeys');
        });
    }
};
