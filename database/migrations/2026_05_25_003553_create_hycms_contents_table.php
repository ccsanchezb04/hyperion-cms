<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_contents', function (Blueprint $table) {

            $table->id('cont_idcont');

            $table->string('cont_nmtitl', 255);

            $table->string('cont_cdslug', 255)
                ->unique();

            $table->enum('cont_cdtype', [
                'post',
                'page',
                'custom'
            ]);

            $table->enum('cont_cdstat', [
                'draft',
                'published'
            ])->default('draft');

            $table->foreignId('cont_idauth')
                ->constrained('hycms_users', 'user_iduser');

            $table->timestamp('cont_dtpubl')
                ->nullable();

            $table->timestamp('cont_dtcrea')
                ->useCurrent();

            $table->timestamp('cont_dtupda')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->softDeletes('cont_dtdele');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_contents');
    }
};
