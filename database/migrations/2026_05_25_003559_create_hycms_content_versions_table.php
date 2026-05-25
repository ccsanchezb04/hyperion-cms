<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_content_versions', function (Blueprint $table) {

            $table->id('cove_idcove');

            $table->foreignId('cove_idcont')
                ->constrained('hycms_contents', 'cont_idcont')
                ->cascadeOnDelete();

            $table->longText('cove_dsbody');

            $table->integer('cove_nrvers');

            $table->timestamp('cove_dtcrea')
                ->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_content_versions');
    }
};
