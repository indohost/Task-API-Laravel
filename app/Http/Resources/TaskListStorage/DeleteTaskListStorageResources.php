<?php

namespace App\Http\Resources\TaskListStorage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeleteTaskListStorageResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $taskResource = [
            'id' => $this->id,
            'path' => $this->path,
            'task_list_id' => $this->task_list_id,
            'deleted_at' => $this->deleted_at,
        ];

        return $taskResource;
    }
}
