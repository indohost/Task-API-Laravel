<?php

namespace App\Http\Resources\TaskListStorage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailTaskListStorageResources extends JsonResource
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
            'filename' => $this->filename,
            'orginal_name' => $this->orginal_name,
            'type' => $this->type,
            'path' => $this->path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'task_list' => $this->taskList,
        ];

        return $taskResource;
    }
}
