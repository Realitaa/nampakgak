<?php

namespace App\Enums;

enum ItemType: string
{
    case Lost = 'lost';
    case Found = 'found';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Lost->value => 'Lost',
            self::Found->value => 'Found',
        ];
    }
}
