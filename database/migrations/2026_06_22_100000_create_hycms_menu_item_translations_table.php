<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_menu_item_translations', function (Blueprint $table) {

            $table->id('mitr_idmitr');

            $table->foreignId('mitr_idmnit')
                ->constrained('hycms_menu_items', 'mnit_idmnit')
                ->cascadeOnDelete();

            $table->string('mitr_cdlang', 10);
            $table->string('mitr_nmlabe', 120);

            $table->timestamp('mitr_dtcrea')->useCurrent();
            $table->timestamp('mitr_dtupda')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['mitr_idmnit', 'mitr_cdlang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_menu_item_translations');
    }
};
