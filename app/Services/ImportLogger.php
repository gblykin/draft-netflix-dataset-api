<?php

namespace App\Services;

class ImportLogger
{
    public function logImportStart(int $totalRecords, array $headers): void
    {
        \Log::channel('import')->info("Import started", [
            'file_info' => [
                'total_records' => $totalRecords,
                'headers' => $headers
            ],
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logImportCompleted(array $summary): void
    {
        \Log::channel('import')->info("Import completed", [
            'summary' => $summary,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logValidationError(int $row, array $errors, array $rawData, array $transformedData): void
    {
        \Log::channel('import')->warning("Validation failed for record", [
            'row' => $row,
            'errors' => $errors,
            'raw_data' => $rawData,
            'transformed_data' => $transformedData,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logWriteError(int $row, array $transformedData): void
    {
        \Log::channel('import')->error("Write failed for record", [
            'row' => $row,
            'transformed_data' => $transformedData,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logProcessingError(int $row, string $error, array $rawData): void
    {
        \Log::channel('import')->error("Processing error for record", [
            'row' => $row,
            'error' => $error,
            'raw_data' => $rawData,
            'timestamp' => now()->toISOString()
        ]);
    }
}

