<?php

namespace App\Enums\TaskList;

enum StatusTaskListEnums: string
{
    case OPENED = 'opened';
    case ONGOING = 'ongoing';
    case DONE = 'done';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
