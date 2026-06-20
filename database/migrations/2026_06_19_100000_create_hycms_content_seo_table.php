<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_content_seo', function (Blueprint $table) {

            $table->id('cose_idcose');

            $table->foreignId('cose_idcont')
                ->constrained('hycms_contents', 'cont_idcont')
                ->cascadeOnDelete();

            $table->string('cose_nmtitl', 255)->nullable();
            $table->string('cose_dsdesc', 320)->nullable();
            $table->string('cose_dsogim', 500)->nullable();
            $table->string('cose_cdcano', 500)->nullable();
            $table->boolean('cose_bonoix')->default(false);

            $table->timestamp('cose_dtcrea')->useCurrent();
            $table->timestamp('cose_dtupda')->useCurrent()->useCurrentOnUpdate();

            $table->unique('cose_idcont');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_content_seo');
    }
};
