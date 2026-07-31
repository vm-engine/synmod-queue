<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

function seedFailedJob(string $jobClass = 'App\\Jobs\\TestJob', ?string $queue = 'default'): string
{
    $uuid = (string) Str::uuid();

    DB::table('failed_jobs')->insert([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => $queue,
        'payload' => json_encode([
            'displayName' => $jobClass,
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => ['commandName' => $jobClass],
        ]),
        'exception' => 'Exception: Something went wrong in '.$jobClass,
        'failed_at' => now(),
    ]);

    return $uuid;
}

beforeEach(function () {
    $this->fullRole = Role::factory()->create(['name' => 'Queue Manager', 'slug' => 'queue-manager', 'level' => 5]);
    RolePermission::factory()
        ->forRole($this->fullRole)
        ->forModule('queue', 'failed')
        ->fullCrud()
        ->create();

    $this->fullUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->fullUser->roles()->attach($this->fullRole);

    $this->readOnlyRole = Role::factory()->create(['name' => 'Queue Viewer', 'slug' => 'queue-viewer', 'level' => 8]);
    RolePermission::factory()
        ->forRole($this->readOnlyRole)
        ->forModule('queue', 'failed')
        ->withActions(['read'])
        ->create();

    $this->readOnlyUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->readOnlyUser->roles()->attach($this->readOnlyRole);

    $this->noPermRole = Role::factory()->create(['name' => 'No Access', 'slug' => 'no-access', 'level' => 10]);
    $this->noPermUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->noPermUser->roles()->attach($this->noPermRole);
});

test('user with full permission can render failed job list', function () {
    $this->actingAs($this->fullUser);

    Livewire::test('queue::failed-job-list')->assertOk();
});

test('user without queue.failed permission is redirected from the route', function () {
    $this->actingAs($this->noPermUser)
        ->get(backend_route('queue.failed'))
        ->assertRedirect();
});

test('it lists failed jobs', function () {
    seedFailedJob('App\\Jobs\\AlphaJob');
    seedFailedJob('App\\Jobs\\BetaJob');

    $this->actingAs($this->fullUser);

    $component = Livewire::test('queue::failed-job-list');

    expect($component->failedJobs->total())->toBe(2);
});

test('it filters by search', function () {
    seedFailedJob('App\\Jobs\\UniqueSearchableJob');
    seedFailedJob('App\\Jobs\\OtherJob');

    $this->actingAs($this->fullUser);

    $component = Livewire::test('queue::failed-job-list')
        ->set('q', 'UniqueSearchableJob');

    expect($component->failedJobs->total())->toBe(1);
});

test('it retries a failed job', function () {
    $uuid = seedFailedJob();

    $this->actingAs($this->fullUser);

    // The synthetic payload's job class doesn't exist, so let queue:retry run
    // for real (it just flips failed_jobs -> a queued job insert) without
    // executing the job body — no need to fake Artisan here.
    Livewire::test('queue::failed-job-list')
        ->call('retry', $uuid)
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed');

    // queue:retry removes the row from failed_jobs and pushes it back onto the queue.
    expect(DB::table('failed_jobs')->where('uuid', $uuid)->exists())->toBeFalse();
});

test('it deletes a failed job', function () {
    $uuid = seedFailedJob();

    $this->actingAs($this->fullUser);

    Livewire::test('queue::failed-job-list')
        ->call('delete', $uuid)
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed');

    expect(DB::table('failed_jobs')->where('uuid', $uuid)->exists())->toBeFalse();
});

test('it bulk retries selected jobs', function () {
    $uuid1 = seedFailedJob('App\\Jobs\\JobOne');
    $uuid2 = seedFailedJob('App\\Jobs\\JobTwo');

    $this->actingAs($this->fullUser);

    Livewire::test('queue::failed-job-list')
        ->set('selected', [$uuid1, $uuid2])
        ->call('retrySelected')
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed')
        ->assertSet('selected', []);

    expect(DB::table('failed_jobs')->whereIn('uuid', [$uuid1, $uuid2])->count())->toBe(0);
});

test('it bulk deletes selected jobs', function () {
    $uuid1 = seedFailedJob('App\\Jobs\\JobOne');
    $uuid2 = seedFailedJob('App\\Jobs\\JobTwo');

    $this->actingAs($this->fullUser);

    Livewire::test('queue::failed-job-list')
        ->set('selected', [$uuid1, $uuid2])
        ->call('deleteSelected')
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed')
        ->assertSet('selected', []);

    expect(DB::table('failed_jobs')->whereIn('uuid', [$uuid1, $uuid2])->count())->toBe(0);
});

test('it flushes all failed jobs', function () {
    seedFailedJob('App\\Jobs\\JobOne');
    seedFailedJob('App\\Jobs\\JobTwo');

    $this->actingAs($this->fullUser);

    Livewire::test('queue::failed-job-list')
        ->call('flushAll')
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed');

    expect(DB::table('failed_jobs')->count())->toBe(0);
});

test('read only user cannot retry a job', function () {
    $uuid = seedFailedJob();

    $this->actingAs($this->readOnlyUser);

    Livewire::test('queue::failed-job-list')
        ->call('retry', $uuid)
        ->assertDispatched('notify');

    expect(DB::table('failed_jobs')->where('uuid', $uuid)->exists())->toBeTrue();
});

test('read only user cannot delete a job', function () {
    $uuid = seedFailedJob();

    $this->actingAs($this->readOnlyUser);

    Livewire::test('queue::failed-job-list')
        ->call('delete', $uuid)
        ->assertDispatched('notify');

    expect(DB::table('failed_jobs')->where('uuid', $uuid)->exists())->toBeTrue();
});
