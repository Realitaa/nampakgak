<?php

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // The category and location factories draw from small unique name pools,
    // so tests that create many items reuse a single pair of records.
    $this->category = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
    $this->location = Location::factory()->create(['name' => 'Main Library']);
});

test('the landing page renders for a guest', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

test('the landing page shows real reports with the expected shape', function () {
    Item::factory()
        ->for($this->category)
        ->for($this->location)
        ->create([
            'title' => 'Black Wireless Earbuds',
            'type' => ItemType::Lost,
            'description' => 'Left on a canteen table.',
            'image_path' => 'items/earbuds.jpg',
            'status' => ItemStatus::Active,
            'event_date' => '2026-10-10',
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('reports', 1)
            ->where('reports.0.title', 'Black Wireless Earbuds')
            ->where('reports.0.type', ItemType::Lost->value)
            ->where('reports.0.description', 'Left on a canteen table.')
            ->where('reports.0.image_path', 'items/earbuds.jpg')
            ->where('reports.0.status', ItemStatus::Active->value)
            ->where('reports.0.event_date', '2026-10-10')
            ->where('reports.0.category.id', $this->category->id)
            ->where('reports.0.category.name', 'Electronics')
            ->where('reports.0.location.id', $this->location->id)
            ->where('reports.0.location.name', 'Main Library')
            ->etc()
        );
});

test('reports are the newest first and capped at twelve', function () {
    foreach (range(1, 14) as $index) {
        Item::factory()
            ->for($this->category)
            ->for($this->location)
            ->create([
                'title' => "Report {$index}",
                'created_at' => now()->subDays(20 - $index),
            ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('reports', 12)
            ->where('reports.0.title', 'Report 14')
            ->where('reports.1.title', 'Report 13')
            ->where('reports.11.title', 'Report 3')
            ->etc()
        );
});

test('a report without an incident date is returned as null', function () {
    Item::factory()
        ->for($this->category)
        ->for($this->location)
        ->create(['event_date' => null]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('reports', 1)
            ->where('reports.0.event_date', null)
        );
});

test('the categories come from the database ordered by name', function () {
    Category::factory()->create(['name' => 'Keys', 'slug' => 'keys']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('categories', fn ($categories) => $categories->contains(
                fn ($category) => $category['id'] === $this->category->id
                    && $category['name'] === 'Electronics'
            ))
            ->where('categories.0.name', 'Electronics')
            ->where('categories.1.name', 'Keys')
        );
});

test('the option maps are present for the filters and labels', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('statusOptions.active', 'Active')
            ->where('statusOptions.claimed', 'Claimed')
            ->where('statusOptions.resolved', 'Resolved')
            ->where('typeOptions.lost', 'Lost')
            ->where('typeOptions.found', 'Found')
        );
});

test('the public payload never exposes report owners contact details', function () {
    $owner = User::factory()->create([
        'email' => 'private-owner@example.com',
        'phone_number' => '081234567890',
    ]);

    Item::factory()
        ->for($owner)
        ->for($this->category)
        ->for($this->location)
        ->create(['title' => 'Found umbrella']);

    $response = $this->get(route('home'));

    $response->assertOk();

    $content = $response->getContent();

    expect($content)
        ->not->toContain('private-owner@example.com')
        ->not->toContain('081234567890');
});
