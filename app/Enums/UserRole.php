<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::User->value => 'User',
            self::Admin->value => 'Admin',
        ];
    }
}
