<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Pending->value => 'Pending',
            self::Approved->value => 'Approved',
            self::Rejected->value => 'Rejected',
        ];
    }
}
