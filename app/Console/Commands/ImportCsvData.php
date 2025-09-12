<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DataImportFactory;

class ImportCsvData extends Command
{
    protected $signature = 'import:csv {type} {file} {--dry-run : Preview import without writing to database}';
    protected $description = 'Import CSV data into the database using flexible SOLID-based architecture';

    public function handle()
    {
        $type = $this->argument('type');
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Starting import of {$type} data from {$file}");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No data will be written to the database");
        }

        try {
            $importer = DataImportFactory::createCsvImporter($type, $file);
            
            // Show CSV headers for confirmation
            $this->displayHeaders($importer);
            
            if (!$this->confirm('Do you want to proceed with the import?')) {
                $this->info('Import cancelled by user.');
                return 0;
            }

            if ($dryRun) {
                $this->performDryRun($importer);
            } else {
                $result = $importer->import();
                $this->displayResults($result);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
    }

    private function displayHeaders($importer): void
    {
        $reader = $importer->getReader();
        $headers = $reader->getHeaders();
        
        $this->info("CSV Headers detected:");
        $this->table(['Column Index', 'Header Name'], 
            array_map(fn($index, $header) => [$index, $header], 
            array_keys($headers), $headers)
        );
    }

    private function performDryRun($importer): void
    {
        $this->info("Performing dry run - analyzing first 10 records...");
        
        $reader = $importer->getReader();
        $transformer = $importer->getTransformer();
        
        $headers = $reader->getHeaders();
        $count = 0;
        
        foreach ($reader->read() as $rawData) {
            if ($count >= 10) break;
            
            $this->info("Record " . ($count + 1) . ":");
            $transformedData = $transformer->transform($rawData, $headers);
            
            if ($transformer->validate($transformedData)) {
                $this->line("✅ Valid: " . json_encode($transformedData, JSON_PRETTY_PRINT));
            } else {
                $this->error("❌ Invalid: " . implode(', ', $transformer->getValidationErrors()));
                $this->line("Raw data: " . json_encode($rawData, JSON_PRETTY_PRINT));
            }
            
            $count++;
        }
        
        $total = $reader->getRecordCount();
        $this->info("Dry run completed. Total records in file: " . ($total ?? 'Unknown'));
    }

    private function displayResults(array $result): void
    {
        $stats = $result['stats'];
        
        $this->info("Import Results:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Status', $stats['status']],
                ['Total Processed', $stats['total_processed']],
                ['Successful', $stats['successful']],
                ['Inserted', $stats['inserted'] ?? 0],
                ['Updated', $stats['updated'] ?? 0],
                ['Failed', $stats['failed']],
                ['Duration', $stats['duration_seconds'] . ' seconds'],
            ]
        );
        
        if ($stats['failed'] > 0) {
            $this->warn("Errors encountered during import:");
            
            // Show recent errors (up to 10) with memory-efficient approach
            $errorTable = array_slice($result['errors'], 0, 10);
            
            // Add note about error limit if there are more errors than shown
            if ($stats['failed'] > 10) {
                $this->info("Showing last 10 errors out of {$stats['failed']} total errors.");
                $this->info("Note: Only the most recent 50 errors are kept in memory to prevent memory issues.");
            }
            // Handle both string errors and array errors for backward compatibility
            $tableData = array_map(function($error) {
                if (is_string($error)) {
                    return ['N/A', 'Error', $error];
                } else {
                    return [
                        $error['row'] ?? 'N/A',
                        $error['type'] ?? 'Error',
                        is_array($error['details']) ? implode('; ', $error['details']) : ($error['details'] ?? 'Unknown error')
                    ];
                }
            }, $errorTable);
            
            $this->table(['Row', 'Type', 'Details'], $tableData);
            
            if (count($result['errors']) > 10) {
                $this->warn("... and " . (count($result['errors']) - 10) . " more errors");
            }
        }
        
        if ($result['success']) {
            $this->info("✅ Import completed successfully!");
        } else {
            $this->error("❌ Import completed with errors.");
        }
    }
}
