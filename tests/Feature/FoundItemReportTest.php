<?php

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');

    $this->category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
    $this->location = Location::factory()->create(['name' => 'Test Location']);
    $this->user = User::factory()->create();
});

/**
 * @param  array<string, mixed>  $attributes
 */
function ownedFoundReport(User $user, array $attributes = []): Item
{
    return Item::factory()
        ->for($user)
        ->recycle([test()->category, test()->location])
        ->found()
        ->create($attributes);
}

/**
 * @return array<string, mixed>
 */
function foundPayload(): array
{
    return [
        'title' => 'Black Wireless Earbuds',
        'description' => 'Found in a charging case on a seat in Lecture Hall 1.',
        'category_id' => test()->category->id,
        'location_id' => test()->location->id,
        'event_date' => '2026-10-01',
    ];
}

describe('listing', function () {
    test('guests are redirected to the login page', function () {
        $this->get(route('found-items.index'))->assertRedirect(route('login'));
    });

    test('the owner sees their own found reports', function () {
        ownedFoundReport($this->user, ['title' => 'My Found Item']);

        $this->actingAs($this->user)
            ->get(route('found-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('items/found/Index')
                ->has('items.data', 1)
                ->where('items.data.0.title', 'My Found Item')
            );
    });

    test('reports owned by another user are hidden', function () {
        ownedFoundReport(User::factory()->create(), ['title' => 'Someone Else Item']);
        ownedFoundReport($this->user, ['title' => 'My Item']);

        $this->actingAs($this->user)
            ->get(route('found-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.title', 'My Item')
            );
    });

    test('lost reports owned by the user are excluded', function () {
        Item::factory()->for($this->user)->lost()->recycle([$this->category, $this->location])->create();
        ownedFoundReport($this->user, ['title' => 'My Found Item']);

        $this->actingAs($this->user)
            ->get(route('found-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.type', ItemType::Found->value)
            );
    });

    test('reports are sorted newest first', function () {
        $older = ownedFoundReport($this->user, ['created_at' => now()->subDay()]);
        $newer = ownedFoundReport($this->user, ['created_at' => now()]);

        $this->actingAs($this->user)
            ->get(route('found-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('items.data.0.id', $newer->id)
                ->where('items.data.1.id', $older->id)
            );
    });

    test('a report without an incident date is listed as null', function () {
        ownedFoundReport($this->user, ['event_date' => null]);

        $this->actingAs($this->user)
            ->get(route('found-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.event_date', null)
            );
    });
});

describe('search and filters', function () {
    test('the keyword filter matches titles and descriptions', function () {
        ownedFoundReport($this->user, ['title' => 'Green Drawstring Gym Bag']);
        ownedFoundReport($this->user, ['description' => 'Left a silver wristwatch on the bench.']);

        $this->actingAs($this->user)
            ->get(route('found-items.index', ['search' => 'Drawstring']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));

        $this->actingAs($this->user)
            ->get(route('found-items.index', ['search' => 'wristwatch']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));
    });

    test('the status filter returns only matching reports', function () {
        ownedFoundReport($this->user, ['status' => ItemStatus::Active]);
        ownedFoundReport($this->user, ['status' => ItemStatus::Resolved]);

        $this->actingAs($this->user)
            ->get(route('found-items.index', ['status' => 'resolved']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.status', ItemStatus::Resolved->value)
            );
    });

    test('the category and location filters can be combined with search', function () {
        ownedFoundReport($this->user, ['title' => 'Blue Umbrella']);
        ownedFoundReport($this->user, ['title' => 'Blue Umbrella', 'category_id' => Category::factory()->create()->id]);

        $this->actingAs($this->user)
            ->get(route('found-items.index', [
                'search' => 'Umbrella',
                'category' => $this->category->id,
                'location' => $this->location->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));
    });

    test('an invalid status value is ignored instead of failing', function () {
        ownedFoundReport($this->user);

        $this->actingAs($this->user)
            ->get(route('found-items.index', ['status' => 'nonsense']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));
    });

    test('pagination keeps the active filters', function () {
        Item::factory()->count(15)->for($this->user)->found()
            ->recycle([$this->category, $this->location])->create();

        $this->actingAs($this->user)
            ->get(route('found-items.index', ['status' => 'active']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('items.last_page', 2)
                ->where('items.next_page_url', fn (string $url) => str_contains($url, 'status=active'))
            );
    });

    test('the second page respects the applied filters', function () {
        Item::factory()->count(15)->for($this->user)->found()
            ->recycle([$this->category, $this->location])->create();
        Item::factory()->count(5)->for($this->user)->lost()
            ->recycle([$this->category, $this->location])->create();

        $this->actingAs($this->user)
            ->get(route('found-items.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('items.current_page', 2)
                ->has('items.data', 3)
                ->where('items.data.0.type', ItemType::Found->value)
            );
    });
});

describe('creation', function () {
    test('the create page renders with form options', function () {
        $this->actingAs($this->user)
            ->get(route('found-items.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('items/found/Create')
                ->has('categories')
                ->has('locations')
            );
    });

    test('valid data creates a found report owned by the authenticated user', function () {
        $this->actingAs($this->user)
            ->post(route('found-items.store'), [
                ...foundPayload(),
                'image' => UploadedFile::fake()->image('earbuds.jpg', 800, 600),
            ])
            ->assertRedirect(route('found-items.index'));

        $item = Item::query()->sole();

        expect($item->user_id)->toBe($this->user->id)
            ->and($item->type)->toBe(ItemType::Found)
            ->and($item->status)->toBe(ItemStatus::Active)
            ->and($item->title)->toBe('Black Wireless Earbuds')
            ->and($item->image_path)->not->toBeNull();

        Storage::disk('public')->assertExists($item->image_path);
    });

    test('the incident date may be omitted', function () {
        $payload = foundPayload();
        unset($payload['event_date']);

        $this->actingAs($this->user)
            ->post(route('found-items.store'), [
                ...$payload,
                'image' => UploadedFile::fake()->image('earbuds.png'),
            ])
            ->assertRedirect(route('found-items.index'));

        expect(Item::query()->sole()->event_date)->toBeNull();
    });

    test('an identity field from the client cannot reassign the report', function () {
        $other = User::factory()->create();

        $this->actingAs($this->user)
            ->post(route('found-items.store'), [
                ...foundPayload(),
                'user_id' => $other->id,
                'type' => ItemType::Lost->value,
                'status' => ItemStatus::Resolved->value,
                'image' => UploadedFile::fake()->image('earbuds.jpg'),
            ])
            ->assertRedirect(route('found-items.index'));

        $item = Item::query()->sole();

        expect($item->user_id)->toBe($this->user->id)
            ->and($item->type)->toBe(ItemType::Found)
            ->and($item->status)->toBe(ItemStatus::Active);
    });

    test('required fields are rejected', function () {
        $this->actingAs($this->user)
            ->post(route('found-items.store'), [])
            ->assertSessionHasErrors(['title', 'description', 'category_id', 'location_id', 'image']);
    });

    test('a non image file is rejected', function () {
        $this->actingAs($this->user)
            ->post(route('found-items.store'), [
                ...foundPayload(),
                'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('image');
    });

    test('an oversized image is rejected', function () {
        $this->actingAs($this->user)
            ->post(route('found-items.store'), [
                ...foundPayload(),
                'image' => UploadedFile::fake()->image('large.jpg')->size(3000),
            ])
            ->assertSessionHasErrors('image');
    });
});

describe('editing', function () {
    test('the edit page renders the report data', function () {
        $item = ownedFoundReport($this->user, ['title' => 'Old Title']);

        $this->actingAs($this->user)
            ->get(route('found-items.edit', $item))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('items/found/Edit')
                ->where('item.id', $item->id)
                ->where('item.title', 'Old Title')
            );
    });

    test('the owner can update their report', function () {
        $item = ownedFoundReport($this->user);

        $this->actingAs($this->user)
            ->put(route('found-items.update', $item), foundPayload())
            ->assertRedirect(route('found-items.index'));

        expect($item->refresh()->title)->toBe('Black Wireless Earbuds');
    });

    test('the stored image is preserved when no replacement is uploaded', function () {
        $path = UploadedFile::fake()->image('original.jpg')->store('items', 'public');
        $item = ownedFoundReport($this->user, ['image_path' => $path]);

        $this->actingAs($this->user)
            ->put(route('found-items.update', $item), foundPayload())
            ->assertRedirect(route('found-items.index'));

        expect($item->refresh()->image_path)->toBe($path);
        Storage::disk('public')->assertExists($path);
    });

    test('replacing the image removes the previous file after the update', function () {
        $oldPath = UploadedFile::fake()->image('original.jpg')->store('items', 'public');
        $item = ownedFoundReport($this->user, ['image_path' => $oldPath]);

        $this->actingAs($this->user)
            ->put(route('found-items.update', $item), [
                ...foundPayload(),
                'image' => UploadedFile::fake()->image('replacement.png'),
            ])
            ->assertRedirect(route('found-items.index'));

        $newPath = $item->refresh()->image_path;

        expect($newPath)->not->toBe($oldPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    });
});

describe('status changes', function () {
    test('a valid transition succeeds and persists', function () {
        $item = ownedFoundReport($this->user, ['status' => ItemStatus::Active]);

        $this->actingAs($this->user)
            ->patch(route('found-items.status', $item), ['status' => 'resolved'])
            ->assertRedirect();

        expect($item->refresh()->status)->toBe(ItemStatus::Resolved);
    });

    test('an invalid status value is rejected', function () {
        $item = ownedFoundReport($this->user);

        $this->actingAs($this->user)
            ->patch(route('found-items.status', $item), ['status' => 'returned'])
            ->assertSessionHasErrors('status');
    });

    test('a disallowed transition is rejected', function () {
        $item = ownedFoundReport($this->user, ['status' => ItemStatus::Resolved]);

        $this->actingAs($this->user)
            ->patch(route('found-items.status', $item), ['status' => 'claimed'])
            ->assertSessionHasErrors('status');

        expect($item->refresh()->status)->toBe(ItemStatus::Resolved);
    });
});

describe('deletion', function () {
    test('the owner can permanently delete their report', function () {
        $item = ownedFoundReport($this->user);

        $this->actingAs($this->user)
            ->delete(route('found-items.destroy', $item))
            ->assertRedirect(route('found-items.index'));

        expect(Item::query()->whereKey($item->id)->exists())->toBeFalse();
    });

    test('the stored image is removed with the report', function () {
        $path = UploadedFile::fake()->image('photo.jpg')->store('items', 'public');
        $item = ownedFoundReport($this->user, ['image_path' => $path]);

        $this->actingAs($this->user)
            ->delete(route('found-items.destroy', $item))
            ->assertRedirect(route('found-items.index'));

        Storage::disk('public')->assertMissing($path);
    });

    test('a report that still has claims cannot be deleted', function () {
        $item = ownedFoundReport($this->user);
        Claim::factory()->for($item)->create();

        $this->actingAs($this->user)
            ->delete(route('found-items.destroy', $item))
            ->assertSessionHasErrors('item');

        expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
    });
});
