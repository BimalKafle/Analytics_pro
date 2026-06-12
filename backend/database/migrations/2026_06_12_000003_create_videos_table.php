<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_account_id')->constrained()->cascadeOnDelete();
            $table->string('platform_video_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('embed_url', 2048);
            $table->timestamp('published_at')->index();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->unique(['platform_account_id', 'platform_video_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
