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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('external_review_id')->unique(); 
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->foreignId('movie_id')->constrained()->onDelete('CASCADE');
            $table->integer('rating'); // 1-5 stars
            $table->date('review_date'); 
            $table->string('device_type');
            $table->boolean('is_verified_watch')->default(false);
            $table->integer('helpful_votes')->default(0);
            $table->integer('total_votes')->default(0);
            $table->text('review_text')->nullable();
            $table->string('sentiment')->nullable(); // Positive, Negative, Neutral
            $table->decimal('sentiment_score', 5, 4)->nullable(); // -1.0 to 1.0
            $table->timestamps();

                        
            $table->index('rating');
            $table->index('is_verified_watch');
            $table->index('review_date');
            $table->index('sentiment');
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
