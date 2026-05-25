<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_user_roles', function (Blueprint $table) {

            $table->foreignId('usro_iduser')
                ->constrained('hycms_users', 'user_iduser')
                ->cascadeOnDelete();

            $table->foreignId('usro_idrole')
                ->constrained('hycms_roles', 'role_idrole')
                ->cascadeOnDelete();

            $table->timestamp('usro_dtcrea')
                ->useCurrent();

            $table->primary([
                'usro_iduser',
                'usro_idrole'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_user_roles');
    }
};
