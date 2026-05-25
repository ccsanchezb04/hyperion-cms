<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hycms_roles', function (Blueprint $table) {

            $table->id('role_idrole');

            $table->string('role_nmname', 80);

            $table->string('role_cdslug', 80)
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_roles');
    }
};
