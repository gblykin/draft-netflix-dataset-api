<?php

namespace Tests\Unit\Services;

use App\Enums\SubscriptionPlan;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    private function createTestUser(array $overrides = []): User
    {
        $defaults = [
            'external_user_id' => 'test-user-' . uniqid(),
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'country' => 'USA',
            'city' => 'New York',
            'subscription_plan' => SubscriptionPlan::BASIC,
            'subscription_start_date' => now()->toDateString(),
            'source_created_at' => now()->subMonths(6),
        ];

        return User::create(array_merge($defaults, $overrides));
    }

    public function test_get_filtered_users_returns_paginated_results()
    {
        // Create test users using helper
        $this->createTestUser([
            'external_user_id' => 'test-user-1',
            'email' => 'user1@test.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'country' => 'USA',
            'city' => 'New York',
        ]);
        $this->createTestUser([
            'external_user_id' => 'test-user-2',
            'email' => 'user2@test.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'Canada',
            'city' => 'Toronto',
        ]);

        $result = $this->userService->getFilteredUsers([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }



}
