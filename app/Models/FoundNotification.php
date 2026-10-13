<?php

namespace App\Models;

use Database\Factories\FoundNotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A separate record stating that someone believes they found an item reported
 * lost. It never changes the lost report itself; the owner reviews it and
 * decides what happens next.
 *
 * @property int $id
 * @property int $item_id
 * @property int $reporter_id
 * @property string $note
 * @property string|null $image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Item $item
 * @property-read User $reporter
 */
#[Fillable(['item_id', 'reporter_id', 'note', 'image_path'])]
class FoundNotification extends Model
{
    /** @use HasFactory<FoundNotificationFactory> */
    use HasFactory;

    /**
     * The lost report this notification concerns.
     *
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * The user who reported finding the item.
     *
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
