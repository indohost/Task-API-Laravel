<?php

namespace App\Enums\TaskList;

enum PriorityTaskListEnums: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
