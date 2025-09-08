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
