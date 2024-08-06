<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Traits\MakesHttpRequests;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use MakesHttpRequests;

    private $tokenUser;
    private $user;

    private $endpointTask = '/api/task';

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and generate a token for JWT authentication
        $this->user = User::factory()->create();
        $this->tokenUser = JWTAuth::fromUser($this->user);
    }

    private function dataTask(): array
    {
        $dataTask = [
            'title' => 'Task',
            'description' => 'Task description',
            'status' => 'opened',
            'user_id' => $this->user['id'],
            'is_test' => false,
        ];

        return $dataTask;
    }

    /**
     * Helper function to set headers with JWT token
     */
    private function headers()
    {
        return ['Authorization' => 'Bearer ' . $this->tokenUser];
    }

    /**
     * @test
     * 
     * Given: Menyiapkan pengguna (User) dan data tugas yang valid.
     * When: Mengirimkan permintaan POST ke endpoint /api/tasks dengan data tersebut.
     * Then: Memastikan bahwa respons memiliki status sukses (201), tugas disimpan di database, dan respons berisi data tugas yang dibuat.

     */
    public function it_creates_a_task_with_valid_data(): void
    {
        // Given valid task data
        $dataTask = $this->dataTask();

        // When we send a POST request to create a task
        $responseTask = $this->postJson($this->endpointTask, $dataTask, $this->headers());

        // Then the response should have a success status
        $responseTask->assertStatus(201);

        // And the task should be stored in the database
        $this->assertDatabaseHas('tasks', $dataTask);

        // And the response should contain the created task
        $responseTask->assertJsonStructure([
            "status",
            "message",
            "data" => [
                "id",
                'title',
                'description',
                'status',
                'user_id',
                'created_at',
            ],
        ]);
    }

    /** 
     * @test
     *  
     * Given: Task yang ada di database.
     * When: Mengirim permintaan PUT ke /api/tasks/{id} untuk memperbarui task.
     * Then: Memeriksa bahwa respons adalah 200 dan data task diperbarui di database.
     * 
     */
    public function it_updates_a_task_with_valid_data(): void
    {
        // Given we have an existing task
        $dataTask = $this->dataTask();

        $taskExist = Task::factory()->create([
            'title' => $dataTask['title'],
            'status' => $dataTask['status'],
            'is_test' => $dataTask['is_test'],
        ]);

        // When we send a PUT request to update the task
        $dataTaskUpdate = [
            'title' => 'Updated Task Title',
            'description' => 'Updated Task Description',
            'status' => 'ongoing',
        ];

        $responseTask = $this->patchJson($this->endpointTask . "/{$taskExist->id}", $dataTaskUpdate, $this->headers());

        // Then it should return a success response 
        $responseTask->assertStatus(200);

        // And the task should be updated in the database
        $this->assertDatabaseHas('tasks', [
            'title' => $dataTaskUpdate['title'],
            'description' => $dataTaskUpdate['description'],
            'status' => $dataTaskUpdate['status'],
        ]);
    }

    /** @test 
     * 
     * Given: Menghasilkan user dan beberapa task menggunakan factory.
     * When: Mengirimkan permintaan GET ke endpoint /api/tasks dengan parameter yang valid dan header Authorization yang berisi token JWT.
     * Then: Memastikan respons memiliki status sukses (200) dan memverifikasi struktur JSON respons yang berisi data task.
     * 
     */
    public function it_searches_tasks_with_valid_parameters(): void
    {
        // Given we have a user and some tasks
        Task::factory()->count(10)->create([
            'user_id' => $this->user->id,
        ]);

        $searchParams = [
            'page' => 1,
            'size' => 5,
            'keyword' => 'task',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'opened',
        ];

        // When we search for tasks with valid parameters
        $responseTask = $this->withHeaders($this->headers())
            ->getJson($this->endpointTask, $searchParams);

        // Then the response should have a success status
        $responseTask->assertStatus(200);

        // And the response should contain tasks data
        $responseTask->assertJsonStructure([
            "status",
            "message",
            "pagination" => [
                "current_page",
                "data" => [
                    "*" => [
                        "id",
                        "title",
                        "description",
                        "status",
                        "user_id",
                        "created_at",
                        "updated_at",
                        "deleted_at",
                    ],
                ],
                "first_page_url",
                "from",
                "last_page",
                "last_page_url",
                "links" => [
                    "*" => [
                        "url",
                        "label",
                        "active",
                    ],
                ],
                "next_page_url",
                "path",
                "per_page",
                "prev_page_url",
                "to",
                "total",
            ],
        ]);
    }

    /** @test
     * 
     * Given: Menghasilkan user.
     * When: Mengirimkan permintaan GET ke endpoint /api/tasks/search dengan parameter status yang tidak valid.
     * Then: Memastikan respons memiliki status error validasi (422).
     * 
     */
    public function it_fails_search_with_invalid_status(): void
    {
        // Given we have a user and some tasks
        Task::factory()->count(10)->create([
            'user_id' => $this->user->id,
        ]);

        $searchParams = [
            'page' => 1,
            'size' => 5,
            'keyword' => 'task',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'invalid_status',
        ];

        // When we search for tasks with an invalid status
        $responseTask = $this->withHeaders($this->headers())
            ->getJson($this->endpointTask, [], 0, $searchParams);

        // Then the response should have a validation error status
        $responseTask->assertStatus(422);
    }

    /** @test
     * 
     * Given: Task yang ada di database.
     * When: Mengirim permintaan DELETE ke /api/tasks/{id}.
     * Then: Memeriksa bahwa respons adalah 200 dan task dihapus dari database.
     * 
     */
    public function it_deletes_a_task(): void
    {
        // Given we have an existing task
        $dataTask = $this->dataTask();

        $taskExist = Task::factory()->create([
            'is_test' => $dataTask['is_test'],
        ]);

        // When we send a DELETE request to delete the task
        $responseTask = $this->deleteJson($this->endpointTask . "/{$taskExist->id}", [], $this->headers());

        // Then it should return a success response
        $responseTask->assertStatus(200);

        // And the task should be deleted from the database
        $this->assertDatabaseMissing('tasks', [
            'id' => $taskExist->id,
            'deleted_at' => null,
        ]);
    }
}
