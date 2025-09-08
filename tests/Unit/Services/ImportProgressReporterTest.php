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

    public function test_memory_efficient_error_handling()
    {
        // Set a small limit for testing
        $this->reporter->setMaxErrorsToKeep(3);
        
        // Add more errors than the limit
        $this->reporter->recordFailure('Error 1', 1);
        $this->reporter->recordFailure('Error 2', 2);
        $this->reporter->recordFailure('Error 3', 3);
        $this->reporter->recordFailure('Error 4', 4);
        $this->reporter->recordFailure('Error 5', 5);
        
        $stats = $this->reporter->getStats();
        
        // Should have 5 total failures
        $this->assertEquals(5, $stats['failed']);
        
        // But only keep the last 3 errors in memory
        $this->assertCount(3, $stats['errors']);
        
        // Should keep the most recent errors
        $this->assertEquals('Error 3', $stats['errors'][0]['details']);
        $this->assertEquals('Error 4', $stats['errors'][1]['details']);
        $this->assertEquals('Error 5', $stats['errors'][2]['details']);
    }

    public function test_get_recent_errors_returns_correct_errors()
    {
        $this->reporter->recordFailure('Error 1', 1);
        $this->reporter->recordFailure('Error 2', 2);
        
        $recentErrors = $this->reporter->getRecentErrors();
        
        $this->assertCount(2, $recentErrors);
        $this->assertEquals('Error 1', $recentErrors[0]['details']);
        $this->assertEquals(1, $recentErrors[0]['row']);
    }

    public function test_get_error_count_returns_correct_count()
    {
        $this->reporter->recordFailure('Error 1');
        $this->reporter->recordFailure('Error 2');
        $this->reporter->recordFailure('Error 3');
        
        $this->assertEquals(3, $this->reporter->getErrorCount());
    }

    public function test_set_max_errors_to_keep_updates_limit()
    {
        $this->reporter->setMaxErrorsToKeep(100);
        $this->assertEquals(100, $this->reporter->getMaxErrorsToKeep());
    }
}
