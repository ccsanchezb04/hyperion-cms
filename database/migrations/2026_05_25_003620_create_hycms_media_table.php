<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_media', function (Blueprint $table) {

            $table->id('medi_idmedi');

            $table->string('medi_dspath', 500);

            $table->string('medi_cdtype', 50);

            $table->unsignedBigInteger('medi_idmdbl')
                ->nullable();

            $table->string('medi_nmmdbl', 100)
                ->nullable();

            $table->foreignId('medi_idusby')
                ->constrained('hycms_users', 'user_iduser');

            $table->timestamp('medi_dtcrea')
                ->useCurrent();

            $table->index([
                'medi_idmdbl',
                'medi_nmmdbl'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_media');
    }
};
