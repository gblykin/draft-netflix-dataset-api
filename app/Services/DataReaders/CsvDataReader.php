<?php

namespace App\Services\DataReaders;

use App\Contracts\DataReaderInterface;

class CsvDataReader implements DataReaderInterface
{
    private $handle;
    private array $headers = [];
    private string $filePath;
    private string $delimiter;
    private ?int $recordCount = null;

    public function __construct(string $filePath, string $delimiter = ',')
    {
        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->openFile();
        $this->readHeaders();
    }

    public function read(): \Generator
    {
        // Reset file pointer to after headers
        rewind($this->handle);
        fgetcsv($this->handle, 0, $this->delimiter); // Skip headers

        while (($data = fgetcsv($this->handle, 0, $this->delimiter)) !== false) {
            if (empty(array_filter($data))) {
                continue; // Skip empty rows
            }

            // Ensure data array has same length as headers
            $data = array_pad($data, count($this->headers), '');
            
            yield array_combine($this->headers, $data);
        }
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRecordCount(): ?int
    {
        if ($this->recordCount === null) {
            $this->calculateRecordCount();
        }
        
        return $this->recordCount;
    }

    public function close(): void
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    private function openFile(): void
    {
        if (!file_exists($this->filePath)) {
            throw new \InvalidArgumentException("File not found: {$this->filePath}");
        }

        $this->handle = fopen($this->filePath, 'r');
        
        if (!$this->handle) {
            throw new \RuntimeException("Could not open file: {$this->filePath}");
        }
    }

    private function readHeaders(): void
    {
        rewind($this->handle);
        $headers = fgetcsv($this->handle, 0, $this->delimiter);
        
        if (!$headers) {
            throw new \RuntimeException("Could not read headers from CSV file");
        }

        // Clean and normalize headers
        $this->headers = array_map(function ($header) {
            return trim(strtolower(str_replace([' ', '-'], '_', $header)));
        }, $headers);
    }

    private function calculateRecordCount(): void
    {
        $currentPosition = ftell($this->handle);
        
        rewind($this->handle);
        fgetcsv($this->handle, 0, $this->delimiter); // Skip headers
        
        $count = 0;
        while (fgetcsv($this->handle, 0, $this->delimiter) !== false) {
            $count++;
        }
        
        fseek($this->handle, $currentPosition);
        $this->recordCount = $count;
    }

    public function __destruct()
    {
        $this->close();
    }
}

