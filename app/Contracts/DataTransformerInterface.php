<?php

namespace App\Contracts;

interface DataTransformerInterface
{
    /**
     * Transform raw data into the target format
     *
     * @param array $rawData
     * @param array $headers
     * @return array
     */
    public function transform(array $rawData, array $headers): array;

    /**
     * Validate the transformed data
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool;

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getValidationErrors(): array;
}

