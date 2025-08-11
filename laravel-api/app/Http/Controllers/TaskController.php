<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $taskService;
    protected $userService;

    public function __construct(TaskService $taskService, UserService $userService)
    {
        $this->taskService = $taskService;
        $this->userService = $userService;
    }

    /**
     * Get all tasks for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->userService->getCurrentUser();
        $tasks = $this->taskService->getTasksByUser($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $tasks
            ]
        ], 200);
    }

    /**
     * Get a specific task
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->getCurrentUser();
        $task = $this->taskService->getTaskByIdAndUser($id, $user->id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'task' => $task
            ]
        ], 200);
    }

    /**
     * Create a new task
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,done',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $this->userService->getCurrentUser();
        $task = $this->taskService->createTask($request->all(), $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'task' => $task
            ]
        ], 201);
    }

    /**
     * Update a task
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,in_progress,done',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $this->userService->getCurrentUser();
        $task = $this->taskService->updateTask($id, $request->all(), $user->id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'task' => $task
            ]
        ], 200);
    }

    /**
     * Delete a task
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->getCurrentUser();
        $deleted = $this->taskService->deleteTask($id, $user->id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ], 200);
    }
}
