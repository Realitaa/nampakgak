<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use Database\Factories\ClaimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $item_id
 * @property int $claimant_id
 * @property string $proof_description
 * @property string|null $proof_image
 * @property ClaimStatus $status
 * @property string|null $rejection_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Item $item
 * @property-read User $claimant
 */
#[Fillable(['item_id', 'claimant_id', 'proof_description', 'proof_image', 'status', 'rejection_reason'])]
class Claim extends Model
{
    /** @use HasFactory<ClaimFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ClaimStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }
}
