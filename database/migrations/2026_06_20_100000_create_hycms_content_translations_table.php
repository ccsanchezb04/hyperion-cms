<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_content_translations', function (Blueprint $table) {

            $table->id('cotr_idcotr');

            $table->foreignId('cotr_idcont')
                ->constrained('hycms_contents', 'cont_idcont')
                ->cascadeOnDelete();

            $table->string('cotr_cdlang', 10);

            $table->string('cotr_nmtitl', 255)->nullable();
            $table->text('cotr_dsbody')->nullable();

            $table->timestamp('cotr_dtcrea')->useCurrent();
            $table->timestamp('cotr_dtupda')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['cotr_idcont', 'cotr_cdlang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_content_translations');
    }
};
