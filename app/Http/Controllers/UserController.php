<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Display a listing of users with their reviewed movies.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query();

        // Apply filters
        if ($request->has('subscription_type')) {
            $query->where('subscription_type', $request->input('subscription_type'));
        }

        if ($request->has('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        if ($request->has('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->has('age_min')) {
            $query->where('age', '>=', $request->input('age_min'));
        }

        if ($request->has('age_max')) {
            $query->where('age', '<=', $request->input('age_max'));
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'user_id');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['user_id', 'age', 'join_date', 'monthly_revenue'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100); // Max 100 items per page
        $users = $query->with(['reviews', 'reviewedMovies'])->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Display the specified user with their reviews and movies.
     */
    public function show(string $id): UserResource
    {
        $user = User::with(['reviews.movie', 'reviewedMovies'])
            ->where('user_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        return new UserResource($user);
    }
}

