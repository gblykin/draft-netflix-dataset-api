<?php

use App\Enums\ContentType;
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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('external_movie_id')->nullable()->unique(); 
            $table->string('title');
            $table->enum('content_type', ContentType::values());
            $table->string('genre_primary');
            $table->string('genre_secondary')->nullable();
            $table->year('release_year');
            $table->integer('duration_minutes')->nullable();
            $table->string('rating')->nullable();
            $table->string('language');
            $table->string('country_of_origin');
            $table->decimal('imdb_rating', 3, 1)->nullable();
            $table->decimal('production_budget', 15, 2)->nullable();
            $table->decimal('box_office_revenue', 15, 2)->nullable();
            $table->integer('number_of_seasons')->nullable();
            $table->integer('number_of_episodes')->nullable();
            $table->boolean('is_netflix_original')->default(false);
            $table->date('added_to_platform')->nullable();
            $table->boolean('content_warning')->default(false);
            $table->timestamps();
            
            $table->index('content_type');
            $table->index('genre_primary');
            $table->index('genre_secondary');
            $table->index('release_year');
            $table->index('rating');
            $table->index('language');
            $table->index('country_of_origin');
            $table->index('imdb_rating');
            $table->index('is_netflix_original');
            $table->index('content_warning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
