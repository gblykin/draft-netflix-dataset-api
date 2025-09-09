<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserListRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of users with their reviewed movies.
     */
    public function index(UserListRequest $request): AnonymousResourceCollection
    { 
        $requestData = $request->all();
        $users = $this->userService->getFilteredUsers($requestData);
        return UserResource::collection($users);
    }

    /**
     * Display the specified user with their reviews and movies.
     */
    public function show(string $id): UserResource
    {
        $user = $this->userService->getUserById($id);
        return new UserResource($user);
    }
}

