<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::forget('illuminate:queue:restart');

    $this->fullRole = Role::factory()->create(['name' => 'Queue Manager', 'slug' => 'queue-manager-worker', 'level' => 5]);
    RolePermission::factory()
        ->forRole($this->fullRole)
        ->forModule('queue', 'worker')
        ->fullCrud()
        ->create();

    $this->fullUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->fullUser->roles()->attach($this->fullRole);

    $this->readOnlyRole = Role::factory()->create(['name' => 'Queue Worker Viewer', 'slug' => 'queue-worker-viewer', 'level' => 8]);
    RolePermission::factory()
        ->forRole($this->readOnlyRole)
        ->forModule('queue', 'worker')
        ->withActions(['read'])
        ->create();

    $this->readOnlyUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->readOnlyUser->roles()->attach($this->readOnlyRole);

    $this->noPermRole = Role::factory()->create(['name' => 'No Access Worker', 'slug' => 'no-access-worker', 'level' => 10]);
    $this->noPermUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->noPermUser->roles()->attach($this->noPermRole);
});

test('user with full permission can render worker control page', function () {
    $this->actingAs($this->fullUser);

    Livewire::test('queue::worker-control')->assertOk();
});

test('user without queue.worker permission is redirected from the route', function () {
    $this->actingAs($this->noPermUser)
        ->get(backend_route('queue.worker'))
        ->assertRedirect();
});

test('it shows never restarted before any restart signal', function () {
    $this->actingAs($this->fullUser);

    Livewire::test('queue::worker-control')
        ->assertSee(__('queue::labels.never_restarted'));

    expect(Cache::get('illuminate:queue:restart'))->toBeNull();
});

test('restart updates the queue restart cache key and page reflects it', function () {
    $this->actingAs($this->fullUser);

    Livewire::test('queue::worker-control')
        ->call('restart')
        ->assertDontSee(__('queue::labels.never_restarted'));

    $cached = Cache::get('illuminate:queue:restart');

    expect($cached)->not->toBeNull()
        ->and(now()->timestamp - $cached)->toBeLessThan(5);
});

test('read-only user cannot trigger a worker restart', function () {
    $this->actingAs($this->readOnlyUser);

    Livewire::test('queue::worker-control')
        ->call('restart')
        ->assertDispatched('notify', variant: 'danger');

    expect(Cache::get('illuminate:queue:restart'))->toBeNull();
});
