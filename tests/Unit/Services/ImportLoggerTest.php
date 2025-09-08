<?php

namespace Tests\Unit\Services;

use App\Services\ImportLogger;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ImportLoggerTest extends TestCase
{
    private ImportLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new ImportLogger();
    }

    public function test_log_import_start_logs_correctly()
    {
        Log::shouldReceive('channel')
            ->with('import')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Import started', \Mockery::type('array'))
            ->once();

        $this->logger->logImportStart(100, ['header1', 'header2']);
    }

    public function test_log_import_completed_logs_correctly()
    {
        Log::shouldReceive('channel')
            ->with('import')
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Import completed', \Mockery::type('array'))
            ->once();

        $summary = ['total' => 100, 'successful' => 95];
        $this->logger->logImportCompleted($summary);
    }

    public function test_log_validation_error_logs_correctly()
    {
        Log::shouldReceive('channel')
            ->with('import')
            ->andReturnSelf();
        
        Log::shouldReceive('warning')
            ->with('Validation failed for record', \Mockery::type('array'))
            ->once();

        $this->logger->logValidationError(1, ['error1', 'error2'], ['raw' => 'data'], ['transformed' => 'data']);
    }

    public function test_log_write_error_logs_correctly()
    {
        Log::shouldReceive('channel')
            ->with('import')
            ->andReturnSelf();
        
        Log::shouldReceive('error')
            ->with('Write failed for record', \Mockery::type('array'))
            ->once();

        $this->logger->logWriteError(1, ['transformed' => 'data']);
    }

    public function test_log_processing_error_logs_correctly()
    {
        Log::shouldReceive('channel')
            ->with('import')
            ->andReturnSelf();
        
        Log::shouldReceive('error')
            ->with('Processing error for record', \Mockery::type('array'))
            ->once();

        $this->logger->logProcessingError(1, 'Test error', ['raw' => 'data']);
    }
}
