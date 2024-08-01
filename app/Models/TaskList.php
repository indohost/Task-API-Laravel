<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskList extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    public function Task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }
}
