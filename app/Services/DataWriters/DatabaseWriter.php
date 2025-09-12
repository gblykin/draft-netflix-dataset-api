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
    private string $lastOperation = 'unknown';
    private string $lastError = '';

    public function __construct(
        string $modelClass,
        string $uniqueKey = 'id',
        bool $useUpsert = true
    ) {
        $this->modelClass = $modelClass;
        $this->uniqueKey = $uniqueKey;
        $this->useUpsert = $useUpsert;

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
                // Check if record exists by external_user_id
                $existingRecord = $model->where($this->uniqueKey, $data[$this->uniqueKey])->first();
                
                if ($existingRecord) {
                    // Update existing record (no email conflict check needed)
                    $existingRecord->update($data);
                    $this->lastOperation = 'update';
                } else {
                    // Check for email conflict before creating
                    if (isset($data['email'])) {
                        $emailConflict = $model->where('email', $data['email'])->first();
                        if ($emailConflict) {
                            throw new \Exception("Email conflict: {$data['email']} already exists with different external_user_id");
                        }
                    }
                    
                    // Create new record
                    $model->fill($data)->save();
                    $this->lastOperation = 'insert';
                }
            } else {
                // Simple insert
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
        // No batch processing, nothing to finalize
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
