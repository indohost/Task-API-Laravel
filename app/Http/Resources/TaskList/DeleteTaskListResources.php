<?php

namespace App\Http\Resources\TaskList;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeleteTaskListResources extends JsonResource
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
            'title' => $this->title,
            'due_date' => $this->due_date,
            'priority' => $this->priority,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at,
        ];

        return $taskResource;
    }
}
