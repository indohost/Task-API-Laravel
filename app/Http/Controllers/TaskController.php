<?php

namespace App\Http\Controllers;

use App\Constants\TaskConstants;
use App\Models\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HTTPCode;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Task::paginate(5);

            return $this->successResponse(TaskConstants::GET_LIST, HTTPCode::HTTP_OK, $data);
        } catch (Exception $e) {
            Log::error([
                'title' => 'lists task',
                'message'   => $e->getMessage(),
            ]);

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|string',
            'user_id' => 'required|uuid',
        ]);

        try {
            $requestTask = [
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'user_id' => $request->user_id,
            ];

            $task = Task::create($requestTask);

            if ($task) return $this->successResponse(TaskConstants::CREATE, HTTPCode::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error([
                'title' => 'store task',
                'message'   => $e->getMessage(),
            ]);

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $task = Task::findOrFail($id);

            return $this->successResponse(TaskConstants::GET_DETAIL, HTTPCode::HTTP_OK, $task);
        } catch (Exception $e) {
            Log::error([
                'title' => 'details task',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return response()->noContent();
            }

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'string',
            'description' => 'string',
            'status' => 'string',
            'user_id' => 'uuid',
        ]);

        try {
            $requestTask = [
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'user_id' => $request->user_id,
            ];

            $task = Task::findOrFail($id);
            $task->update($requestTask);

            if (!empty($task->getChanges())) return $this->successResponse(TaskConstants::UPDATE, HTTPCode::HTTP_OK);

            return response()->noContent();
        } catch (Exception $e) {
            Log::error([
                'title' => 'updated task',
                'message'   => $e->getMessage(),
            ]);

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $task = Task::findOrFail($id);

            if ($task->delete()) return $this->successResponse(TaskConstants::DELETE, HTTPCode::HTTP_OK);

            return response()->noContent();
        } catch (Exception $e) {
            Log::error([
                'title' => 'delete task',
                'message'  => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return response()->noContent();
            }

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
