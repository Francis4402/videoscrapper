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
        Schema::create('scraped_videos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('source_url');
            $table->string('source_domain')->nullable();
            $table->string('title')->default('Untitled Video');
            $table->text('description')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->text('video_url')->nullable();
            $table->text('embed_url')->nullable();
            $table->string('video_type')->default('direct'); // direct, hls, embed
            $table->string('duration')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraped_videos');
    }
};
