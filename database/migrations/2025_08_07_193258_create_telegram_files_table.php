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
        Schema::create('telegram_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('file_name');
            $table->string('mime_type');
            $table->string('file_id');
            $table->string('file_unique_id');
            $table->string('file_size');
            $table->longText('caption')->nullable();
            // Self-referencing foreign key for parent folder
            $table->unsignedBigInteger('parent_folder_id')->nullable();
            $table->foreign('parent_folder_id')
                ->references('id')
                ->on('telegram_folders')
                ->onDelete('cascade'); // <- Important
                                   // $table->foreignId('user_id')->constrained()->onDelete('cascade');
                                   // or if your users table uses a custom name:
            $table->string('user_id');
            $table->foreign('user_id')->references('user_id')->on('telegram_users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_files');
    }
};
