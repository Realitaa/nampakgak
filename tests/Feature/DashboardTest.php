<?php

use App\Enums\ItemStatus;
use App\Enums\ItemType;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
    $this->location = Location::factory()->create(['name' => 'Test Location']);
    $this->user = User::factory()->create();
});

/**
 * @param  array<string, mixed>  $attributes
 */
function dashboardItem(User $user, array $attributes = []): Item
{
    return Item::factory()
        ->for($user)
        ->recycle([test()->category, test()->location])
        ->create($attributes);
}

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('the summary counts only the authenticated user own items', function () {
    $other = User::factory()->create();

    dashboardItem($this->user, ['type' => ItemType::Lost, 'status' => ItemStatus::Active]);
    dashboardItem($this->user, ['type' => ItemType::Lost, 'status' => ItemStatus::Active]);
    dashboardItem($this->user, ['type' => ItemType::Found, 'status' => ItemStatus::Active]);
    dashboardItem($this->user, ['type' => ItemType::Lost, 'status' => ItemStatus::Resolved]);

    // Another user's activity must never leak into these counts.
    dashboardItem($other, ['type' => ItemType::Lost, 'status' => ItemStatus::Active]);
    dashboardItem($other, ['type' => ItemType::Found, 'status' => ItemStatus::Active]);
    dashboardItem($other, ['type' => ItemType::Found, 'status' => ItemStatus::Resolved]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('DashboardUser')
            ->where('summary.activeLost', 2)
            ->where('summary.activeFound', 1)
            ->where('summary.resolved', 1)
        );
});

test('pending claims are counted only for items owned by the authenticated user', function () {
    $other = User::factory()->create();
    $claimant = User::factory()->create();

    $ownedItem = dashboardItem($this->user, ['type' => ItemType::Found, 'status' => ItemStatus::Active]);
    $otherItem = dashboardItem($other, ['type' => ItemType::Found, 'status' => ItemStatus::Active]);

    Claim::factory()->pending()->for($ownedItem)->for($claimant, 'claimant')->create();
    Claim::factory()->pending()->for($otherItem)->for($claimant, 'claimant')->create();
    Claim::factory()->approved()->for($ownedItem)->for($claimant, 'claimant')->create();
    Claim::factory()->pending()->for($otherItem)->for($this->user, 'claimant')->create();

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('summary.pendingClaims', 1)
        );
});

test('reports contain only the authenticated user own items, newest first, capped at five', function () {
    $other = User::factory()->create();

    foreach (range(1, 6) as $index) {
        dashboardItem($this->user, [
            'title' => "Owned report {$index}",
            'created_at' => now()->subDays(10 - $index),
        ]);
    }

    dashboardItem($other, ['title' => 'Someone else report', 'created_at' => now()]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('reports', 5)
            ->where('reports.0.title', 'Owned report 6')
            ->where('reports.1.title', 'Owned report 5')
            ->where('reports.4.title', 'Owned report 2')
            ->where('reports.0.category.name', 'Test Category')
            ->where('reports.0.location.name', 'Test Location')
            ->etc()
        );
});

test('claims include incoming claims on owned items and submitted claims by the user', function () {
    $claimant = User::factory()->create(['name' => 'Claimant Person']);
    $owner = User::factory()->create();

    $ownedItem = dashboardItem($this->user, ['title' => 'My found umbrella', 'type' => ItemType::Found]);
    $otherItem = dashboardItem($owner, ['title' => 'Someone else wallet', 'type' => ItemType::Found]);

    Claim::factory()->pending()->for($ownedItem)->for($claimant, 'claimant')->create([
        'proof_description' => 'It has my initials on the handle.',
    ]);
    Claim::factory()->rejected()->for($otherItem)->for($this->user, 'claimant')->create();

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('claims', 2)
            ->where('claims.0.direction', 'incoming')
            ->where('claims.0.reportTitle', 'My found umbrella')
            ->where('claims.0.reportId', $ownedItem->id)
            ->where('claims.0.claimant', 'Claimant Person')
            ->where('claims.0.status', 'pending')
            ->where('claims.0.proofDescription', 'It has my initials on the handle.')
            ->where('claims.1.direction', 'submitted')
            ->where('claims.1.reportTitle', 'Someone else wallet')
            ->where('claims.1.claimant', null)
            ->where('claims.1.status', 'rejected')
            ->etc()
        );
});

test('the claims payload never exposes another user contact details', function () {
    $claimant = User::factory()->create([
        'name' => 'Claimant Person',
        'email' => 'private-claimant@example.com',
        'phone_number' => '081234567890',
    ]);

    $ownedItem = dashboardItem($this->user, ['type' => ItemType::Found]);
    Claim::factory()->pending()->for($ownedItem)->for($claimant, 'claimant')->create();

    $owner = User::factory()->create([
        'email' => 'private-owner@example.com',
        'phone_number' => '089876543210',
    ]);
    $otherItem = dashboardItem($owner, ['type' => ItemType::Found]);
    Claim::factory()->pending()->for($otherItem)->for($this->user, 'claimant')->create();

    $response = $this->actingAs($this->user)->get(route('dashboard'));

    $response->assertOk();

    $content = $response->getContent();

    expect($content)
        ->not->toContain('private-claimant@example.com')
        ->not->toContain('081234567890')
        ->not->toContain('private-owner@example.com')
        ->not->toContain('089876543210');
});

test('the dashboard receives the status option maps', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('statusOptions.active', 'Active')
            ->where('statusOptions.claimed', 'Claimed')
            ->where('statusOptions.resolved', 'Resolved')
            ->where('claimStatusOptions.pending', 'Pending')
            ->where('claimStatusOptions.approved', 'Approved')
            ->where('claimStatusOptions.rejected', 'Rejected')
        );
});
