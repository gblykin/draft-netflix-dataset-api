<?php

namespace App\Contracts;

interface DataWriterInterface
{
    /**
     * Write a single record
     *
     * @param array $data
     * @return bool
     */
    public function writeRecord(array $data): bool;

    /**
     * Write multiple records in batch
     *
     * @param array $records
     * @return int Number of records written
     */
    public function writeBatch(array $records): int;

    /**
     * Begin a transaction (if supported)
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit the transaction
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback the transaction
     *
     * @return void
     */
    public function rollback(): void;

    /**
     * Finalize the writing process
     *
     * @return void
     */
    public function finalize(): void;
}

