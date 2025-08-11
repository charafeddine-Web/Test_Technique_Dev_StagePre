<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * Get all tasks for a specific user
     *
     * @param int $userId
     * @return Collection
     */
    public function getTasksByUser(int $userId): Collection;

    /**
     * Find a task by ID and user ID
     *
     * @param int $id
     * @param int $userId
     * @return Task|null
     */
    public function findByIdAndUser(int $id, int $userId): ?Task;

    /**
     * Create a new task
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task;

    /**
     * Update a task
     *
     * @param int $id
     * @param int $userId
     * @param array $data
     * @return Task|null
     */
    public function update(int $id, int $userId, array $data): ?Task;

    /**
     * Delete a task
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function delete(int $id, int $userId): bool;
}
