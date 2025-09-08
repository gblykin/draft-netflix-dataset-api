<?php

namespace App\Services;

use App\Contracts\DataReaderInterface;
use App\Contracts\DataWriterInterface;
use App\Contracts\DataTransformerInterface;

class DataImportService
{
    private DataReaderInterface $reader;
    private DataWriterInterface $writer;
    private DataTransformerInterface $transformer;
    private array $stats = [];
    private array $errors = [];

    public function __construct(
        DataReaderInterface $reader,
        DataWriterInterface $writer,
        DataTransformerInterface $transformer
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->transformer = $transformer;
        $this->initializeStats();
    }

    public function import(): array
    {
        $this->initializeStats();
        
        // Log import start to import log
        \Log::channel('import')->info("Import started", [
            'file_info' => [
                'total_records' => $this->reader->getRecordCount(),
                'headers' => $this->reader->getHeaders()
            ],
            'timestamp' => now()->toISOString()
        ]);
        
        try {
            $this->writer->beginTransaction();
            
            $headers = $this->reader->getHeaders();
            $this->stats['headers'] = $headers;
            
            foreach ($this->reader->read() as $index => $rawData) {
                $this->processRecord($rawData, $headers, $index);
                
                // Progress reporting
                if (($index + 1) % 1000 === 0) {
                    $this->reportProgress($index + 1);
                }
            }
            
            $this->writer->finalize();
            $this->writer->commit();
            
            $this->stats['status'] = 'completed';
            
            // Log import summary to import log
            \Log::channel('import')->info("Import completed", [
                'summary' => [
                    'total_processed' => $this->stats['total_processed'],
                    'successful' => $this->stats['successful'],
                    'failed' => $this->stats['failed'],
                    'success_rate' => $this->stats['total_processed'] > 0 ? 
                        round(($this->stats['successful'] / $this->stats['total_processed']) * 100, 2) : 0,
                    'duration_seconds' => now()->diffInSeconds($this->stats['started_at']),
                    'status' => $this->stats['status']
                ],
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            $this->writer->rollback();
            $this->stats['status'] = 'failed';
            $this->stats['error'] = $e->getMessage();
            throw $e;
        } finally {
            $this->reader->close();
        }

        return $this->getImportSummary();
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function processRecord(array $rawData, array $headers, int $index): void
    {
        try {
            $this->stats['total_processed']++;
            
            // Transform the data
            $transformedData = $this->transformer->transform($rawData, $headers);
            
            // Validate the transformed data
            if (!$this->transformer->validate($transformedData)) {
                $validationErrors = $this->transformer->getValidationErrors();
                $this->recordError($index, 'Validation failed', $validationErrors);
                
                // Log validation errors to import log
                \Log::channel('import')->warning("Validation failed for record", [
                    'row' => $index + 1,
                    'errors' => $validationErrors,
                    'raw_data' => $rawData,
                    'transformed_data' => $transformedData,
                    'timestamp' => now()->toISOString()
                ]);
                return;
            }
            
            // Write the data
            if ($this->writer->writeRecord($transformedData)) {
                $this->stats['successful']++;
            } else {
                $this->recordError($index, 'Write failed', ['Could not write record to destination']);
                
                // Log write failures to import log
                \Log::channel('import')->error("Write failed for record", [
                    'row' => $index + 1,
                    'transformed_data' => $transformedData,
                    'timestamp' => now()->toISOString()
                ]);
            }
            
        } catch (\Exception $e) {
            $this->recordError($index, 'Processing error', [$e->getMessage()]);
            
            // Log processing errors to import log
            \Log::channel('import')->error("Processing error for record", [
                'row' => $index + 1,
                'error' => $e->getMessage(),
                'raw_data' => $rawData,
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    private function recordError(int $index, string $type, array $details): void
    {
        $this->stats['failed']++;
        $this->errors[] = [
            'row' => $index + 1, // 1-based for user display
            'type' => $type,
            'details' => $details,
        ];
        
        // Limit error storage to prevent memory issues
        if (count($this->errors) > 1000) {
            array_shift($this->errors);
            $this->stats['errors_truncated'] = true;
        }
    }

    private function reportProgress(int $processed): void
    {
        $total = $this->reader->getRecordCount();
        $percentage = $total ? round(($processed / $total) * 100, 1) : 0;
        
        echo "Processed {$processed}" . ($total ? " of {$total} ({$percentage}%)" : '') . " records...\n";
    }

    private function initializeStats(): void
    {
        $this->stats = [
            'total_processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'status' => 'in_progress',
            'started_at' => now(),
            'headers' => [],
            'errors_truncated' => false,
        ];
        $this->errors = [];
    }

    private function getImportSummary(): array
    {
        $this->stats['completed_at'] = now();
        $this->stats['duration'] = $this->stats['completed_at']->diffInSeconds($this->stats['started_at']);
        
        return [
            'stats' => $this->stats,
            'errors' => $this->errors,
            'success' => $this->stats['status'] === 'completed' && $this->stats['failed'] === 0,
        ];
    }
}
