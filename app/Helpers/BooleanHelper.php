<?php

namespace App\Helpers;

class BooleanHelper
{
    /**
     * Convert various boolean representations to actual boolean value.
     * Supports: true, false, 1, 0, "true", "false", "1", "0"
     */
    public static function convertToBoolean($value): bool
    {
        return in_array($value, ['true', '1', 1, true], true);
    }
}
