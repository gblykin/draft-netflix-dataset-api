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
    private string $lastOperation = 'unknown';
    private string $lastError = '';

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
                // Check if record exists by unique key
                $existingRecord = $model->where($this->uniqueKey, $data[$this->uniqueKey])->first();
                
                if ($existingRecord) {
                    // Update existing record
                    $updated = $existingRecord->update($data);
                    if ($updated === 0) {
                        throw new \Exception("Failed to update existing record - no rows affected");
                    }
                    // Mark as update for tracking
                    $this->lastOperation = 'update';
                } else {
                    // Create new record
                    $saved = $model->fill($data)->save();
                    if (!$saved) {
                        throw new \Exception("Failed to create new record");
                    }
                    // Mark as insert for tracking
                    $this->lastOperation = 'insert';
                }
            } else {
                $model->fill($data)->save();
                $this->lastOperation = 'insert';
            }
            
            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle all database constraint violations and query errors
            $errorMessage = $this->getConstraintViolationMessage($e, $data);
            $this->lastError = $errorMessage;
            \Log::channel('import')->error("Database error", [
                'error' => $errorMessage,
                'data' => $data,
                'timestamp' => now()->toISOString()
            ]);
            return false;
        } catch (\Exception $e) {
            // Log to both general log and dedicated import log
            $this->lastError = $e->getMessage();
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

    /**
     * Get a user-friendly message for constraint violations
     */
    private function getConstraintViolationMessage(\Illuminate\Database\QueryException $e, array $data): string
    {
        $message = $e->getMessage();
        
        // Check for specific constraint violations
        if (strpos($message, 'Duplicate entry') !== false) {
            if (strpos($message, 'email') !== false) {
                return "Duplicate email address: " . ($data['email'] ?? 'unknown');
            } elseif (strpos($message, 'external_user_id') !== false) {
                return "Duplicate external user ID: " . ($data['external_user_id'] ?? 'unknown');
            } elseif (strpos($message, 'external_movie_id') !== false) {
                return "Duplicate external movie ID: " . ($data['external_movie_id'] ?? 'unknown');
            } elseif (strpos($message, 'external_review_id') !== false) {
                return "Duplicate external review ID: " . ($data['external_review_id'] ?? 'unknown');
            }
        }
        
        return "Database constraint violation: " . $message;
    }

    /**
     * Get the last operation performed (insert, update, or unknown)
     */
    public function getLastOperation(): string
    {
        return $this->lastOperation;
    }

    /**
     * Reset the last operation tracking
     */
    public function resetLastOperation(): void
    {
        $this->lastOperation = 'unknown';
    }

    /**
     * Get the last error message
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Reset the last error message
     */
    public function resetLastError(): void
    {
        $this->lastError = '';
    }
}
