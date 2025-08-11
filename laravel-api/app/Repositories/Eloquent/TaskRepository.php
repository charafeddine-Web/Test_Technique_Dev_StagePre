<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Get all tasks for a specific user
     *
     * @param int $userId
     * @return Collection
     */
    public function getTasksByUser(int $userId): Collection
    {
        return Task::where('user_id', $userId)->get();
    }

    /**
     * Find a task by ID and user ID
     *
     * @param int $id
     * @param int $userId
     * @return Task|null
     */
    public function findByIdAndUser(int $id, int $userId): ?Task
    {
        return Task::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Create a new task
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Update a task
     *
     * @param int $id
     * @param int $userId
     * @param array $data
     * @return Task|null
     */
    public function update(int $id, int $userId, array $data): ?Task
    {
        $task = $this->findByIdAndUser($id, $userId);
        
        if (!$task) {
            return null;
        }

        $task->update($data);
        return $task;
    }

    /**
     * Delete a task
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function delete(int $id, int $userId): bool
    {
        $task = $this->findByIdAndUser($id, $userId);
        
        if (!$task) {
            return false;
        }

        return $task->delete();
    }
}
