<?php

namespace App\Services\DataWriters;

use App\Contracts\DataWriterInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatabaseWriter implements DataWriterInterface
{
    private string $modelClass;
    private string $uniqueKey;
    private bool $useUpsert;
    private array $batchData = [];
    private int $batchSize;

    public function __construct(
        string $modelClass,
        string $uniqueKey = 'id',
        bool $useUpsert = true,
        int $batchSize = 1000
    ) {
        $this->modelClass = $modelClass;
        $this->uniqueKey = $uniqueKey;
        $this->useUpsert = $useUpsert;
        $this->batchSize = $batchSize;

        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} does not exist");
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("Class {$modelClass} must extend Eloquent Model");
        }
    }

    public function writeRecord(array $data): bool
    {
        try {
            $model = new $this->modelClass();
            
            if ($this->useUpsert && isset($data[$this->uniqueKey])) {
                $model->updateOrCreate(
                    [$this->uniqueKey => $data[$this->uniqueKey]],
                    $data
                );
            } else {
                $model->fill($data)->save();
            }
            
            return true;
        } catch (\Exception $e) {
            // Log to both general log and dedicated import log
            \Log::error("Failed to write record: " . $e->getMessage(), ['data' => $data]);
            \Log::channel('import')->error("Database write failed", [
                'error' => $e->getMessage(),
                'data' => $data,
                'timestamp' => now()->toISOString()
            ]);
            return false;
        }
    }

    public function writeBatch(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $written = 0;
        
        try {
            $chunks = array_chunk($records, $this->batchSize);
            
            foreach ($chunks as $chunk) {
                if ($this->useUpsert) {
                    $written += $this->performUpsert($chunk);
                } else {
                    $written += $this->performInsert($chunk);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Batch write failed: " . $e->getMessage());
            \Log::channel('import')->error("Batch write failed", [
                'error' => $e->getMessage(),
                'record_count' => count($records),
                'timestamp' => now()->toISOString()
            ]);
            throw $e;
        }

        return $written;
    }

    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollback(): void
    {
        DB::rollback();
    }

    public function finalize(): void
    {
        // Write any remaining batch data
        if (!empty($this->batchData)) {
            $this->writeBatch($this->batchData);
            $this->batchData = [];
        }
    }

    /**
     * Add record to batch for later writing
     */
    public function addToBatch(array $data): void
    {
        $this->batchData[] = $data;
        
        if (count($this->batchData) >= $this->batchSize) {
            $this->writeBatch($this->batchData);
            $this->batchData = [];
        }
    }

    private function performUpsert(array $records): int
    {
        $model = new $this->modelClass();
        $table = $model->getTable();
        
        // Laravel's upsert method (available in Laravel 8+)
        if (method_exists($model, 'upsert')) {
            return $model->upsert($records, [$this->uniqueKey]);
        }
        
        // Fallback: individual updateOrCreate calls
        $count = 0;
        foreach ($records as $record) {
            if ($this->writeRecord($record)) {
                $count++;
            }
        }
        
        return $count;
    }

    private function performInsert(array $records): int
    {
        $model = new $this->modelClass();
        
        // Add timestamps if the model uses them
        if ($model->usesTimestamps()) {
            $now = now();
            foreach ($records as &$record) {
                $record['created_at'] = $record['created_at'] ?? $now;
                $record['updated_at'] = $record['updated_at'] ?? $now;
            }
        }
        
        $model->insert($records);
        return count($records);
    }
}
