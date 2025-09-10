<?php

namespace App\Services;

use App\Helpers\BooleanHelper;
use App\Models\User;
use App\Services\Filters\UserFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function getFilteredUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::query();
        
        // Check if user wants to exclude reviewed movies for performance
        $excludeReviewedMovies = isset($filters['exclude_reviewed_movies']) && 
            BooleanHelper::convertToBoolean($filters['exclude_reviewed_movies']);
        
        if (!$excludeReviewedMovies) {
            $query->with(['reviewedMovies' => function ($query) use ($filters) {
                // Sort by review creation date (pivot table created_at)
                $query->orderBy('reviews.created_at', 'desc');
                
                // Limit reviewed movies per user unless user wants all movies
                $showAllReviewedMovies = isset($filters['show_all_reviewed_movies']) && 
                    BooleanHelper::convertToBoolean($filters['show_all_reviewed_movies']);
                
                if (!$showAllReviewedMovies) {
                    $query->limit(10);
                }
            }]);
        }
        
        $filterService = new UserFilterService($query, $filters);
        
        return $filterService->apply();
    }

    public function getUserById(string $id): User
    {
        return User::with(['reviews.movie', 'reviewedMovies'])
            ->where('id', $id)
            ->firstOrFail();
    }

    public function getUsersByCountry(string $country): LengthAwarePaginator
    {
        return User::where('country', 'like', '%' . $country . '%')
            ->with(['reviews', 'reviewedMovies'])
            ->paginate(15);
    }

    public function getActiveUsers(): LengthAwarePaginator
    {
        return User::where('is_active', true)
            ->with(['reviews', 'reviewedMovies'])
            ->paginate(15);
    }
}
