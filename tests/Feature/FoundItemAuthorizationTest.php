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

function authorizableItem(User $user, array $attributes = []): Item
{
    return Item::factory()
        ->for($user)
        ->recycle([test()->category, test()->location])
        ->create($attributes);
}

function foundUpdatePayload(): array
{
    return [
        'title' => 'Attempted Change',
        'description' => 'Attempted change.',
        'category_id' => test()->category->id,
        'location_id' => test()->location->id,
    ];
}

test('guests cannot reach any found management route', function () {
    $item = Item::factory()->found()->create();

    $this->get(route('found-items.edit', $item))->assertRedirect(route('login'));
    $this->put(route('found-items.update', $item), foundUpdatePayload())->assertRedirect(route('login'));
    $this->patch(route('found-items.status', $item), ['status' => 'resolved'])->assertRedirect(route('login'));
    $this->delete(route('found-items.destroy', $item))->assertRedirect(route('login'));
});

test('another user cannot open the edit form', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Found]);

    $this->actingAs($this->other)
        ->get(route('found-items.edit', $item))
        ->assertForbidden();
});

test('another user cannot update the report', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Found, 'title' => 'Original']);

    $this->actingAs($this->other)
        ->put(route('found-items.update', $item), foundUpdatePayload())
        ->assertForbidden();

    expect($item->refresh()->title)->toBe('Original');
});

test('another user cannot change the status', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Found, 'status' => ItemStatus::Active]);

    $this->actingAs($this->other)
        ->patch(route('found-items.status', $item), ['status' => 'resolved'])
        ->assertForbidden();

    expect($item->refresh()->status)->toBe(ItemStatus::Active);
});

test('another user cannot delete the report', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Found]);

    $this->actingAs($this->other)
        ->delete(route('found-items.destroy', $item))
        ->assertForbidden();

    expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
});

test('a lost report cannot be opened through the found-report routes', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Lost]);

    $this->actingAs($this->owner)
        ->get(route('found-items.edit', $item))
        ->assertForbidden();
});

test('a lost report cannot be updated through the found-report routes', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Lost, 'title' => 'Lost Item']);

    $this->actingAs($this->owner)
        ->put(route('found-items.update', $item), foundUpdatePayload())
        ->assertForbidden();

    expect($item->refresh()->title)->toBe('Lost Item');
});

test('a lost report cannot have its status changed through the found-report routes', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Lost]);

    $this->actingAs($this->owner)
        ->patch(route('found-items.status', $item), ['status' => 'resolved'])
        ->assertForbidden();
});

test('a lost report cannot be deleted through the found-report routes', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Lost]);

    $this->actingAs($this->owner)
        ->delete(route('found-items.destroy', $item))
        ->assertForbidden();

    expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
});

test('a found report cannot be deleted through the lost-report routes', function () {
    $item = authorizableItem($this->owner, ['type' => ItemType::Found]);

    $this->actingAs($this->owner)
        ->delete(route('lost-items.destroy', $item))
        ->assertForbidden();

    expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
});
