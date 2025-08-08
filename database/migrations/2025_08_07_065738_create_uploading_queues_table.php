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
        Schema::create('uploading_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_folder_id')->nullable();
            $table->foreign('parent_folder_id')
                ->references('id')
                ->on('telegram_folders')
                ->onDelete('cascade'); // <- Important

            // User relationship (referencing 'user_id' from telegram_users)
            $table->string('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('telegram_users')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploading_queues');
    }
};
