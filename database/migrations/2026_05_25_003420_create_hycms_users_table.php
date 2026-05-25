<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_users', function (Blueprint $table) {

            $table->id('user_iduser');

            $table->string('user_nmname', 100);

            $table->string('user_dsemai', 150)
                ->unique();

            $table->string('user_cdpass', 255);

            $table->enum('user_cdstat', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamp('user_dtcrea')
                ->useCurrent();

            $table->timestamp('user_dtupda')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->softDeletes('user_dtdele');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_users');
    }
};
