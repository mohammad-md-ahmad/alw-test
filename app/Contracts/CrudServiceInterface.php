<?php

namespace App\Contracts;

use Spatie\LaravelData\Data;

interface CrudServiceInterface
{
    /**
     * @param  Data  $data
     * @return array
     */
    public function query(Data $data): array;

    /**
     * @param Data $data
     * @return array
     */
    public function create(Data $data): array;

    /**
     * @param Data $data
     * @return array
     */
    public function update(Data $data): array;

    /**
     * @param  Data  $data
     * @return bool
     */
    public function delete(Data $data): bool;
}
