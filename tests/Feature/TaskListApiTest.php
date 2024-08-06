<?php

namespace Tests\Feature;

use App\Constants\TaskListConstants;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Traits\MakesHttpRequests;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class TaskListApiTest extends TestCase
{
    use MakesHttpRequests;

    private $tokenUser;
    private $user;
    private $task;

    private $endpointTaskList = '/api/task-list';

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and generate a token for JWT authentication
        $this->user = User::factory()->create();
        $this->tokenUser = JWTAuth::fromUser($this->user);

        // Create a task 
        $this->task = Task::factory()->create();
    }

    private function dataTaskList(): array
    {
        $dataTaskList = [
            'title' => 'Task',
            'description' => 'Task description',
            'due_date' => '2024-08-06',
            'priority' => 'low',
            'status' => 'opened',
            'task_id' => $this->task['id'],
            'is_test' => true,
        ];

        return $dataTaskList;
    }

    /**
     * Helper function to set headers with JWT token
     */
    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenUser];
    }

    /**
     * @test
     * 
     * Given: Menyiapkan data yang valid.
     * When: Mengirimkan permintaan POST ke endpoint /api/task-list dengan data tersebut.
     * Then: Memastikan bahwa respons memiliki status sukses (201), tugas disimpan di database, dan respons berisi data tugas yang dibuat.

     */
    public function it_creates_a_task_list_with_valid_data(): void
    {
        // Given valid task list data
        $dataTaskList = $this->dataTaskList();

        // When we send a POST request to create a task
        $responseTaskList = $this->postJson($this->endpointTaskList, $dataTaskList, $this->headers());

        // Then the response should have a success status and message
        $responseTaskList->assertStatus(201)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListConstants::CREATE,
            ]);

        // And the task should be stored in the database
        $this->assertDatabaseHas('task_lists', $dataTaskList);

        // And the response should contain the created task list
        $responseTaskList->assertJsonStructure([
            "status",
            "message",
            "data" => [
                "id",
                "title",
                "description",
                "due_date",
                "priority",
                "status",
                "task_id",
                "created_at",
            ],
        ]);
    }

    /** 
     * @test
     *  
     * Given: Task List yang ada di database.
     * When: Mengirim permintaan PUT ke /api/task-list/{id} untuk memperbarui task list.
     * Then: Memeriksa bahwa respons adalah 200 dan data task list diperbarui di database.
     * 
     */
    public function it_updates_a_task_list_with_valid_data(): void
    {
        // Given we have an existing task list
        $dataTaskList = $this->dataTaskList();

        $taskListExist = TaskList::factory()->create([
            'title' => $dataTaskList['title'],
            'due_date' => $dataTaskList['due_date'],
            'priority' => $dataTaskList['priority'],
            'status' => $dataTaskList['status'],
            'is_test' => $dataTaskList['is_test'],
        ]);

        // When we send a PUT request to update the task
        $dataTaskListUpdate = [
            'title' => 'Updated Task List Title',
            'description' => 'Updated Task List Description',
            'priority' => 'medium',
            'status' => 'ongoing',
        ];

        $responseTaskList = $this->patchJson($this->endpointTaskList . "/{$taskListExist->id}", $dataTaskListUpdate, $this->headers());

        // Then it should return a success response 
        $responseTaskList->assertStatus(200)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListConstants::UPDATE,
            ]);

        // And the task list should be updated in the database
        $this->assertDatabaseHas('task_lists', [
            'title' => $dataTaskListUpdate['title'],
            'description' => $dataTaskListUpdate['description'],
            'priority' => $dataTaskListUpdate['priority'],
            'status' => $dataTaskListUpdate['status'],
        ]);
    }

    /** @test 
     * 
     * Given: Menghasilkan beberapa Task List menggunakan factory.
     * When: Mengirimkan permintaan GET ke endpoint /api/task-list dengan parameter yang valid dan header Authorization yang berisi token JWT.
     * Then: Memastikan respons memiliki status sukses (200) dan memverifikasi struktur JSON respons yang berisi data task.
     * 
     */
    public function it_searches_task_list_with_valid_parameters(): void
    {
        // Given we have some task list
        TaskList::factory()->count(10)->create([
            'task_id' => $this->task->id,
        ]);

        $searchParams = [
            'page' => 1,
            'size' => 5,
            'keyword' => 'task',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'priority' => 'low',
            'status' => 'opened',
        ];

        // When we search for tasks with valid parameters
        $responseTaskList = $this->withHeaders($this->headers())
            ->getJson($this->endpointTaskList, $searchParams);

        // Then the response should have a success status
        $responseTaskList->assertStatus(200)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListConstants::GET_LIST,
            ]);

        // And the response should contain tasks data
        $responseTaskList->assertJsonStructure([
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
                        "task_id",
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
     * Given: Menghasilkan beberapa Task List menggunakan factory.
     * When: Mengirimkan permintaan GET ke endpoint /api/task-list dengan parameter status yang tidak valid.
     * Then: Memastikan respons memiliki status error validasi (422).
     * 
     */
    public function it_fails_search_with_invalid_status(): void
    {
        // Given we have some task list
        TaskList::factory()->count(10)->create([
            'task_id' => $this->task->id,
        ]);

        $searchParams = [
            'page' => 1,
            'size' => 5,
            'keyword' => 'task',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'priority' => 'invalid_priority',
            'status' => 'invalid_status',
        ];

        // When we search for task list with an invalid status
        $responseTaskList = $this->withHeaders($this->headers())
            ->getJson($this->endpointTaskList, [], 0, $searchParams);

        // Then the response should have a validation error status
        $responseTaskList->assertStatus(422);
    }

    /** @test
     * 
     * Given: Task List yang ada di database.
     * When: Mengirim permintaan DELETE ke /api/task-list/{id}.
     * Then: Memeriksa bahwa respons adalah 200 dan task dihapus dari database.
     * 
     */
    public function it_deletes_a_task_list(): void
    {
        // Given we have an existing task
        $dataTaskList = $this->dataTaskList();

        $taskListExist = TaskList::factory()->create([
            'is_test' => $dataTaskList['is_test'],
        ]);

        // When we send a DELETE request to delete the task
        $responseTaskList = $this->deleteJson($this->endpointTaskList . "/{$taskListExist->id}", [], $this->headers());

        // Then it should return a success response
        $responseTaskList->assertStatus(200)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListConstants::DELETE,
            ]);

        // And the task should be deleted from the database
        $this->assertDatabaseMissing('task_lists', [
            'id' => $taskListExist->id,
            'deleted_at' => null,
        ]);
    }
}
