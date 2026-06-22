<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hycms_content_translations', function (Blueprint $table) {
            $table->string('cotr_cdslug', 200)->nullable()->after('cotr_cdlang');

            // Unique compuesto: el mismo slug puede existir en idiomas distintos
            // (ej. 'seguros' ES + 'insurance' EN), pero NO duplicarse dentro
            // del mismo idioma.
            $table->unique(['cotr_cdlang', 'cotr_cdslug'], 'hycms_cotr_lang_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('hycms_content_translations', function (Blueprint $table) {
            $table->dropUnique('hycms_cotr_lang_slug_unique');
            $table->dropColumn('cotr_cdslug');
        });
    }
};
