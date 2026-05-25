<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_permissions', function (Blueprint $table) {
            $table->id('perm_idperm');
            
            $table->string('perm_nmname', 100);
            $table->string('perm_cdslug', 100)->unique();
            $table->text('perm_dsdesc')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_permissions');
    }
};
