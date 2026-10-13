<?php

namespace App\Policies;

use App\Enums\ItemType;
use App\Models\Item;
use App\Models\User;

/**
 * Guards owner-facing report management. A report may only be managed by its
 * owner, and only through routes matching its own type, so a lost report can
 * never be touched through the found-report routes and vice versa.
 */
class ItemPolicy
{
    private function ownsReportOfType(User $user, Item $item, ItemType $type): bool
    {
        return $item->type === $type && $item->user_id === $user->id;
    }

    public function viewLost(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Lost);
    }

    public function updateLost(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Lost);
    }

    public function updateLostStatus(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Lost);
    }

    public function deleteLost(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Lost);
    }

    public function viewFound(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Found);
    }

    public function updateFound(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Found);
    }

    public function updateFoundStatus(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Found);
    }

    public function deleteFound(User $user, Item $item): bool
    {
        return $this->ownsReportOfType($user, $item, ItemType::Found);
    }
}
