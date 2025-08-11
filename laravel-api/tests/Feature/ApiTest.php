<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'address' => '123 Main St',
            'image' => 'https://example.com/image.jpg',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'full_name',
                            'email',
                            'phone_number',
                            'address',
                            'image',
                            'created_at',
                            'updated_at'
                        ],
                        'token'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'full_name' => 'John Doe'
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token'
                    ]
                ]);
    }

    public function test_user_can_get_profile()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user'
                    ]
                ]);
    }

    public function test_user_can_create_task()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'task' => [
                            'id',
                            'user_id',
                            'title',
                            'description',
                            'status',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id
        ]);
    }

    public function test_user_can_get_tasks()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Task 1'
        ]);

        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Task 2'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'tasks'
                    ]
                ]);

        $this->assertCount(2, $response->json('data.tasks'));
    }

    public function test_user_can_get_specific_task()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Task'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'task'
                    ]
                ]);

        $this->assertEquals('Test Task', $response->json('data.task.title'));
    }

    public function test_user_can_update_task()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title'
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'done'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'task'
                    ]
                ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'done'
        ]);
    }

    public function test_user_can_delete_task()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $task = Task::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id
        ]);
    }

    public function test_user_cannot_access_other_user_task()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $token1 = auth()->login($user1);

        $task = Task::factory()->create([
            'user_id' => $user2->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1
        ])->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_tasks()
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }
}
