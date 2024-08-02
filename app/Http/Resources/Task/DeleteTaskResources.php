<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeleteTaskResources extends JsonResource
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
            'status' => $this->status,
            'deleted_at' => $this->deleted_at,
        ];

        return $taskResource;
    }
}
