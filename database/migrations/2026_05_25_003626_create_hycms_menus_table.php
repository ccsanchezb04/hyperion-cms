<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_menus', function (Blueprint $table) {

            $table->id('menu_idmenu');

            $table->string('menu_nmname', 100);

            $table->string('menu_cdslug', 100)
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_menus');
    }
};
