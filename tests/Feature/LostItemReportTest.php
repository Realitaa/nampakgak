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
function ownedLostReport(User $user, array $attributes = []): Item
{
    return Item::factory()
        ->for($user)
        ->recycle([test()->category, test()->location])
        ->lost()
        ->create($attributes);
}

/**
 * @return array<string, mixed>
 */
function validPayload(): array
{
    return [
        'title' => 'Blue Canvas Backpack',
        'description' => 'Left behind in the library reading room.',
        'category_id' => test()->category->id,
        'location_id' => test()->location->id,
        'event_date' => '2026-10-01',
    ];
}

describe('listing', function () {
    test('guests are redirected to the login page', function () {
        $this->get(route('lost-items.index'))->assertRedirect(route('login'));
    });

    test('the owner sees their own lost reports', function () {
        ownedLostReport($this->user, ['title' => 'My Backpack']);

        $this->actingAs($this->user)
            ->get(route('lost-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('items/lost/Index')
                ->has('items.data', 1)
                ->where('items.data.0.title', 'My Backpack')
            );
    });

    test('reports owned by another user are hidden', function () {
        ownedLostReport(User::factory()->create(), ['title' => 'Someone Else Item']);
        ownedLostReport($this->user, ['title' => 'My Item']);

        $this->actingAs($this->user)
            ->get(route('lost-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.title', 'My Item')
            );
    });

    test('found reports owned by the user are excluded', function () {
        Item::factory()->for($this->user)->found()->recycle([$this->category, $this->location])->create();
        ownedLostReport($this->user, ['title' => 'My Lost Item']);

        $this->actingAs($this->user)
            ->get(route('lost-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.type', ItemType::Lost->value)
            );
    });

    test('reports are sorted newest first', function () {
        $older = ownedLostReport($this->user, ['created_at' => now()->subDay()]);
        $newer = ownedLostReport($this->user, ['created_at' => now()]);

        $this->actingAs($this->user)
            ->get(route('lost-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('items.data.0.id', $newer->id)
                ->where('items.data.1.id', $older->id)
            );
    });

    test('a report without an incident date is listed as null', function () {
        ownedLostReport($this->user, ['event_date' => null]);

        $this->actingAs($this->user)
            ->get(route('lost-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.event_date', null)
            );
    });
});

describe('search and filters', function () {
    test('the keyword filter matches titles and descriptions', function () {
        ownedLostReport($this->user, ['title' => 'Silver Wristwatch']);
        ownedLostReport($this->user, ['description' => 'Found a green drawstring bag.']);

        $this->actingAs($this->user)
            ->get(route('lost-items.index', ['search' => 'Wristwatch']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));

        $this->actingAs($this->user)
            ->get(route('lost-items.index', ['search' => 'drawstring']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));
    });

    test('the status filter returns only matching reports', function () {
        ownedLostReport($this->user, ['status' => ItemStatus::Active]);
        ownedLostReport($this->user, ['status' => ItemStatus::Resolved]);

        $this->actingAs($this->user)
            ->get(route('lost-items.index', ['status' => 'resolved']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 1)
                ->where('items.data.0.status', ItemStatus::Resolved->value)
            );
    });

    test('the category and location filters can be combined with search', function () {
        ownedLostReport($this->user, ['title' => 'Black Umbrella']);
        ownedLostReport($this->user, ['title' => 'Black Umbrella', 'category_id' => Category::factory()->create()->id]);

        $this->actingAs($this->user)
            ->get(route('lost-items.index', [
                'search' => 'Umbrella',
                'category' => $this->category->id,
                'location' => $this->location->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));
    });

    test('an invalid status value is ignored instead of failing', function () {
        ownedLostReport($this->user);

        $this->actingAs($this->user)
            ->get(route('lost-items.index', ['status' => 'nonsense']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));
    });

    test('pagination keeps the active filters', function () {
        Item::factory()->count(15)->for($this->user)->lost()
            ->recycle([$this->category, $this->location])->create();

        $this->actingAs($this->user)
            ->get(route('lost-items.index', ['status' => 'active']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('items.last_page', 2)
                ->where('items.next_page_url', fn (string $url) => str_contains($url, 'status=active'))
            );
    });

    test('the filter options come from the database', function () {
        $this->actingAs($this->user)
            ->get(route('lost-items.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories', fn ($categories) => $categories->contains(
                    fn ($option) => $option['id'] === $this->category->id
                ))
                ->where('locations', fn ($locations) => $locations->contains(
                    fn ($option) => $option['id'] === $this->location->id
                ))
            );
    });
});

describe('creation', function () {
    test('the create page renders with form options', function () {
        $this->actingAs($this->user)
            ->get(route('lost-items.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('items/lost/Create')
                ->has('categories')
                ->has('locations')
            );
    });

    test('valid data creates a lost report owned by the authenticated user', function () {
        $this->actingAs($this->user)
            ->post(route('lost-items.store'), [
                ...validPayload(),
                'image' => UploadedFile::fake()->image('backpack.jpg', 800, 600),
            ])
            ->assertRedirect(route('lost-items.index'));

        $item = Item::query()->sole();

        expect($item->user_id)->toBe($this->user->id)
            ->and($item->type)->toBe(ItemType::Lost)
            ->and($item->status)->toBe(ItemStatus::Active)
            ->and($item->title)->toBe('Blue Canvas Backpack')
            ->and($item->image_path)->not->toBeNull();

        Storage::disk('public')->assertExists($item->image_path);
    });

    test('the incident date may be omitted', function () {
        $payload = validPayload();
        unset($payload['event_date']);

        $this->actingAs($this->user)
            ->post(route('lost-items.store'), [
                ...$payload,
                'image' => UploadedFile::fake()->image('backpack.png'),
            ])
            ->assertRedirect(route('lost-items.index'));

        expect(Item::query()->sole()->event_date)->toBeNull();
    });

    test('an identity field from the client cannot reassign the report', function () {
        $other = User::factory()->create();

        $this->actingAs($this->user)
            ->post(route('lost-items.store'), [
                ...validPayload(),
                'user_id' => $other->id,
                'type' => ItemType::Found->value,
                'status' => ItemStatus::Resolved->value,
                'image' => UploadedFile::fake()->image('backpack.jpg'),
            ])
            ->assertRedirect(route('lost-items.index'));

        $item = Item::query()->sole();

        expect($item->user_id)->toBe($this->user->id)
            ->and($item->type)->toBe(ItemType::Lost)
            ->and($item->status)->toBe(ItemStatus::Active);
    });

    test('required fields are rejected', function () {
        $this->actingAs($this->user)
            ->post(route('lost-items.store'), [])
            ->assertSessionHasErrors(['title', 'description', 'category_id', 'location_id', 'image']);
    });

    test('a non image file is rejected', function () {
        $this->actingAs($this->user)
            ->post(route('lost-items.store'), [
                ...validPayload(),
                'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('image');
    });

    test('an oversized image is rejected', function () {
        $this->actingAs($this->user)
            ->post(route('lost-items.store'), [
                ...validPayload(),
                'image' => UploadedFile::fake()->image('large.jpg')->size(3000),
            ])
            ->assertSessionHasErrors('image');
    });

    test('an unknown category is rejected', function () {
        $this->actingAs($this->user)
            ->post(route('lost-items.store'), [
                ...validPayload(),
                'category_id' => 999999,
                'image' => UploadedFile::fake()->image('backpack.jpg'),
            ])
            ->assertSessionHasErrors('category_id');
    });
});

describe('editing', function () {
    test('the edit page renders the report data', function () {
        $item = ownedLostReport($this->user, ['title' => 'Old Title']);

        $this->actingAs($this->user)
            ->get(route('lost-items.edit', $item))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('items/lost/Edit')
                ->where('item.id', $item->id)
                ->where('item.title', 'Old Title')
            );
    });

    test('the owner can update their report', function () {
        $item = ownedLostReport($this->user);

        $this->actingAs($this->user)
            ->put(route('lost-items.update', $item), validPayload())
            ->assertRedirect(route('lost-items.index'));

        expect($item->refresh()->title)->toBe('Blue Canvas Backpack');
    });

    test('the stored image is preserved when no replacement is uploaded', function () {
        $path = UploadedFile::fake()->image('original.jpg')->store('items', 'public');
        $item = ownedLostReport($this->user, ['image_path' => $path]);

        $this->actingAs($this->user)
            ->put(route('lost-items.update', $item), validPayload())
            ->assertRedirect(route('lost-items.index'));

        expect($item->refresh()->image_path)->toBe($path);
        Storage::disk('public')->assertExists($path);
    });

    test('replacing the image removes the previous file after the update', function () {
        $oldPath = UploadedFile::fake()->image('original.jpg')->store('items', 'public');
        $item = ownedLostReport($this->user, ['image_path' => $oldPath]);

        $this->actingAs($this->user)
            ->put(route('lost-items.update', $item), [
                ...validPayload(),
                'image' => UploadedFile::fake()->image('replacement.png'),
            ])
            ->assertRedirect(route('lost-items.index'));

        $newPath = $item->refresh()->image_path;

        expect($newPath)->not->toBe($oldPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    });

    test('invalid data is rejected on update', function () {
        $item = ownedLostReport($this->user);

        $this->actingAs($this->user)
            ->put(route('lost-items.update', $item), ['title' => ''])
            ->assertSessionHasErrors(['title', 'description', 'category_id', 'location_id']);
    });
});

describe('status changes', function () {
    test('a valid transition succeeds and persists', function () {
        $item = ownedLostReport($this->user, ['status' => ItemStatus::Active]);

        $this->actingAs($this->user)
            ->patch(route('lost-items.status', $item), ['status' => 'resolved'])
            ->assertRedirect();

        expect($item->refresh()->status)->toBe(ItemStatus::Resolved);
    });

    test('an invalid status value is rejected', function () {
        $item = ownedLostReport($this->user);

        $this->actingAs($this->user)
            ->patch(route('lost-items.status', $item), ['status' => 'returned'])
            ->assertSessionHasErrors('status');
    });

    test('a disallowed transition is rejected', function () {
        $item = ownedLostReport($this->user, ['status' => ItemStatus::Resolved]);

        $this->actingAs($this->user)
            ->patch(route('lost-items.status', $item), ['status' => 'claimed'])
            ->assertSessionHasErrors('status');

        expect($item->refresh()->status)->toBe(ItemStatus::Resolved);
    });
});

describe('deletion', function () {
    test('the owner can permanently delete their report', function () {
        $item = ownedLostReport($this->user);

        $this->actingAs($this->user)
            ->delete(route('lost-items.destroy', $item))
            ->assertRedirect(route('lost-items.index'));

        expect(Item::query()->whereKey($item->id)->exists())->toBeFalse();
    });

    test('the stored image is removed with the report', function () {
        $path = UploadedFile::fake()->image('photo.jpg')->store('items', 'public');
        $item = ownedLostReport($this->user, ['image_path' => $path]);

        $this->actingAs($this->user)
            ->delete(route('lost-items.destroy', $item))
            ->assertRedirect(route('lost-items.index'));

        Storage::disk('public')->assertMissing($path);
    });

    test('a report that still has claims cannot be deleted', function () {
        $item = ownedLostReport($this->user);
        Claim::factory()->for($item)->create();

        $this->actingAs($this->user)
            ->delete(route('lost-items.destroy', $item))
            ->assertSessionHasErrors('item');

        expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
    });
});
