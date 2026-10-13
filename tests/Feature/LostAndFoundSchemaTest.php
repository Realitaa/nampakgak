<?php

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;

test('a user can be an administrator', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->isAdmin())->toBeTrue();
});

test('a user has many items and claims', function () {
    $user = User::factory()->create();
    Item::factory()->for($user)->create();
    Claim::factory()->for($user, 'claimant')->create();

    expect($user->items)->toHaveCount(1)
        ->and($user->claims)->toHaveCount(1);
});

test('a category and a location each have many items', function () {
    $category = Category::factory()->create();
    $location = Location::factory()->create();
    Item::factory()->for($category)->for($location)->create();

    expect($category->items)->toHaveCount(1)
        ->and($location->items)->toHaveCount(1);
});

test('an item belongs to a user, category and location', function () {
    $item = Item::factory()->create();

    expect($item->user)->toBeInstanceOf(User::class)
        ->and($item->category)->toBeInstanceOf(Category::class)
        ->and($item->location)->toBeInstanceOf(Location::class);
});
test('item type and status are cast to enums', function () {
    $item = Item::factory()->lost()->resolved()->create();

    expect($item->type)->toBe(ItemType::Lost)
        ->and($item->status)->toBe(ItemStatus::Resolved);
});

test('an item has many claims', function () {
    $item = Item::factory()->found()->create();
    Claim::factory()->for($item)->count(2)->create();

    expect($item->claims)->toHaveCount(2);
});

test('a claim belongs to an item and a claimant', function () {
    $claim = Claim::factory()->create();

    expect($claim->item)->toBeInstanceOf(Item::class)
        ->and($claim->claimant)->toBeInstanceOf(User::class);
});

test('a claim status is cast and rejected claims carry a reason', function () {
    $claim = Claim::factory()->rejected()->create();

    expect($claim->status)->toBe(ClaimStatus::Rejected)
        ->and($claim->rejection_reason)->not->toBeNull();
});

test('the claim factory only creates claims for found items', function () {
    Claim::factory()->count(5)->create();

    expect(Item::query()->where('type', ItemType::Lost)->has('claims')->count())->toBe(0);
});

test('the found scope returns only found items', function () {
    Item::factory()->lost()->create();
    $found = Item::factory()->found()->create();

    expect(Item::query()->found()->pluck('id')->all())->toBe([$found->id]);
});
