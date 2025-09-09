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
    private ImportProgressReporter $progressReporter;
    private ImportLogger $logger;

    public function __construct(
        DataReaderInterface $reader,
        DataWriterInterface $writer,
        DataTransformerInterface $transformer,
        ImportProgressReporter $progressReporter = null,
        ImportLogger $logger = null
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->transformer = $transformer;
        $this->progressReporter = $progressReporter ?? new ImportProgressReporter();
        $this->logger = $logger ?? new ImportLogger();
    }

    public function import(): array
    {
        $this->progressReporter->initializeStats();
        
        // Log import start
        $this->logger->logImportStart(
            $this->reader->getRecordCount(),
            $this->reader->getHeaders()
        );
        
        try {
            $this->writer->beginTransaction();
            
            $headers = $this->reader->getHeaders();
            
            foreach ($this->reader->read() as $index => $rawData) {
                $this->processRecord($rawData, $headers, $index);
                
                // Progress reporting using the new service
                if ($this->progressReporter->shouldReportProgress($index + 1)) {
                    $this->progressReporter->reportProgress($index + 1);
                }
            }
            
            $this->writer->finalize();
            $this->writer->commit();
            
            $this->progressReporter->setStatus('completed');
            
            // Log import summary
            $this->logger->logImportCompleted($this->getImportSummary());
            
        } catch (\Exception $e) {
            $this->writer->rollback();
            $this->progressReporter->setStatus('failed');
            $this->progressReporter->setError($e->getMessage());
            throw $e;
        } finally {
            $this->reader->close();
        }

        return $this->getImportSummary();
    }

    public function getStats(): array
    {
        return $this->progressReporter->getStats();
    }

    public function getErrors(): array
    {
        return $this->progressReporter->getStats()['errors'] ?? [];
    }

    private function processRecord(array $rawData, array $headers, int $index): void
    {
        try {
            $this->progressReporter->recordProcessed();
            
            // Transform the data
            $transformedData = $this->transformer->transform($rawData, $headers);
            
            // Special handling for reviews - transform external IDs to internal IDs
            if ($this->transformer instanceof \App\Services\DataTransformers\ReviewDataTransformer) {
                $transformedData = $this->transformer->transformExternalIds($transformedData);
            }
            
            // Validate the transformed data
            if (!$this->transformer->validate($transformedData)) {
                $validationErrors = $this->transformer->getValidationErrors();
                $this->progressReporter->recordFailure('Validation failed: ' . implode(', ', $validationErrors), $index + 1);
                
                // Log validation errors using the new logger
                $this->logger->logValidationError($index + 1, $validationErrors, $rawData, $transformedData);
                return;
            }
            
            // Write the data
            if ($this->writer->writeRecord($transformedData)) {
                $this->progressReporter->recordSuccess();
            } else {
                $this->progressReporter->recordFailure('Write failed: Could not write record to destination', $index + 1);
                
                // Log write failures using the new logger
                $this->logger->logWriteError($index + 1, $transformedData);
            }
            
        } catch (\Exception $e) {
            $this->progressReporter->recordFailure('Processing error: ' . $e->getMessage(), $index + 1);
            
            // Log processing errors using the new logger
            $this->logger->logProcessingError($index + 1, $e->getMessage(), $rawData);
        }
    }

    private function getImportSummary(): array
    {
        $stats = $this->progressReporter->getStats();
        $stats['duration_seconds'] = $stats['started_at']->diffInSeconds(now());
        $stats['success_rate'] = $stats['total_processed'] > 0 
            ? round(($stats['successful'] / $stats['total_processed']) * 100, 2) 
            : 0;
        
        return [
            'stats' => $stats,
            'errors' => $this->getErrors(),
            'success' => $stats['status'] === 'completed' && $stats['failed'] === 0,
        ];
    }
}
