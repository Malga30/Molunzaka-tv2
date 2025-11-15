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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->string('avatar')->nullable()->comment('URL or path to profile avatar');
            $table->boolean('kids_mode')->default(false)->comment('Enable kids mode for this profile');
            $table->json('parental_controls')->nullable()->comment('Parental control settings: content_rating, watch_time_limit, require_pin, pin_code');
            $table->json('preferences')->nullable()->comment('User preferences: language, subtitle_language, quality, autoplay, notifications');
            $table->timestamps();
            
            $table->index('user_id');
            $table->unique(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
