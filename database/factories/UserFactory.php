<?php

namespace Database\Factories;

use App\Enums\Device;
use App\Enums\Gender;
use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_user_id' => $this->faker->unique()->uuid(),
            'email' => $this->faker->unique()->safeEmail(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'age' => $this->faker->numberBetween(18, 80),
            'gender' => $this->faker->randomElement([...Gender::cases(), null]),
            'country' => $this->faker->country(),
            'state_province' => $this->faker->state(),
            'city' => $this->faker->city(),
            'subscription_plan' => $this->faker->randomElement(SubscriptionPlan::cases()),
            'subscription_start_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'monthly_spend' => $this->faker->randomFloat(2, 0, 50),
            'primary_device' => $this->faker->randomElement(Device::cases()),
            'household_size' => $this->faker->numberBetween(1, 6),
            'source_created_at' => $this->faker->dateTimeBetween('-3 years', '-1 month'),
        ];
    }
}
