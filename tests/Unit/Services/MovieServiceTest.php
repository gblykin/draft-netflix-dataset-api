<?php

namespace Tests\Unit\Services;

use App\Enums\ContentType;
use App\Models\Movie;
use App\Services\MovieService;
use App\Services\Filters\MovieFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use Mockery;

class MovieServiceTest extends TestCase
{
    use RefreshDatabase;

    private MovieService $movieService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->movieService = new MovieService();
    }

    public function test_get_filtered_movies_returns_paginated_results()
    {
        // Create test movies directly
        Movie::create([
            'external_movie_id' => 'test-movie-1',
            'title' => 'Test Movie 1',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ]);
        Movie::create([
            'external_movie_id' => 'test-movie-2',
            'title' => 'Test Movie 2',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Comedy',
            'release_year' => 2022,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ]);

        $result = $this->movieService->getFilteredMovies([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }

    public function test_movie_model_can_be_created_and_retrieved()
    {
        $movie = Movie::create([
            'external_movie_id' => 'test-movie-123',
            'title' => 'Test Movie',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ]);

        $result = Movie::find($movie->id);

        $this->assertEquals($movie->id, $result->id);
        $this->assertEquals('test-movie-123', $result->external_movie_id);
    }

    public function test_movie_model_returns_null_when_not_found()
    {
        // Use a very large number that's unlikely to exist
        $result = Movie::find(PHP_INT_MAX);
        
        $this->assertNull($result);
    }
}
