<?php

namespace Database\Factories;

use App\Enums\TaskList\PriorityTaskListEnums;
use App\Enums\TaskList\StatusTaskListEnums;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskList>
 */
class TaskListFactory extends Factory
{
    protected $model = TaskList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'due_date' => $this->faker->dateTimeBetween('-1 month', '+1 year'),
            'priority' => $this->faker->randomElement(PriorityTaskListEnums::getValues()),
            'status' => $this->faker->randomElement(StatusTaskListEnums::getValues()),
            'task_id' => Task::factory(),
        ];
    }
}
