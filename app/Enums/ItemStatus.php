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

    /**
     * Statuses a lost report may move to from this one.
     *
     * The product requirements leave the exact transitions open. For a lost
     * report the reporter either keeps looking (`active`), has a claim under
     * review (`claimed`), or has the item back (`resolved`). From `resolved`
     * the report can be reopened as `active` if the item turns out missing
     * again; `claimed` is never reachable directly from `resolved`.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Active => [self::Claimed, self::Resolved],
            self::Claimed => [self::Active, self::Resolved],
            self::Resolved => [self::Active],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
