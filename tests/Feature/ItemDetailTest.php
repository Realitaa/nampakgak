<?php

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Claim;
use App\Models\FoundNotification;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
    $this->location = Location::factory()->create(['name' => 'Test Location']);
    $this->owner = User::factory()->create();
    $this->viewer = User::factory()->create();
});

function detailItem(User $owner, array $attributes = []): Item
{
    return Item::factory()
        ->for($owner)
        ->recycle([test()->category, test()->location])
        ->create($attributes);
}

test('a lost report can be opened by a guest', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Lost, 'title' => 'Lost Backpack']);

    $this->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/Show')
            ->where('item.id', $item->id)
            ->where('item.type', 'lost')
            ->where('item.title', 'Lost Backpack')
            ->where('item.category.name', 'Test Category')
            ->where('item.location.name', 'Test Location')
        );
});

test('a found report can be opened by a guest', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found]);

    $this->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/Show')
            ->where('item.type', 'found')
        );
});

test('a nonexistent report returns 404', function () {
    $this->get(route('items.show', 999999))->assertNotFound();
});

test('a guest is offered neither action', function () {
    $lost = detailItem($this->owner, ['type' => ItemType::Lost]);
    $found = detailItem($this->owner, ['type' => ItemType::Found]);

    $this->get(route('items.show', $lost))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permissions.canReportFound', false)
            ->where('permissions.canClaim', false)
            ->where('permissions.isOwner', false)
        );

    $this->get(route('items.show', $found))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permissions.canClaim', false)
        );
});

test('a guest never receives owner-only panels', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found]);
    Claim::factory()->for($item)->create();

    $this->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('claims', null)
            ->where('foundNotifications', null)
            ->where('ownerLinks', null)
        );
});

test('another authenticated user is offered the found notification form on a lost report', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Lost, 'status' => ItemStatus::Active]);

    $this->actingAs($this->viewer)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permissions.canReportFound', true)
            ->where('permissions.canClaim', false)
            ->where('permissions.isOwner', false)
            ->where('claims', null)
        );
});

test('a resolved lost report no longer offers the found notification form', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Lost, 'status' => ItemStatus::Resolved]);

    $this->actingAs($this->viewer)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('permissions.canReportFound', false));
});

test('another authenticated user is offered the claim form on a found report', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found, 'status' => ItemStatus::Active]);

    $this->actingAs($this->viewer)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permissions.canClaim', true)
            ->where('permissions.canReportFound', false)
            ->where('permissions.isOwner', false)
        );
});

test('a resolved found report no longer offers the claim form', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found, 'status' => ItemStatus::Resolved]);

    $this->actingAs($this->viewer)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('permissions.canClaim', false));
});

test('the owner is offered management actions and not public forms', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found, 'status' => ItemStatus::Active]);

    $this->actingAs($this->owner)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('permissions.isOwner', true)
            ->where('permissions.canEdit', true)
            ->where('permissions.canDelete', true)
            ->where('permissions.canClaim', false)
            ->where('permissions.canReportFound', false)
            ->where('ownerLinks.edit', route('found-items.edit', $item))
            ->where('ownerLinks.delete', route('found-items.destroy', $item))
        );
});

test('a lost report owner receives the found notifications they must review', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Lost]);
    FoundNotification::factory()->for($item)->for($this->viewer, 'reporter')->create([
        'note' => 'I found it near the cafeteria.',
    ]);

    $this->actingAs($this->owner)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('foundNotifications', 1)
            ->where('foundNotifications.0.note', 'I found it near the cafeteria.')
            ->where('foundNotifications.0.reporter.name', $this->viewer->name)
        );
});

test('another user never receives the found notifications of a lost report', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Lost]);
    FoundNotification::factory()->for($item)->create();

    $this->actingAs($this->viewer)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('foundNotifications', null));
});

test('a found report owner receives the claim list with claimant contact details', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found]);
    $claimant = User::factory()->create(['phone_number' => '081200000009']);
    Claim::factory()->for($item)->for($claimant, 'claimant')->create([
        'proof_description' => 'The case has my initials inside.',
    ]);

    $this->actingAs($this->owner)
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('claims', 1)
            ->where('claims.0.proof_description', 'The case has my initials inside.')
            ->where('claims.0.claimant.email', $claimant->email)
            ->where('claims.0.claimant.phone_number', '081200000009')
        );
});

test('claimant contact details are never sent to a non owner', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found]);
    $claimant = User::factory()->create(['phone_number' => '081200000009']);
    Claim::factory()->for($item)->for($claimant, 'claimant')->create();

    $response = $this->actingAs($this->viewer)->get(route('items.show', $item))->assertOk();

    $response->assertInertia(fn (Assert $page) => $page->where('claims', null));

    // Belt and braces: the private number must not appear anywhere in the payload.
    expect($response->getContent())->not->toContain('081200000009');
});

test('the public detail response never exposes private contact data', function () {
    $item = detailItem($this->owner, ['type' => ItemType::Found]);
    $this->owner->update(['phone_number' => '081277777777']);

    $response = $this->get(route('items.show', $item))->assertOk();

    expect($response->getContent())
        ->not->toContain('081277777777')
        ->not->toContain($this->owner->email);
});
