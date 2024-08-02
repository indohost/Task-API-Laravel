<?php

namespace App\Http\Controllers;

use App\Constants\TaskListConstants;
use App\Enums\TaskList\PriorityTaskListEnums;
use App\Enums\TaskList\StatusTaskListEnums;
use App\Http\Resources\TaskList\DeleteTaskListResources;
use App\Http\Resources\TaskList\DetailTaskListResources;
use App\Http\Resources\TaskList\StoreTaskListResources;
use App\Http\Resources\TaskList\UpdateTaskListResources;
use App\Models\TaskList;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as HTTPCode;

class TaskListController extends Controller
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
            'due_date' => 'date',
            'priority' => ['string', Rule::in(PriorityTaskListEnums::getValues())],
            'status' => ['string', Rule::in(StatusTaskListEnums::getValues())],
        ]);

        try {
            $page = $request->input('page', 1);
            $size = $request->input('size', 10);

            $taskLists = TaskList::query();

            $taskLists = $taskLists->where(function (Builder $builder) use ($request) {
                $keyword = $request->input('keyword');
                $dueDate = $request->input('due_date');
                $priority = $request->input('priority');
                $status = $request->input('status');

                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');


                if ($keyword) {
                    $builder->orWhere('title', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%');
                }

                if ($dueDate) {
                    $builder->where('due_date', $dueDate);
                }

                if ($priority) {
                    $builder->where('priority', $priority);
                }

                if ($status) {
                    $builder->where('status', $status);
                }

                if ($startDate && $endDate) {
                    $from = Carbon::parse($startDate)->startOfDay();
                    $to = Carbon::parse($endDate)->endOfDay();
                    $builder->whereBetween('created_at', [$from, $to]);
                }
            });

            $taskLists = $taskLists->latest()
                ->paginate(
                    perPage: $size,
                    page: $page
                );

            return $this->successResponse(TaskListConstants::GET_LIST, HTTPCode::HTTP_OK, $taskLists);
        } catch (Exception $e) {
            Log::error([
                'title' => 'index task list',
                'message'   => $e->getMessage(),
            ]);

            return $this->failedResponse(TaskListConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            'due_date' => 'required|date',
            'priority' => ['required', 'string', Rule::in(PriorityTaskListEnums::getValues())],
            'status' => ['required', 'string', Rule::in(StatusTaskListEnums::getValues())],
            'task_id' => 'required|uuid|exists:tasks,id',
        ]);

        try {
            $requestTaskList = [
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'task_id' => $request->task_id,
            ];

            $taskList = TaskList::create($requestTaskList);

            if ($taskList) return $this->successResponse(TaskListConstants::CREATE, HTTPCode::HTTP_CREATED, new StoreTaskListResources($taskList));
        } catch (Exception $e) {
            Log::error([
                'title' => 'store task list',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            $taskList = TaskList::findOrFail($id);

            return $this->successResponse(TaskListConstants::GET_DETAIL, HTTPCode::HTTP_OK, new DetailTaskListResources($taskList));
        } catch (Exception $e) {
            Log::error([
                'title' => 'details task list',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            'due_date' => 'date',
            'priority' => ['string', Rule::in(PriorityTaskListEnums::getValues())],
            'status' => ['string', Rule::in(StatusTaskListEnums::getValues())],
            'task_id' => 'uuid|exists:tasks,id',
        ]);

        try {
            $requestTaskList = [
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => $request->status,
                'task_id' => $request->task_id,
            ];

            $requestTaskList = array_filter($requestTaskList, function ($value) {
                return !is_null($value) && $value !== '';
            });

            $taskList = TaskList::findOrFail($id);
            $taskList->update($requestTaskList);

            if (!empty($taskList->getChanges())) return $this->successResponse(TaskListConstants::UPDATE, HTTPCode::HTTP_OK, new UpdateTaskListResources($taskList));

            return $this->failedResponse(TaskListConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error([
                'title' => 'update task list',
                'message'   => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
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
            $taskList = TaskList::findOrFail($id);

            if ($taskList->delete()) return $this->successResponse(TaskListConstants::DELETE, HTTPCode::HTTP_OK, new DeleteTaskListResources($taskList));

            return $this->failedResponse(TaskListConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error([
                'title' => 'delete task list',
                'message'  => $e->getMessage(),
            ]);

            if ($e instanceof ModelNotFoundException) {
                return $this->failedResponse(TaskListConstants::NO_CONTENT, HTTPCode::HTTP_NO_CONTENT);
            }

            return $this->failedResponse(TaskListConstants::INTERNAL_SERVER_ERROR, HTTPCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
