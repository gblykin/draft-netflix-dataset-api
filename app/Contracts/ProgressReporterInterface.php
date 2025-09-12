<?php

namespace App\Contracts;

interface ProgressReporterInterface
{
    public function initializeStats(): void;
    public function recordProcessed(): void;
    public function recordSuccess(): void;
    public function recordInsert(): void;
    public function recordUpdate(): void;
    public function recordFailure(string $error, ?int $row = null): void;
    public function shouldReportProgress(int $currentCount): bool;
    public function reportProgress(int $currentCount): void;
    public function getStats(): array;
    public function getRecentErrors(): array;
    public function getErrorCount(): int;
    public function setMaxErrorsToKeep(int $maxErrors): void;
    public function getMaxErrorsToKeep(): int;
    public function setStatus(string $status): void;
    public function setError(string $error): void;
    public function setTotalRecords(int $totalRecords): void;
    public function getTotalRecords(): int;
}
