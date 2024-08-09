<?php

namespace Tests\Feature;

use App\Constants\TaskListStorageConstants;
use App\Models\TaskList;
use App\Models\TaskListStorage;
use App\Models\User;
use App\Traits\MakesHttpRequests;
use Illuminate\Http\UploadedFile;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class TaskListStorageApiTest extends TestCase
{
    use MakesHttpRequests;

    private $tokenUser;
    private $user;
    private $taskList;

    private $endpointTaskListStorage = '/api/task-list-storage';

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and generate a token for JWT authentication
        $this->user = User::factory()->create();
        $this->tokenUser = JWTAuth::fromUser($this->user);

        // Create a task list
        $this->taskList = TaskList::factory()->create();
    }

    private function dataTaskListStorage(): array
    {
        $fileTest = UploadedFile::fake()->create('document.png', 100);

        $dataTaskListStorage = [
            'file' => $fileTest,
            'orginal_name' => $this->infoUploadedFile($fileTest)['orginal_name'],
            'type' => $this->infoUploadedFile($fileTest)['type'],
            'task_list_id' => $this->taskList['id'],
            'is_test' => true,
        ];

        return $dataTaskListStorage;
    }


    /**
     * Helper function to set headers with JWT token
     */
    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenUser];
    }

    /**
     * Helper function to information of uploaded file
     */
    private function infoUploadedFile(UploadedFile $file): array
    {
        $fileOriginalName  = $file->getClientOriginalName();
        $originalName = pathinfo($fileOriginalName, PATHINFO_FILENAME);
        $fileExtension = pathinfo($fileOriginalName, PATHINFO_EXTENSION);

        return [
            'orginal_name' => $originalName,
            'type' => $fileExtension,
        ];
    }

    /**
     * @test
     * 
     * Given: Menyiapkan data yang valid.
     * When: Mengirimkan permintaan POST ke endpoint /api/task-list-storage dengan data tersebut.
     * Then: Memastikan bahwa respons memiliki status sukses (201), tugas disimpan di database, dan respons berisi data tugas yang dibuat.

     */
    public function it_creates_a_task_list_storage_with_valid_data(): void
    {
        // Given valid task list data
        $dataTaskListStorage = $this->dataTaskListStorage();

        // When we send a POST request to create a task list storage
        $responseTaskListStorage = $this->postJson($this->endpointTaskListStorage, $dataTaskListStorage, $this->headers());

        // Then the response should have a success status and message
        $responseTaskListStorage->assertStatus(201)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListStorageConstants::CREATE,
            ]);

        // And the task should be stored in the database
        $existDataTaskListStorage = [
            'orginal_name' => $dataTaskListStorage['orginal_name'],
            'type' => $dataTaskListStorage['type'],
            'task_list_id' => $this->taskList['id'],
            'is_test' => true,
        ];

        $this->assertDatabaseHas('task_list_storages', $existDataTaskListStorage);

        // And the response should contain the created task list
        $responseTaskListStorage->assertJsonStructure([
            "status",
            "message",
            "data" => [
                "id",
                "filename",
                "orginal_name",
                "type",
                "path",
                "task_list_id",
                "created_at",
            ],
        ]);
    }

    /** 
     * @test
     *  
     * Given: Task List yang ada di database.
     * When: Mengirim permintaan PUT ke /api/task-list-storage/{id} untuk memperbarui task list.
     * Then: Memeriksa bahwa respons adalah 200 dan data task list storage diperbarui di database.
     * 
     */
    public function it_updates_a_task_list_storage_with_valid_data(): void
    {
        // Given we have an existing task list storage
        $dataTaskListStorage = $this->dataTaskListStorage();

        $taskListStorageExist = TaskListStorage::factory()->create([
            'orginal_name' => $dataTaskListStorage['orginal_name'],
            'type' => $dataTaskListStorage['type'],
            'is_test' => true,
        ]);

        // When we send a PUT request to update the task
        $fileUpdate = UploadedFile::fake()->create('document.png', 100);

        $dataTaskListStorageUpdate = [
            'file' => $fileUpdate,
            'task_list_id' => $this->taskList['id'],
        ];

        $responseTaskListStorage = $this->patchJson($this->endpointTaskListStorage . "/{$taskListStorageExist->id}", $dataTaskListStorageUpdate, $this->headers());

        // Then it should return a success response 
        $responseTaskListStorage->assertStatus(200)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListStorageConstants::UPDATE,
            ]);

        // And the task list should be updated in the database
        $this->assertDatabaseHas('task_list_storages', [
            'id' => $taskListStorageExist->id,
            'orginal_name' => $this->infoUploadedFile($fileUpdate)['orginal_name'],
            'type' => $this->infoUploadedFile($fileUpdate)['type'],
            'is_test' => true,
        ]);
    }

    /** @test 
     * 
     * Given: Menghasilkan beberapa Task List Storage menggunakan factory.
     * When: Mengirimkan permintaan GET ke endpoint /api/task-list-storage dengan parameter yang valid dan header Authorization yang berisi token JWT.
     * Then: Memastikan respons memiliki status sukses (200) dan memverifikasi struktur JSON respons yang berisi data task.
     * 
     */
    public function it_searches_task_list_storage_with_valid_parameters(): void
    {
        // Given we have some task list storage
        TaskListStorage::factory()->count(10)->create([
            'task_list_id' => $this->taskList->id,
        ]);

        $searchParams = [
            'page' => 1,
            'size' => 5,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        // When we search for tasks with valid parameters
        $responseTaskListStorage = $this->withHeaders($this->headers())
            ->getJson($this->endpointTaskListStorage, $searchParams);

        // Then the response should have a success status
        $responseTaskListStorage->assertStatus(200)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListStorageConstants::GET_LIST,
            ]);

        // And the response should contain tasks data
        $responseTaskListStorage->assertJsonStructure([
            "status",
            "message",
            "pagination" => [
                "current_page",
                "data" => [
                    "*" => [
                        "id",
                        "filename",
                        "orginal_name",
                        "type",
                        "path",
                        "task_list_id",
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
     * When: Mengirimkan permintaan GET ke endpoint /api/task-list-storage dengan parameter status yang tidak valid.
     * Then: Memastikan respons memiliki status error validasi (422).
     * 
     */
    public function it_fails_search_with_invalid_date_range(): void
    {
        // Given we have some task list storage
        TaskListStorage::factory()->count(10)->create([
            'task_list_id' => $this->taskList->id,
        ]);

        $searchParams = [
            'page' => 1,
            'size' => 5,
            'start_date' => '2024-21-01',
            'end_date' => '2024-22-31',
        ];

        // When we search for task list storage with an invalid status
        $responseTaskListStorage = $this->withHeaders($this->headers())
            ->getJson($this->endpointTaskListStorage, [], 0, $searchParams);

        // Then the response should have a validation error status
        $responseTaskListStorage->assertStatus(422);
    }

    /** @test
     * 
     * Given: Task List yang ada di database.
     * When: Mengirim permintaan DELETE ke /api/task-list-storage/{id}.
     * Then: Memeriksa bahwa respons adalah 200 dan task list storage dihapus dari database.
     * 
     */
    public function it_deletes_a_task_list_storage(): void
    {
        // Given we have an existing task list storage
        $dataTaskListStorage = $this->dataTaskListStorage();

        $taskListStorageExist = TaskListStorage::factory()->create([
            'orginal_name' => $dataTaskListStorage['orginal_name'],
            'type' => $dataTaskListStorage['type'],
            'is_test' => true,
        ]);

        // When we send a DELETE request to delete the task list storage
        $responseTaskListStorage = $this->deleteJson($this->endpointTaskListStorage . "/{$taskListStorageExist->id}", [], $this->headers());

        // Then it should return a success response
        $responseTaskListStorage->assertStatus(200)
            ->assertJson([
                "status" => "OK",
                "message" => TaskListStorageConstants::DELETE,
            ]);

        // And the task should be deleted from the database
        $this->assertDatabaseMissing('task_list_storages', [
            'id' => $taskListStorageExist->id,
            'deleted_at' => null,
        ]);
    }
}
