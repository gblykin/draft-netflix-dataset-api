<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case BASIC = 'Basic';
    case STANDARD = 'Standard';
    case PREMIUM = 'Premium';
    case PREMIUM_PLUS = 'Premium+';

    /**
     * Get all subscription plan values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all subscription plan cases as an array.
     */
    public static function allCases(): array
    {
        return self::cases();
    }

    /**
     * Get a human-readable label for the subscription plan.
     */
    public function label(): string
    {
        return match($this) {
            self::BASIC => 'Basic',
            self::STANDARD => 'Standard',
            self::PREMIUM => 'Premium',
            self::PREMIUM_PLUS => 'Premium+',
        };
    }

    /**
     * Get the tier level for the subscription plan (1-4).
     */
    public function tier(): int
    {
        return match($this) {
            self::BASIC => 1,
            self::STANDARD => 2,
            self::PREMIUM => 3,
            self::PREMIUM_PLUS => 4,
        };
    }

    /**
     * Get the category for the subscription plan.
     */
    public function category(): string
    {
        return match($this) {
            self::BASIC => 'Entry Level',
            self::STANDARD => 'Standard',
            self::PREMIUM => 'Premium',
            self::PREMIUM_PLUS => 'Premium',
        };
    }

    /**
     * Check if this plan is a premium tier.
     */
    public function isPremium(): bool
    {
        return in_array($this, [self::PREMIUM, self::PREMIUM_PLUS]);
    }

    /**
     * Check if this plan is the highest tier.
     */
    public function isHighestTier(): bool
    {
        return $this === self::PREMIUM_PLUS;
    }
}
