<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_menu_items', function (Blueprint $table) {

            $table->id('mnit_idmnit');

            $table->foreignId('mnit_idmenu')
                ->constrained('hycms_menus', 'menu_idmenu')
                ->cascadeOnDelete();

            $table->string('mnit_nmlabe', 100);

            $table->enum('mnit_cdtype', [
                'content',
                'url',
                'category'
            ]);

            $table->unsignedBigInteger('mnit_idrefi')
                ->nullable();

            $table->string('mnit_nmlkbl', 100)
                ->nullable();

            $table->string('mnit_dsurll', 500)
                ->nullable();

            $table->foreignId('mnit_idpare')
                ->nullable()
                ->constrained('hycms_menu_items', 'mnit_idmnit')
                ->nullOnDelete();

            $table->integer('mnit_nrorde')
                ->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_menu_items');
    }
};
