<?php

namespace Tests\Unit\Services;

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
            'movie_id' => 'test-movie-1',
            'title' => 'Test Movie 1',
            'content_type' => 'Movie',
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ]);
        Movie::create([
            'movie_id' => 'test-movie-2',
            'title' => 'Test Movie 2',
            'content_type' => 'Movie',
            'genre_primary' => 'Comedy',
            'release_year' => 2022,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ]);

        $request = new Request();
        $result = $this->movieService->getFilteredMovies($request);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }

    public function test_get_movie_by_id_returns_correct_movie()
    {
        $movie = Movie::create([
            'movie_id' => 'test-movie-123',
            'title' => 'Test Movie',
            'content_type' => 'Movie',
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ]);

        $result = $this->movieService->getMovieById('test-movie-123');

        $this->assertEquals($movie->id, $result->id);
        $this->assertEquals('test-movie-123', $result->movie_id);
    }

    public function test_get_movie_by_id_throws_exception_when_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        $this->movieService->getMovieById('non-existent-id');
    }
}
