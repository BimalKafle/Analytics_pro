<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date')->index();
            $table->unsignedBigInteger('views')->default(0);
            // Nullable metrics distinguish "platform does not provide this"
            // from a real zero (e.g. YouTube exposes no impressions/CTR via API).
            $table->unsignedBigInteger('likes')->nullable();
            $table->unsignedBigInteger('comments')->nullable();
            $table->unsignedBigInteger('shares')->nullable();
            $table->unsignedBigInteger('watch_time_seconds')->nullable();
            $table->unsignedInteger('avg_view_duration_seconds')->nullable();
            $table->unsignedBigInteger('impressions')->nullable();
            $table->decimal('ctr', 7, 4)->nullable();
            $table->integer('subscribers_gained')->nullable();
            // Raw platform response, preserved for reprocessing and future AI phases.
            $table->jsonb('native_metric_payload')->nullable();
            $table->timestamps();

            $table->unique(['video_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
    }
};
