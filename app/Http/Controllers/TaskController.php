<?php

namespace App\Http\Controllers;

use App\Constants\TaskConstants;
use App\Enums\Task\StatusTaskEnums;
use App\Http\Resources\Task\DeleteTaskResources;
use App\Http\Resources\Task\DetailTaskResources;
use App\Http\Resources\Task\StoreTaskResources;
use App\Http\Resources\Task\UpdateTaskResources;
use App\Models\Task;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as HTTPCode;

class TaskController extends Controller
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
            'keyword' => 'string',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'status' => ['string', Rule::in(StatusTaskEnums::getValues())],
        ]);

        try {
            $page = $request->input('page', 1);
            $size = $request->input('size', 10);

            $tasks = Task::query();

            $tasks = $tasks->where(function (Builder $builder) use ($request) {
                $keyword = $request->input('keyword');
                $status = $request->input('status');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if ($status) {
                    $builder->where('status', $status);
                }

                if ($keyword) {
                    $builder->orWhere('title', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                }

                if ($startDate && $endDate) {
                    $from = Carbon::parse($startDate)->startOfDay();
                    $to = Carbon::parse($endDate)->endOfDay();
                    $builder->whereBetween('created_at', [$from, $to]);
                }
            });

            $tasks = $tasks->latest()
                ->paginate(
                    perPage: $size,
                    page: $page
                );

            return $this->successResponse(TaskConstants::GET_LIST, HTTPCode::HTTP_OK, $tasks);
        } catch (Exception $e) {
            Log::error([
                'title' => 'index task',
                'message'   => $e->getMessage(),
            ]);

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => ['required', 'string', Rule::in(StatusTaskEnums::getValues())],
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        try {
            $requestTask = [
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'user_id' => $request->user_id,
            ];

            $task = Task::create($requestTask);

            if ($task) return $this->successResponse(TaskConstants::CREATE, HTTPCode::HTTP_CREATED, new StoreTaskResources($task));
        } catch (Exception $e) {
            Log::error([
                'title' => 'store task',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            $task = Task::findOrFail($id);

            return $this->successResponse(TaskConstants::GET_DETAIL, HTTPCode::HTTP_OK, new DetailTaskResources($task));
        } catch (Exception $e) {
            Log::error([
                'title' => 'details task',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            'title' => 'string',
            'description' => 'string',
            'status' => ['required', 'string', Rule::in(StatusTaskEnums::getValues())],
            'user_id' => 'uuid|exists:users,id',
        ]);

        try {
            $requestTask = [
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'user_id' => $request->user_id,
            ];

            $requestTask = array_filter($requestTask, function ($value) {
                return !is_null($value) && $value !== '';
            });

            $task = Task::findOrFail($id);
            $task->update($requestTask);

            if (!empty($task->getChanges())) return $this->successResponse(TaskConstants::UPDATE, HTTPCode::HTTP_OK, new UpdateTaskResources($task));

            return $this->failedResponse(TaskConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error([
                'title' => 'update task',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            $task = Task::findOrFail($id);

            if ($task->delete()) return $this->successResponse(TaskConstants::DELETE, HTTPCode::HTTP_OK, new DeleteTaskResources($task));

            return $this->failedResponse(TaskConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error([
                'title' => 'delete task',
                'message'  => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
