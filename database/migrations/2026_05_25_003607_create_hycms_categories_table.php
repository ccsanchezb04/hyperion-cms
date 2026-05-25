<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_categories', function (Blueprint $table) {

            $table->id('cate_idcate');

            $table->string('cate_nmname', 100);

            $table->string('cate_cdslug', 100)
                ->unique();

            $table->foreignId('cate_idpare')
                ->nullable()
                ->constrained('hycms_categories', 'cate_idcate')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_categories');
    }
};
