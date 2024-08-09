<?php

namespace App\Http\Controllers;

use App\Constants\TaskListStorageConstants;
use App\Http\Resources\TaskListStorage\DeleteTaskListStorageResources;
use App\Http\Resources\TaskListStorage\DetailTaskListStorageResources;
use App\Http\Resources\TaskListStorage\StoreTaskListStorageResources;
use App\Http\Resources\TaskListStorage\UpdateTaskListStorageResources;
use App\Models\TaskListStorage;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HTTPCode;

class TaskListStorageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'page' => 'integer',
            'size' => 'integer',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        try {
            $page = $request->input('page', 1);
            $size = $request->input('size', 10);

            $taskListStorage = TaskListStorage::query();

            $taskListStorage = $taskListStorage->where(function (Builder $builder) use ($request) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($startDate && $endDate) {
                    $from = Carbon::parse($startDate)->startOfDay();
                    $to = Carbon::parse($endDate)->endOfDay();
                    $builder->whereBetween('created_at', [$from, $to]);
                }
            });

            $taskListStorage = $taskListStorage->latest()
                ->paginate(
                    perPage: $size,
                    page: $page
                );

            return $this->successResponse(TaskListStorageConstants::GET_LIST, HTTPCode::HTTP_OK, $taskListStorage);
        } catch (Exception $e) {
            Log::error([
                'title' => 'index task list storage',
                'message'   => $e->getMessage(),
            ]);

            return $this->failedResponse(TaskListStorageConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,pdf|max:2048',
            'task_list_id' => 'required|uuid|exists:task_lists,id',
            'is_test' => 'boolean',
        ]);

        try {
            $taskListId = $request->task_list_id;
            $fileImage = $request->file;

            $fileOriginalName  = $fileImage->getClientOriginalName();
            $originalName = pathinfo($fileOriginalName, PATHINFO_FILENAME);
            $fileExtension = pathinfo($fileOriginalName, PATHINFO_EXTENSION);

            $generateNewFileName = 'task-list-' . date('ymd') . time() . '.' . $fileImage->extension();

            $filePath = "task_list/" . $taskListId;

            Storage::putFileAs(
                $filePath,
                $fileImage,
                $generateNewFileName
            );

            $requestTaskListStorage = [
                'filename' => $generateNewFileName,
                'orginal_name' => $originalName,
                'type' => $fileExtension,
                'path' => $filePath,
                'task_list_id' => $request->task_list_id,
                'is_test' => (bool) $request?->is_test,
            ];

            $taskListStorage = TaskListStorage::create($requestTaskListStorage);

            if ($taskListStorage) return $this->successResponse(TaskListStorageConstants::CREATE, HTTPCode::HTTP_CREATED, new StoreTaskListStorageResources($taskListStorage));
        } catch (Exception $e) {
            Log::error([
                'title' => 'store task list storage',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListStorageConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListStorageConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     * @param string $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $taskListStorage = TaskListStorage::findOrFail($id);

            return $this->successResponse(TaskListStorageConstants::GET_DETAIL, HTTPCode::HTTP_OK, new DetailTaskListStorageResources($taskListStorage));
        } catch (Exception $e) {
            Log::error([
                'title' => 'details task list storage',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListStorageConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListStorageConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'file' => 'file|mimes:jpeg,jpg,png,pdf|max:2048',
        ]);

        try {
            $fileImage = $request->file;

            $taskListStorage = TaskListStorage::findOrFail($id);

            $fileOriginalName  = $fileImage->getClientOriginalName();
            $originalName = pathinfo($fileOriginalName, PATHINFO_FILENAME);
            $fileExtension = pathinfo($fileOriginalName, PATHINFO_EXTENSION);

            $generateNewFileName = 'task-list-' . date('ymd') . time() . '.' . $fileImage->extension();

            $filePath = "task_list/" . $taskListStorage->id;

            Storage::putFileAs(
                $filePath,
                $fileImage,
                $generateNewFileName
            );

            $requestTaskListStorage = [
                'filename' => $generateNewFileName,
                'orginal_name' => $originalName,
                'type' => $fileExtension,
                'path' => $filePath,
            ];

            $pathFileOld = $taskListStorage->path;
            $fileNameOld = $taskListStorage->filename;

            Storage::delete($pathFileOld . $fileNameOld);

            $taskListStorage->update($requestTaskListStorage);

            if (!empty($taskListStorage->getChanges())) return $this->successResponse(TaskListStorageConstants::UPDATE, HTTPCode::HTTP_OK, new UpdateTaskListStorageResources($taskListStorage));

            return $this->failedResponse(TaskListStorageConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error([
                'title' => 'update task list storage',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListStorageConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListStorageConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $taskListStorage = TaskListStorage::findOrFail($id);

            if ($taskListStorage->delete()) return $this->successResponse(TaskListStorageConstants::DELETE, HTTPCode::HTTP_OK, new DeleteTaskListStorageResources($taskListStorage));

            return $this->failedResponse(TaskListStorageConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error([
                'title' => 'delete task list storage',
                'message'  => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListStorageConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListStorageConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
