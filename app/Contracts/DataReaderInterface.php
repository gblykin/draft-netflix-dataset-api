<?php

namespace App\Contracts;

interface DataReaderInterface
{
    /**
     * Read data from the source
     *
     * @return \Generator
     */
    public function read(): \Generator;

    /**
     * Get the column headers/names
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Get total number of records (if available)
     *
     * @return int|null
     */
    public function getRecordCount(): ?int;

    /**
     * Close the data source
     *
     * @return void
     */
    public function close(): void;
}

