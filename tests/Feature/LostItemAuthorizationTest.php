<?php

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;

beforeEach(function () {
    $this->category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
    $this->location = Location::factory()->create(['name' => 'Test Location']);
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();
});

function ownedItem(User $user, array $attributes = []): Item
{
    return Item::factory()
        ->for($user)
        ->recycle([test()->category, test()->location])
        ->create($attributes);
}

function lostReportPayload(): array
{
    return [
        'title' => 'Attempted Change',
        'description' => 'Attempted change.',
        'category_id' => test()->category->id,
        'location_id' => test()->location->id,
    ];
}

test('guests cannot reach any management route', function (Item $item) {
    $this->get(route('lost-items.edit', $item))->assertRedirect(route('login'));
    $this->put(route('lost-items.update', $item), lostReportPayload())->assertRedirect(route('login'));
    $this->patch(route('lost-items.status', $item), ['status' => 'resolved'])->assertRedirect(route('login'));
    $this->delete(route('lost-items.destroy', $item))->assertRedirect(route('login'));
})->with([fn () => Item::factory()->lost()->create()]);

test('another user cannot open the edit form', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Lost]);

    $this->actingAs($this->other)
        ->get(route('lost-items.edit', $item))
        ->assertForbidden();
});

test('another user cannot update the report', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Lost, 'title' => 'Original']);

    $this->actingAs($this->other)
        ->put(route('lost-items.update', $item), lostReportPayload())
        ->assertForbidden();

    expect($item->refresh()->title)->toBe('Original');
});

test('another user cannot change the status', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Lost, 'status' => ItemStatus::Active]);

    $this->actingAs($this->other)
        ->patch(route('lost-items.status', $item), ['status' => 'resolved'])
        ->assertForbidden();

    expect($item->refresh()->status)->toBe(ItemStatus::Active);
});

test('another user cannot delete the report', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Lost]);

    $this->actingAs($this->other)
        ->delete(route('lost-items.destroy', $item))
        ->assertForbidden();

    expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
});

test('a found report cannot be opened through the lost-report routes', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Found]);

    $this->actingAs($this->owner)
        ->get(route('lost-items.edit', $item))
        ->assertForbidden();
});

test('a found report cannot be updated through the lost-report routes', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Found, 'title' => 'Found Item']);

    $this->actingAs($this->owner)
        ->put(route('lost-items.update', $item), lostReportPayload())
        ->assertForbidden();

    expect($item->refresh()->title)->toBe('Found Item');
});

test('a found report cannot have its status changed through the lost-report routes', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Found]);

    $this->actingAs($this->owner)
        ->patch(route('lost-items.status', $item), ['status' => 'resolved'])
        ->assertForbidden();
});

test('a found report cannot be deleted through the lost-report routes', function () {
    $item = ownedItem($this->owner, ['type' => ItemType::Found]);

    $this->actingAs($this->owner)
        ->delete(route('lost-items.destroy', $item))
        ->assertForbidden();

    expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
});
