<?php

namespace App\Services;

class ImportProgressReporter
{
    private array $stats = [];
    private int $lastReportedCount = 0;
    private array $recentErrors = [];
    private int $maxErrorsToKeep = 50; // Keep only last 50 errors in memory

    public function __construct()
    {
        $this->initializeStats();
    }

    public function initializeStats(): void
    {
        $this->stats = [
            'total_processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'status' => 'running',
            'started_at' => now(),
            'errors' => []
        ];
        $this->lastReportedCount = 0;
        $this->recentErrors = [];
    }

    public function recordProcessed(): void
    {
        $this->stats['total_processed']++;
    }

    public function recordSuccess(): void
    {
        $this->stats['successful']++;
    }

    public function recordFailure(string $error, ?int $row = null): void
    {
        $this->stats['failed']++;
        
        // Add error to recent errors (memory-efficient)
        $errorData = [
            'row' => $row,
            'type' => 'Error',
            'details' => $error
        ];
        
        $this->recentErrors[] = $errorData;
        
        // Keep only the most recent errors to prevent memory issues
        if (count($this->recentErrors) > $this->maxErrorsToKeep) {
            array_shift($this->recentErrors); // Remove oldest error
        }
    }

    public function shouldReportProgress(int $currentCount): bool
    {
        return ($currentCount - $this->lastReportedCount) >= 1000;
    }

    public function reportProgress(int $currentCount): void
    {
        $percentage = $this->stats['total_processed'] > 0 
            ? round(($currentCount / $this->stats['total_processed']) * 100, 1) 
            : 0;
        
        echo "Processed {$currentCount} of {$this->stats['total_processed']} ({$percentage}%) records...\n";
        $this->lastReportedCount = $currentCount;
    }

    public function getStats(): array
    {
        $stats = $this->stats;
        $stats['errors'] = $this->recentErrors; // Return only recent errors
        return $stats;
    }
    
    public function getRecentErrors(): array
    {
        return $this->recentErrors;
    }
    
    public function getErrorCount(): int
    {
        return $this->stats['failed'];
    }
    
    public function setMaxErrorsToKeep(int $maxErrors): void
    {
        $this->maxErrorsToKeep = $maxErrors;
    }
    
    public function getMaxErrorsToKeep(): int
    {
        return $this->maxErrorsToKeep;
    }

    public function setStatus(string $status): void
    {
        $this->stats['status'] = $status;
    }

    public function setError(string $error): void
    {
        $this->stats['error'] = $error;
    }
}

