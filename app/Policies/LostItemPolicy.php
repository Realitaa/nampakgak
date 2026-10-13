<?php

namespace App\Policies;

use App\Enums\ItemType;
use App\Models\Item;
use App\Models\User;

class LostItemPolicy
{
    /**
     * A user may manage a report only when they own it and it is a lost report.
     * Found reports are managed through their own (not yet implemented) flow.
     */
    private function ownsLostReport(User $user, Item $item): bool
    {
        return $item->type === ItemType::Lost && $item->user_id === $user->id;
    }

    public function view(User $user, Item $item): bool
    {
        return $this->ownsLostReport($user, $item);
    }

    public function update(User $user, Item $item): bool
    {
        return $this->ownsLostReport($user, $item);
    }

    public function updateStatus(User $user, Item $item): bool
    {
        return $this->ownsLostReport($user, $item);
    }

    public function delete(User $user, Item $item): bool
    {
        return $this->ownsLostReport($user, $item);
    }
}
