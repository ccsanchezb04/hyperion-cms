<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE hycms_contents MODIFY cont_cdstat ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE hycms_contents MODIFY cont_cdstat ENUM('draft','published') NOT NULL DEFAULT 'draft'");
    }
};
