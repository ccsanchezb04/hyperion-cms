<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hycms_notifications', function (Blueprint $table) {
            $table->id('noti_idnoti');
            $table->string('noti_nmtype');
            $table->text('noti_dsdata')->nullable();
            $table->unsignedBigInteger('noti_iduser');
            $table->boolean('noti_boread')->default(false);
            $table->timestamp('noti_dtcrea')->useCurrent();
            $table->timestamp('noti_dtreau')->nullable();

            $table->foreign('noti_iduser')->references('user_iduser')->on('hycms_users')->onDelete('cascade');
            $table->index('noti_iduser');
            $table->index('noti_boread');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hycms_notifications');
    }
};
