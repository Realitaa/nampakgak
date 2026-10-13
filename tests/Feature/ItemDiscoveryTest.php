<?php

use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // The category and location factories draw from small unique name pools,
    // so tests that create many items reuse a single pair of records.
    $this->category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
    $this->location = Location::factory()->create(['name' => 'Test Location']);
});

function makeItems(int $count, array $attributes = []): void
{
    Item::factory()
        ->count($count)
        ->recycle([test()->category, test()->location])
        ->create($attributes);
}

test('the listing is accessible without authentication', function () {
    makeItems(3);

    $this->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/Index')
            ->has('items.data', 3)
        );
});

test('the listing returns real database records with their relations', function () {
    $item = Item::factory()->create(['title' => 'Blue Canvas Backpack']);

    $this->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/Index')
            ->has('items.data', 1)
            ->where('items.data.0.id', $item->id)
            ->where('items.data.0.title', 'Blue Canvas Backpack')
            ->where('items.data.0.category.name', $item->category->name)
            ->where('items.data.0.location.name', $item->location->name)
        );
});

test('reports are sorted newest first', function () {
    $older = Item::factory()->recycle([$this->category, $this->location])->create(['created_at' => now()->subDay()]);
    $newer = Item::factory()->recycle([$this->category, $this->location])->create(['created_at' => now()]);

    $this->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.data.0.id', $newer->id)
            ->where('items.data.1.id', $older->id)
        );
});

test('reports are paginated server side', function () {
    makeItems(15);

    $this->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 12)
            ->where('items.current_page', 1)
            ->where('items.last_page', 2)
            ->where('items.total', 15)
        );
});

test('the search filter matches titles', function () {
    Item::factory()->create(['title' => 'Silver Wristwatch']);
    Item::factory()->create(['title' => 'Grey Hoodie Jacket']);

    $this->get(route('items.index', ['search' => 'Wristwatch']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 1)
            ->where('items.data.0.title', 'Silver Wristwatch')
        );
});

test('the search filter matches descriptions', function () {
    Item::factory()->recycle([$this->category, $this->location])->create(['description' => 'Found inside a green drawstring bag near the library.']);
    Item::factory()->recycle([$this->category, $this->location])->create(['description' => 'Left behind in the lecture hall.']);

    $this->get(route('items.index', ['search' => 'drawstring']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1));
});

test('the type filter returns only matching reports', function () {
    makeItems(2, ['type' => ItemType::Lost]);
    makeItems(3, ['type' => ItemType::Found]);

    $this->get(route('items.index', ['type' => 'found']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 3)
            ->where('items.data.0.type', ItemType::Found->value)
        );
});

test('the category filter returns only matching reports', function () {
    $category = Category::factory()->create();
    Item::factory()->for($category)->create();
    Item::factory()->create();

    $this->get(route('items.index', ['category' => $category->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 1)
            ->where('items.data.0.category.id', $category->id)
        );
});

test('the location filter returns only matching reports', function () {
    $location = Location::factory()->create();
    Item::factory()->for($location)->create();
    Item::factory()->create();

    $this->get(route('items.index', ['location' => $location->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 1)
            ->where('items.data.0.location.id', $location->id)
        );
});

test('all filters can be combined', function () {
    $category = Category::factory()->create();
    $location = Location::factory()->create();

    Item::factory()->found()->for($category)->for($location)->create([
        'title' => 'Black Wireless Earbuds',
    ]);
    Item::factory()->lost()->for($category)->for($location)->create([
        'title' => 'Black Wireless Earbuds',
    ]);
    Item::factory()->found()->for($category)->create(['title' => 'Black Wireless Earbuds']);
    Item::factory()->found()->for($location)->create(['title' => 'Black Wireless Earbuds']);

    $this->get(route('items.index', [
        'search' => 'Wireless',
        'type' => 'found',
        'category' => $category->id,
        'location' => $location->id,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 1)
            ->where('items.data.0.type', ItemType::Found->value)
            ->where('items.data.0.category.id', $category->id)
            ->where('items.data.0.location.id', $location->id)
        );
});

test('an unknown type value is ignored instead of failing', function () {
    makeItems(2);

    $this->get(route('items.index', ['type' => 'stolen']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 2));
});

test('a category id that does not exist is ignored instead of failing', function () {
    makeItems(2);

    $this->get(route('items.index', ['category' => 999999]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 2));
});

test('the active filters are passed through to the page', function () {
    $category = Category::factory()->create();

    $this->get(route('items.index', [
        'search' => 'wallet',
        'type' => 'found',
        'category' => $category->id,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'wallet')
            ->where('filters.type', 'found')
            ->where('filters.category', $category->id)
        );
});

test('pagination links keep the active filters', function () {
    makeItems(15, ['type' => ItemType::Found]);

    $this->get(route('items.index', ['type' => 'found']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.last_page', 2)
            ->where('items.next_page_url', fn (string $url) => str_contains($url, '/items?')
                && str_contains($url, 'type=found'))
        );
});

test('the newest report appears first on the first page', function () {
    $oldest = Item::factory()->recycle([$this->category, $this->location])->create(['created_at' => now()->subDays(3)]);
    $newest = Item::factory()->recycle([$this->category, $this->location])->create(['created_at' => now()]);

    $this->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.data.0.id', $newest->id)
            ->where('items.data.1.id', $oldest->id)
        );
});

test('the second page respects the applied filters', function () {
    makeItems(15, ['type' => ItemType::Found]);
    makeItems(5, ['type' => ItemType::Lost]);

    $this->get(route('items.index', ['type' => 'found', 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.current_page', 2)
            ->has('items.data', 3)
            ->where('items.data.0.type', ItemType::Found->value)
        );
});

test('the filter options come from the database', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);
    $location = Location::factory()->create(['name' => 'Main Library']);

    $this->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('categories', fn ($categories) => $categories->contains(
                fn ($option) => $option['id'] === $category->id && $option['name'] === 'Electronics'
            ))
            ->where('locations', fn ($locations) => $locations->contains(
                fn ($option) => $option['id'] === $location->id && $option['name'] === 'Main Library'
            ))
        );
});

test('an empty result set renders the page without error', function () {
    $this->get(route('items.index', ['search' => 'nothing-matches-this']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 0));
});

test('a report without an incident date is returned as null', function () {
    Item::factory()->recycle([$this->category, $this->location])->create(['event_date' => null]);

    $this->get(route('items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 1)
            ->where('items.data.0.event_date', null)
        );
});
