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

    public function test_user_model_can_be_created_and_retrieved()
    {
        $user = $this->createTestUser([
            'external_user_id' => 'test-user-123',
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'country' => 'USA',
            'city' => 'Los Angeles',
        ]);

        $result = User::find($user->id);

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('test-user-123', $result->external_user_id);
    }

    public function test_get_users_by_country_returns_filtered_results()
    {
        $this->createTestUser([
            'external_user_id' => 'user-1',
            'email' => 'user1@test.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'country' => 'United States',
            'city' => 'New York',
        ]);
        $this->createTestUser([
            'external_user_id' => 'user-2',
            'email' => 'user2@test.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'Canada',
            'city' => 'Toronto',
        ]);
        $this->createTestUser([
            'external_user_id' => 'user-3',
            'email' => 'user3@test.com',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'country' => 'United Kingdom',
            'city' => 'London',
        ]);

        $result = $this->userService->getUsersByCountry('United');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items()); // United States and United Kingdom
    }

    public function test_get_active_users_returns_only_active_users()
    {
        $this->createTestUser([
            'external_user_id' => 'user-1',
            'email' => 'user1@test.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'country' => 'USA',
            'city' => 'New York',
            'is_active' => true,
        ]);
        $this->createTestUser([
            'external_user_id' => 'user-2',
            'email' => 'user2@test.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'city' => 'Los Angeles',
            'is_active' => false,
        ]);
        $this->createTestUser([
            'external_user_id' => 'user-3',
            'email' => 'user3@test.com',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'country' => 'USA',
            'city' => 'Chicago',
            'is_active' => true,
        ]);

        $result = $this->userService->getActiveUsers();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }
}
