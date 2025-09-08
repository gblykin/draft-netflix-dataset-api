<?php

namespace App\Services;

use App\Models\User;
use App\Services\Filters\UserFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function getFilteredUsers(Request $request): LengthAwarePaginator
    {
        $query = User::query();
        
        // Check if user wants to exclude reviewed movies for performance
        if (!$request->boolean('exclude_reviewed_movies', false)) {
            $query->with(['reviewedMovies' => function ($query) use ($request) {
                // Sort by review creation date (pivot table created_at)
                $query->orderBy('reviews.created_at', 'desc');
                
                // Limit reviewed movies per user unless user wants all movies
                if (!$request->boolean('show_all_reviewed_movies', false)) {
                    $query->limit(10);
                }
            }]);
        }
        
        $filterService = new UserFilterService($query, $request);
        
        return $filterService->apply();
    }

    public function getUserById(string $id): User
    {
        return User::with(['reviews.movie', 'reviewedMovies'])
            ->where('user_id', $id)
            ->orWhere('id', $id)
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
