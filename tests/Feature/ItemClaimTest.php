<?php

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
    $this->location = Location::factory()->create(['name' => 'Test Location']);
    $this->owner = User::factory()->create();
    $this->claimant = User::factory()->create();
});

function foundReport(User $owner, array $attributes = []): Item
{
    return Item::factory()
        ->for($owner)
        ->recycle([test()->category, test()->location])
        ->found()
        ->create($attributes);
}

test('guests cannot submit a claim', function () {
    $item = foundReport($this->owner);

    $this->post(route('items.claims.store', $item), ['proof_description' => 'It is mine.'])
        ->assertRedirect(route('login'));
});

test('an authenticated user can claim another user found item', function () {
    $item = foundReport($this->owner, ['status' => ItemStatus::Active]);

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), [
            'proof_description' => 'The case has my initials inside.',
        ])
        ->assertRedirect();

    $claim = Claim::query()->sole();

    expect($claim->item_id)->toBe($item->id)
        ->and($claim->claimant_id)->toBe($this->claimant->id)
        ->and($claim->status)->toBe(ClaimStatus::Pending)
        ->and($claim->proof_description)->toBe('The case has my initials inside.');
});

test('an optional proof image is stored with the claim', function () {
    $item = foundReport($this->owner);

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), [
            'proof_description' => 'Proof attached.',
            'proof_image' => UploadedFile::fake()->image('proof.png'),
        ])
        ->assertRedirect();

    $claim = Claim::query()->sole();

    expect($claim->proof_image)->not->toBeNull();
    Storage::disk('public')->assertExists($claim->proof_image);
});

test('a user cannot claim their own found report', function () {
    $item = foundReport($this->owner);

    $this->actingAs($this->owner)
        ->post(route('items.claims.store', $item), ['proof_description' => 'Mine.'])
        ->assertSessionHasErrors('proof_description');

    expect(Claim::query()->count())->toBe(0);
});

test('a resolved found report no longer accepts claims', function () {
    $item = foundReport($this->owner, ['status' => ItemStatus::Resolved]);

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), ['proof_description' => 'Mine.'])
        ->assertSessionHasErrors('proof_description');

    expect(Claim::query()->count())->toBe(0);
});

test('a found report that is claimed still accepts competing claims', function () {
    $item = foundReport($this->owner, ['status' => ItemStatus::Claimed]);
    $rival = User::factory()->create();

    $this->actingAs($rival)
        ->post(route('items.claims.store', $item), ['proof_description' => 'It is mine.'])
        ->assertRedirect();

    expect(Claim::query()->count())->toBe(1);
});

test('a lost report cannot receive a claim', function () {
    $item = Item::factory()->for($this->owner)->lost()
        ->recycle([$this->category, $this->location])->create();

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), ['proof_description' => 'Mine.'])
        ->assertSessionHasErrors('proof_description');

    expect(Claim::query()->count())->toBe(0);
});

test('the same claimant cannot open a second active claim', function () {
    $item = foundReport($this->owner);

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), ['proof_description' => 'First.'])
        ->assertRedirect();

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), ['proof_description' => 'Second.'])
        ->assertSessionHasErrors('proof_description');

    expect(Claim::query()->count())->toBe(1);
});

test('a rejected claimant may submit again with fresh evidence', function () {
    $item = foundReport($this->owner);
    Claim::factory()->for($item)->for($this->claimant, 'claimant')->rejected()->create();

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), ['proof_description' => 'More evidence this time.'])
        ->assertRedirect();

    expect(Claim::query()->count())->toBe(2);
});

test('the proof description is required', function () {
    $item = foundReport($this->owner);

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), [])
        ->assertSessionHasErrors('proof_description');
});

test('an oversized proof image is rejected', function () {
    $item = foundReport($this->owner);

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), [
            'proof_description' => 'Mine.',
            'proof_image' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ])
        ->assertSessionHasErrors('proof_image');
});

test('a client cannot assign the claim to another claimant', function () {
    $item = foundReport($this->owner);
    $other = User::factory()->create();

    $this->actingAs($this->claimant)
        ->post(route('items.claims.store', $item), [
            'proof_description' => 'Mine.',
            'claimant_id' => $other->id,
            'status' => ClaimStatus::Approved->value,
        ])
        ->assertRedirect();

    $claim = Claim::query()->sole();

    expect($claim->claimant_id)->toBe($this->claimant->id)
        ->and($claim->status)->toBe(ClaimStatus::Pending);
});
