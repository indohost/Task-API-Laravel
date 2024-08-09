<?php

namespace Database\Factories;

use App\Models\TaskList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskListStorage>
 */
class TaskListStorageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename' => $this->faker->unique()->word . '.' . $this->faker->fileExtension,
            'orginal_name' => $this->faker->word . '.' . $this->faker->fileExtension,
            'type' => $this->faker->fileExtension,
            'path' => $this->faker->filePath(),
            'task_list_id' => TaskList::factory(),
        ];
    }
}
