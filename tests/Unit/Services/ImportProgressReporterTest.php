<?php

namespace Tests\Unit\Services;

use App\Services\ImportProgressReporter;
use Tests\TestCase;

class ImportProgressReporterTest extends TestCase
{
    private ImportProgressReporter $reporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reporter = new ImportProgressReporter();
    }

    public function test_initialize_stats_sets_initial_values()
    {
        $this->reporter->initializeStats();
        $stats = $this->reporter->getStats();

        $this->assertEquals(0, $stats['total_processed']);
        $this->assertEquals(0, $stats['successful']);
        $this->assertEquals(0, $stats['failed']);
        $this->assertEquals('running', $stats['status']);
        $this->assertArrayHasKey('started_at', $stats);
    }

    public function test_record_processed_increments_count()
    {
        $this->reporter->recordProcessed();
        $this->reporter->recordProcessed();

        $stats = $this->reporter->getStats();
        $this->assertEquals(2, $stats['total_processed']);
    }

    public function test_record_success_increments_success_count()
    {
        $this->reporter->recordSuccess();
        $this->reporter->recordSuccess();

        $stats = $this->reporter->getStats();
        $this->assertEquals(2, $stats['successful']);
    }

    public function test_record_failure_increments_failure_count()
    {
        $this->reporter->recordFailure('Test error');
        $this->reporter->recordFailure('Another error');

        $stats = $this->reporter->getStats();
        $this->assertEquals(2, $stats['failed']);
        $this->assertCount(2, $stats['errors']);
    }

    public function test_should_report_progress_returns_true_when_threshold_reached()
    {
        $this->assertTrue($this->reporter->shouldReportProgress(1000));
        $this->assertTrue($this->reporter->shouldReportProgress(2000));
        $this->assertFalse($this->reporter->shouldReportProgress(500));
    }

    public function test_set_status_updates_status()
    {
        $this->reporter->setStatus('completed');
        $stats = $this->reporter->getStats();

        $this->assertEquals('completed', $stats['status']);
    }

    public function test_set_error_updates_error()
    {
        $this->reporter->setError('Test error message');
        $stats = $this->reporter->getStats();

        $this->assertEquals('Test error message', $stats['error']);
    }
}
