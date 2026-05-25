<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_settings', function (Blueprint $table) {

            $table->id('sett_idsett');

            $table->string('sett_cdkeys', 100)
                ->unique();

            $table->text('sett_dsvalu');

            $table->string('sett_nmgrou', 80);

            $table->timestamp('sett_dtupda')
                ->useCurrent()
                ->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_settings');
    }
};
