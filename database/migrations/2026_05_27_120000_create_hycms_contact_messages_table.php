<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hycms_contact_messages', function (Blueprint $table) {
            $table->id('cmsg_idcmsg');

            $table->string('cmsg_nmname', 150);
            $table->string('cmsg_dsemai', 190);
            $table->string('cmsg_cdsubj', 50);
            $table->text('cmsg_dsmess');

            $table->string('cmsg_dsipad', 45)->nullable();
            $table->string('cmsg_dsuage', 255)->nullable();

            $table->timestamp('cmsg_dtread')->nullable();

            $table->timestamp('cmsg_dtcrea')->useCurrent();
            $table->timestamp('cmsg_dtupda')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes('cmsg_dtdele');

            $table->index('cmsg_dtread');
            $table->index('cmsg_dtcrea');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hycms_contact_messages');
    }
};
