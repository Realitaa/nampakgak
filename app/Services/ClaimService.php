<?php

namespace App\Services;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClaimService
{
    /**
     * Disk used for claim proof images; shared with report images.
     */
    public const string IMAGE_DISK = 'public';

    /**
     * Directory on the image disk where claim proof images are stored.
     */
    public const string IMAGE_DIRECTORY = 'claims';

    /**
     * Whether a report currently accepts new ownership claims.
     *
     * A resolved report is already returned, so it accepts none. Reports under
     * review (`claimed`) still may receive competing claims, and `active` found
     * reports always do.
     */
    public function acceptsClaims(Item $item): bool
    {
        return $item->type === ItemType::Found && $item->status !== ItemStatus::Resolved;
    }

    /**
     * Submit an ownership claim for a found report.
     *
     * @param  array{proof_description: string}  $data
     *
     * @throws ValidationException when the report does not accept claims or the
     *                             claimant already has an active claim on it
     */
    public function submit(Item $item, User $claimant, array $data, ?UploadedFile $proofImage): Claim
    {
        if (! $this->acceptsClaims($item)) {
            throw ValidationException::withMessages([
                'proof_description' => __('This report is no longer accepting claims.'),
            ]);
        }

        if ($item->user_id === $claimant->id) {
            throw ValidationException::withMessages([
                'proof_description' => __('You cannot claim an item you reported finding.'),
            ]);
        }

        // One active claim per claimant per report; a rejected claim may be
        // replaced so a claimant who adds evidence is not locked out forever.
        $hasActiveClaim = $item->claims()
            ->where('claimant_id', $claimant->id)
            ->where('status', '!=', ClaimStatus::Rejected)
            ->exists();

        if ($hasActiveClaim) {
            throw ValidationException::withMessages([
                'proof_description' => __('You already have a claim awaiting review for this item.'),
            ]);
        }

        return DB::transaction(function () use ($item, $claimant, $data, $proofImage): Claim {
            $storedPath = $proofImage?->store(self::IMAGE_DIRECTORY, self::IMAGE_DISK);
            $imagePath = $storedPath === false ? null : $storedPath;

            return Claim::query()->create([
                'item_id' => $item->id,
                'claimant_id' => $claimant->id,
                'proof_description' => $data['proof_description'],
                'proof_image' => $imagePath,
                'status' => ClaimStatus::Pending,
                'rejection_reason' => null,
            ]);
        });
    }

    /**
     * Claims for a found report, newest first, with the claimant attached for
     * the owner-only management panel. Callers must authorize the viewer.
     *
     * @return Collection<int, Claim>
     */
    public function forOwner(Item $item): Collection
    {
        return $item->claims()
            ->with(['claimant:id,name,email,phone_number'])
            ->latest()
            ->get();
    }

    /**
     * Remove a proof image file if it lives inside the claim image directory.
     */
    public function deleteImageFile(string $path): void
    {
        if (! str_starts_with($path, self::IMAGE_DIRECTORY.'/')) {
            return;
        }

        Storage::disk(self::IMAGE_DISK)->delete($path);
    }
}
