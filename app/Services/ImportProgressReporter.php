<?php

namespace App\Services;

class ImportProgressReporter
{
    private array $stats = [];
    private int $lastReportedCount = 0;

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
    }

    public function recordProcessed(): void
    {
        $this->stats['total_processed']++;
    }

    public function recordSuccess(): void
    {
        $this->stats['successful']++;
    }

    public function recordFailure(string $error): void
    {
        $this->stats['failed']++;
        $this->stats['errors'][] = $error;
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
        return $this->stats;
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

