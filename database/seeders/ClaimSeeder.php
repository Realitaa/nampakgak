<?php

namespace Database\Seeders;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClaimSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = $this->definitions();

        $claimants = User::query()->where('role', 'user')->orderBy('id')->pluck('id', 'email');
        $foundItems = Item::query()->found()->orderBy('id')->get();

        if ($claimants->isEmpty() || $foundItems->count() < 18) {
            return;
        }

        $claims = $foundItems->values()->map(function (Item $item, int $index) use ($claimants, $definitions): array {
            $definition = $definitions[$index];
            $claimantEmails = array_values(array_diff(
                $claimants->keys()->all(),
                [$item->user()->value('email')],
            ));

            return [
                'item_id' => $item->id,
                'claimant_id' => $claimants[$claimantEmails[$index % count($claimantEmails)]],
                'proof_description' => $definition['proof_description'],
                'proof_image' => $definition['proof_image'],
                'status' => $definition['status'],
                'rejection_reason' => $definition['rejection_reason'],
            ];
        })->all();

        DB::transaction(function () use ($claims, $foundItems): void {
            foreach ($claims as $claim) {
                $claimantId = $claim['claimant_id'];
                $itemId = $claim['item_id'];

                Claim::updateOrCreate(
                    ['item_id' => $itemId, 'claimant_id' => $claimantId],
                    [
                        'proof_description' => $claim['proof_description'],
                        'proof_image' => $claim['proof_image'],
                        'status' => $claim['status'],
                        'rejection_reason' => $claim['rejection_reason'],
                    ],
                );

                $status = $claim['status'];
                $itemStatus = match ($status) {
                    ClaimStatus::Pending => ItemStatus::Claimed,
                    ClaimStatus::Approved => ItemStatus::Resolved,
                    ClaimStatus::Rejected => ItemStatus::Active,
                };

                $foundItems->firstWhere('id', $itemId)?->update(['status' => $itemStatus]);
            }
        });
    }

    /**
     * Per-claim evidence, kept in item order so each found item has one claim.
     *
     * @return array<int, array{proof_description: string, proof_image: string|null, status: ClaimStatus, rejection_reason: string|null}>
     */
    private function definitions(): array
    {
        return [
            ['proof_description' => 'The earbuds case has a small crack on the hinge and my initials inside.', 'proof_image' => 'claims/proof-001.jpg', 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The backpack contains a blue notebook and a keychain with my dormitory key.', 'proof_image' => null, 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The green tag has my room number written on the back in small handwriting.', 'proof_image' => 'claims/proof-003.jpg', 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The hoodie was bought at a campus event and has a faded print on the sleeve.', 'proof_image' => null, 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The glasses have a scratch on the left lens from a fall last semester.', 'proof_image' => 'claims/proof-005.jpg', 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The water bottle has a dent near the base from being dropped on the stairs.', 'proof_image' => null, 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The wallet holds a photograph of my family behind the identity card slot.', 'proof_image' => 'claims/proof-007.jpg', 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The card sleeve has my name printed in small letters at the bottom corner.', 'proof_image' => null, 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The tote bag has an enamel pin of my faculty organization attached to the strap.', 'proof_image' => 'claims/proof-009.jpg', 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The bracelet was a gift and has a small bead missing near the clasp.', 'proof_image' => null, 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The notebook has my name written on the inside cover in blue ink.', 'proof_image' => 'claims/proof-011.jpg', 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The power bank has a small sticker of my student organization on the back.', 'proof_image' => 'claims/proof-012.jpg', 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The coin purse still contains a folded bus ticket from last week.', 'proof_image' => null, 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The locker key strap has my initials stitched in white thread.', 'proof_image' => 'claims/proof-014.jpg', 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The denim jacket has a patch sewn on the inside of the left pocket.', 'proof_image' => null, 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The sunglasses case has my name written on the inside with a marker.', 'proof_image' => 'claims/proof-016.jpg', 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The calculator has my student number engraved on the back cover.', 'proof_image' => null, 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
            ['proof_description' => 'The flash drive contains files from my previous semester and a named folder.', 'proof_image' => 'claims/proof-018.jpg', 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The practicum report has my name typed on the cover page but the pages were mixed up.', 'proof_image' => null, 'status' => ClaimStatus::Rejected, 'rejection_reason' => 'The details provided did not match the pages kept by the finder.'],
            ['proof_description' => 'The sling bag has a small keychain attached that matches my description.', 'proof_image' => 'claims/proof-020.jpg', 'status' => ClaimStatus::Rejected, 'rejection_reason' => 'Another claimant already provided matching evidence for this item.'],
            ['proof_description' => 'The house keys have a blue keychain with a faded logo of my hometown.', 'proof_image' => null, 'status' => ClaimStatus::Rejected, 'rejection_reason' => 'The claimant could not confirm a distinguishing feature on request.'],
            ['proof_description' => 'The chemistry module has highlighted sections and my name on the first page.', 'proof_image' => 'claims/proof-022.jpg', 'status' => ClaimStatus::Approved, 'rejection_reason' => null],
            ['proof_description' => 'The blue light glasses have a small repair mark on the right hinge.', 'proof_image' => null, 'status' => ClaimStatus::Pending, 'rejection_reason' => null],
        ];
    }
}
