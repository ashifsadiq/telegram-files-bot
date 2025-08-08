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
        Schema::create('uploading_queue_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('file_id');
            $table->string('file_unique_id')->nullable();
            $table->string('file_size')->nullable();
            $table->longText('caption')->nullable();
            $table->foreignId('uploading_queues_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            //    or if your users table uses a custom name:
            // $table->string('uploading_queues_id');
            // $table->foreign('user_id')->references('user_id')->on('telegram_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploading_queue_files');
    }
};
