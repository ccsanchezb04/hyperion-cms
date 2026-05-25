<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_role_permissions', function (Blueprint $table) {
            $table->id('rope_idrope');
            
            $table->foreignId('rope_idrole')
                ->constrained('hycms_roles', 'role_idrole')
                ->cascadeOnDelete();
            
            $table->foreignId('rope_idperm')
                ->constrained('hycms_permissions', 'perm_idperm')
                ->cascadeOnDelete();
            
            $table->timestamp('rope_dtcrea')->useCurrent();
            
            $table->unique(['rope_idrole', 'rope_idperm']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_role_permissions');
    }
};
