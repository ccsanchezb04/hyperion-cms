<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_content_category', function (Blueprint $table) {

            $table->foreignId('coca_idcont')
                ->constrained('hycms_contents', 'cont_idcont')
                ->cascadeOnDelete();

            $table->foreignId('coca_idcate')
                ->constrained('hycms_categories', 'cate_idcate')
                ->cascadeOnDelete();

            $table->primary([
                'coca_idcont',
                'coca_idcate'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_content_category');
    }
};
