<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\FoundNotification;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FoundNotificationService
{
    /**
     * Disk used for notification images; shared with report images.
     */
    public const string IMAGE_DISK = 'public';

    /**
     * Directory on the image disk where notification images are stored.
     */
    public const string IMAGE_DIRECTORY = 'found-notifications';

    /**
     * Whether a lost report still accepts found notifications.
     *
     * A resolved report means the item is back with its owner, so no further
     * sightings are useful.
     */
    public function acceptsNotifications(Item $item): bool
    {
        return $item->type === ItemType::Lost && $item->status !== ItemStatus::Resolved;
    }

    /**
     * Record that a user believes they found an item reported lost.
     *
     * The original report is deliberately left untouched: the owner reviews the
     * notification before deciding whether the item is resolved.
     *
     * @param  array{note: string}  $data
     *
     * @throws ValidationException when the report does not accept notifications
     *                             or the reporter already submitted one
     */
    public function submit(Item $item, User $reporter, array $data, ?UploadedFile $image): FoundNotification
    {
        if (! $this->acceptsNotifications($item)) {
            throw ValidationException::withMessages([
                'note' => __('This report is no longer accepting found notifications.'),
            ]);
        }

        if ($item->user_id === $reporter->id) {
            throw ValidationException::withMessages([
                'note' => __('You cannot report finding your own lost item.'),
            ]);
        }

        if ($item->foundNotifications()->where('reporter_id', $reporter->id)->exists()) {
            throw ValidationException::withMessages([
                'note' => __('You have already reported finding this item.'),
            ]);
        }

        return DB::transaction(function () use ($item, $reporter, $data, $image): FoundNotification {
            $storedPath = $image?->store(self::IMAGE_DIRECTORY, self::IMAGE_DISK);
            $imagePath = $storedPath === false ? null : $storedPath;

            return FoundNotification::query()->create([
                'item_id' => $item->id,
                'reporter_id' => $reporter->id,
                'note' => $data['note'],
                'image_path' => $imagePath,
            ]);
        });
    }

    /**
     * Notifications against a lost report, newest first, with the reporter
     * attached for the owner-only review section. Callers must authorize access.
     *
     * @return Collection<int, FoundNotification>
     */
    public function forOwner(Item $item): Collection
    {
        return $item->foundNotifications()
            ->with(['reporter:id,name,email,phone_number'])
            ->latest()
            ->get();
    }
}
