<?php

namespace App\Http\Resources\TaskListStorage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateTaskListStorageResources extends JsonResource
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
            'task_list_id' => $this->task_list_id,
            'updated_at' => $this->updated_at,
        ];

        return $taskResource;
    }
}
