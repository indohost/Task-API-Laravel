<?php

namespace App\Enums\Task;

enum StatusTaskEnums: string
{
    case OPENED = 'opened';
    case ONGOING = 'ongoing';
    case DONE = 'done';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
