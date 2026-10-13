<?php

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\FoundNotification;
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
    $this->finder = User::factory()->create();
});

function lostReport(User $owner, array $attributes = []): Item
{
    return Item::factory()
        ->for($owner)
        ->recycle([test()->category, test()->location])
        ->lost()
        ->create($attributes);
}

test('guests cannot submit a found notification', function () {
    $item = lostReport($this->owner);

    $this->post(route('items.found-notifications.store', $item), ['note' => 'I found it.'])
        ->assertRedirect(route('login'));
});

test('an authenticated user can report finding another user lost item', function () {
    $item = lostReport($this->owner, ['status' => ItemStatus::Active]);

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), [
            'note' => 'I found this near the library.',
        ])
        ->assertRedirect();

    $notification = FoundNotification::query()->sole();

    expect($notification->item_id)->toBe($item->id)
        ->and($notification->reporter_id)->toBe($this->finder->id)
        ->and($notification->note)->toBe('I found this near the library.');
});

test('the original lost report is left unchanged', function () {
    $item = lostReport($this->owner, ['status' => ItemStatus::Active]);
    $statusBefore = $item->status;
    $updatedBefore = $item->updated_at;

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), ['note' => 'Found it.'])
        ->assertRedirect();

    $item->refresh();

    expect($item->status)->toBe($statusBefore)
        ->and($item->updated_at->equalTo($updatedBefore))->toBeTrue();
});

test('an optional photo is stored with the notification', function () {
    $item = lostReport($this->owner);

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), [
            'note' => 'Found it in the lab.',
            'image' => UploadedFile::fake()->image('found.jpg', 800, 600),
        ])
        ->assertRedirect();

    $notification = FoundNotification::query()->sole();

    expect($notification->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($notification->image_path);
});

test('the owner cannot report finding their own lost item', function () {
    $item = lostReport($this->owner);

    $this->actingAs($this->owner)
        ->post(route('items.found-notifications.store', $item), ['note' => 'Mine.'])
        ->assertSessionHasErrors('note');

    expect(FoundNotification::query()->count())->toBe(0);
});

test('a note is required', function () {
    $item = lostReport($this->owner);

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), [])
        ->assertSessionHasErrors('note');
});

test('an oversized image is rejected', function () {
    $item = lostReport($this->owner);

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), [
            'note' => 'Found it.',
            'image' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ])
        ->assertSessionHasErrors('image');
});

test('a resolved lost report no longer accepts notifications', function () {
    $item = lostReport($this->owner, ['status' => ItemStatus::Resolved]);

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), ['note' => 'Found it.'])
        ->assertSessionHasErrors('note');

    expect(FoundNotification::query()->count())->toBe(0);
});

test('a found report cannot receive a found notification', function () {
    $item = Item::factory()->for($this->owner)->found()
        ->recycle([$this->category, $this->location])->create();

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), ['note' => 'Found it.'])
        ->assertSessionHasErrors('note');
});

test('the same reporter cannot submit twice for one report', function () {
    $item = lostReport($this->owner);

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), ['note' => 'First.'])
        ->assertRedirect();

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), ['note' => 'Second.'])
        ->assertSessionHasErrors('note');

    expect(FoundNotification::query()->count())->toBe(1);
});

test('a client cannot assign another reporter', function () {
    $item = lostReport($this->owner);
    $other = User::factory()->create();

    $this->actingAs($this->finder)
        ->post(route('items.found-notifications.store', $item), [
            'note' => 'Found it.',
            'reporter_id' => $other->id,
            'user_id' => $other->id,
        ])
        ->assertRedirect();

    expect(FoundNotification::query()->sole()->reporter_id)->toBe($this->finder->id);
});
