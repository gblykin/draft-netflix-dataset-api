<?php

namespace App\Enums;

enum Device: string
{
    case MOBILE = 'Mobile';
    case DESKTOP = 'Desktop';
    case TABLET = 'Tablet';
    case TV = 'TV';
    case SMART_TV = 'Smart TV';
    case GAMING_CONSOLE = 'Gaming Console';
    case LAPTOP = 'Laptop';
    case OTHER = 'Other';

    /**
     * Get all device values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all device cases as an array.
     */
    public static function allCases(): array
    {
        return self::cases();
    }

    /**
     * Get a human-readable label for the device.
     */
    public function label(): string
    {
        return match($this) {
            self::MOBILE => 'Mobile',
            self::DESKTOP => 'Desktop',
            self::TABLET => 'Tablet',
            self::TV => 'TV',
            self::SMART_TV => 'Smart TV',
            self::GAMING_CONSOLE => 'Gaming Console',
            self::LAPTOP => 'Laptop',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get device category for grouping similar devices.
     */
    public function category(): string
    {
        return match($this) {
            self::MOBILE, self::TABLET => 'Mobile Devices',
            self::DESKTOP, self::LAPTOP => 'Computer',
            self::TV, self::SMART_TV => 'TV Devices',
            self::GAMING_CONSOLE => 'Gaming',
            self::OTHER => 'Other',
        };
    }
}
