<?php

namespace App\Services;

use App\Services\DataReaders\CsvDataReader;
use App\Services\DataWriters\DatabaseWriter;
use App\Services\DataTransformers\UserDataTransformer;
use App\Services\DataTransformers\MovieDataTransformer;
use App\Services\DataTransformers\ReviewDataTransformer;
use App\Models\User;
use App\Models\Movie;
use App\Models\Review;

class DataImportFactory
{
    public static function createCsvImporter(string $entityType, string $filePath): DataImportService
    {
        $reader = new CsvDataReader($filePath);
        
        [$writer, $transformer] = self::createWriterAndTransformer($entityType);
        
        return new DataImportService($reader, $writer, $transformer);
    }

    public static function createWriterAndTransformer(string $entityType): array
    {
        return match (strtolower($entityType)) {
            'users' => [
                new DatabaseWriter(User::class, 'external_user_id', true), // Use external_user_id as unique key
                new UserDataTransformer(),
            ],
            'movies' => [
                new DatabaseWriter(Movie::class, 'external_movie_id', true),
                new MovieDataTransformer(),
            ],
            'reviews' => [
                new DatabaseWriter(Review::class, 'external_review_id', true),
                new ReviewDataTransformer(),
            ],
            default => throw new \InvalidArgumentException("Unsupported entity type: {$entityType}"),
        };
    }


}
