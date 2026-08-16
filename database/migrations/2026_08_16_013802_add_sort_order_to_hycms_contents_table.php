<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hycms_contents', function (Blueprint $table) {
            $table->unsignedSmallInteger('cont_nrorde')->nullable()->after('cont_dsembd');
        });
    }

    public function down(): void
    {
        Schema::table('hycms_contents', function (Blueprint $table) {
            $table->dropColumn('cont_nrorde');
        });
    }
};
