<?php

namespace App\Models;

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property int $location_id
 * @property string $title
 * @property ItemType $type
 * @property string $description
 * @property string|null $image_path
 * @property ItemStatus $status
 * @property Carbon $event_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Category $category
 * @property-read Location $location
 */
#[Fillable(['user_id', 'category_id', 'location_id', 'title', 'type', 'description', 'image_path', 'status', 'event_date'])]
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ItemType::class,
            'status' => ItemStatus::class,
            'event_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return HasMany<Claim, $this>
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * @param  Builder<Item>  $query
     * @return Builder<Item>
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->where('type', ItemType::Lost);
    }

    /**
     * @param  Builder<Item>  $query
     * @return Builder<Item>
     */
    public function scopeFound(Builder $query): Builder
    {
        return $query->where('type', ItemType::Found);
    }
}
