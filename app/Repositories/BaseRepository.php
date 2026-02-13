<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all(array $relations = []): Collection
    {
        return $this->model->with($relations)->get();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage);
    }

    /**
     * Find by id
     */
    public function findById(int $id, array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find by id or fail
     */
    public function findOrFail(int $id, array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    /**
     * Delete record
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Find by condition
     */
    public function findByCondition(array $conditions, array $relations = []): Collection
    {
        $query = $this->model->with($relations);

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get();
    }
}
