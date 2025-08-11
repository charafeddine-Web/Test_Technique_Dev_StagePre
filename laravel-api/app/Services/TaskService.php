<?php

namespace App\Services;

use App\Events\TaskCreated;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCreatedNotification;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks for a user
     *
     * @param int $userId
     * @return Collection
     */
    public function getTasksByUser(int $userId): Collection
    {
        return $this->taskRepository->getTasksByUser($userId);
    }

    /**
     * Get a specific task for a user
     *
     * @param int $id
     * @param int $userId
     * @return \App\Models\Task|null
     */
    public function getTaskByIdAndUser(int $id, int $userId): ?\App\Models\Task
    {
        return $this->taskRepository->findByIdAndUser($id, $userId);
    }

    /**
     * Create a new task
     *
     * @param array $data
     * @param int $userId
     * @return \App\Models\Task
     */
    public function createTask(array $data, int $userId): \App\Models\Task
    {
        $data['user_id'] = $userId;
        $task = $this->taskRepository->create($data);

        // Get the user
        $user = User::find($userId);

        // Dispatch the event for real-time notification
        event(new TaskCreated($task, $user));

        // Send notification
        $user->notify(new TaskCreatedNotification($task));

        return $task;
    }

    /**
     * Update a task
     *
     * @param int $id
     * @param array $data
     * @param int $userId
     * @return \App\Models\Task|null
     */
    public function updateTask(int $id, array $data, int $userId): ?\App\Models\Task
    {
        return $this->taskRepository->update($id, $userId, $data);
    }

    /**
     * Delete a task
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function deleteTask(int $id, int $userId): bool
    {
        return $this->taskRepository->delete($id, $userId);
    }
}
