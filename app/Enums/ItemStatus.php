<?php

namespace App\Enums;

enum ItemStatus: string
{
    case Active = 'active';
    case Claimed = 'claimed';
    case Resolved = 'resolved';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Active->value => 'Active',
            self::Claimed->value => 'Claimed',
            self::Resolved->value => 'Resolved',
        ];
    }
}
