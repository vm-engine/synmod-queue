<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use VmEngine\Queue\Services\PendingJobService;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

function seedPendingJob(string $jobClass = 'App\\Jobs\\TestJob', string $queue = 'default', int $attempts = 0): int
{
    return (int) DB::table('jobs')->insertGetId([
        'queue' => $queue,
        'payload' => json_encode([
            'displayName' => $jobClass,
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => ['commandName' => $jobClass],
        ]),
        'attempts' => $attempts,
        'reserved_at' => null,
        'available_at' => now()->getTimestamp(),
        'created_at' => now()->getTimestamp(),
    ]);
}

beforeEach(function () {
    config(['queue.default' => 'database']);

    $this->fullRole = Role::factory()->create(['name' => 'Queue Manager', 'slug' => 'queue-manager', 'level' => 5]);
    RolePermission::factory()
        ->forRole($this->fullRole)
        ->forModule('queue', 'pending')
        ->fullCrud()
        ->create();

    $this->fullUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->fullUser->roles()->attach($this->fullRole);

    $this->readOnlyRole = Role::factory()->create(['name' => 'Queue Viewer', 'slug' => 'queue-viewer', 'level' => 8]);
    RolePermission::factory()
        ->forRole($this->readOnlyRole)
        ->forModule('queue', 'pending')
        ->withActions(['read'])
        ->create();

    $this->readOnlyUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->readOnlyUser->roles()->attach($this->readOnlyRole);

    $this->noPermRole = Role::factory()->create(['name' => 'No Access', 'slug' => 'no-access', 'level' => 10]);
    $this->noPermUser = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $this->noPermUser->roles()->attach($this->noPermRole);
});

test('user with full permission can render pending job list', function () {
    $this->actingAs($this->fullUser);

    Livewire::test('queue::pending-job-list')->assertOk();
});

test('user without queue.pending permission is redirected from the route', function () {
    $this->actingAs($this->noPermUser)
        ->get(backend_route('queue.pending'))
        ->assertRedirect();
});

test('it lists pending jobs', function () {
    seedPendingJob('App\\Jobs\\AlphaJob');
    seedPendingJob('App\\Jobs\\BetaJob');

    $this->actingAs($this->fullUser);

    $component = Livewire::test('queue::pending-job-list');

    expect($component->pendingJobs->total())->toBe(2);
});

test('it filters by search', function () {
    seedPendingJob('App\\Jobs\\UniqueSearchableJob');
    seedPendingJob('App\\Jobs\\OtherJob');

    $this->actingAs($this->fullUser);

    $component = Livewire::test('queue::pending-job-list')
        ->set('q', 'UniqueSearchableJob');

    expect($component->pendingJobs->total())->toBe(1);
});

test('it filters by queue name', function () {
    seedPendingJob('App\\Jobs\\JobOne', 'emails');
    seedPendingJob('App\\Jobs\\JobTwo', 'exports');

    $this->actingAs($this->fullUser);

    $component = Livewire::test('queue::pending-job-list')
        ->set('filterQueue', 'emails');

    expect($component->pendingJobs->total())->toBe(1);
});

test('it deletes a pending job', function () {
    $id = seedPendingJob();

    $this->actingAs($this->fullUser);

    Livewire::test('queue::pending-job-list')
        ->call('delete', $id)
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed');

    expect(DB::table('jobs')->where('id', $id)->exists())->toBeFalse();
});

test('it bulk deletes selected pending jobs', function () {
    $id1 = seedPendingJob('App\\Jobs\\JobOne');
    $id2 = seedPendingJob('App\\Jobs\\JobTwo');

    $this->actingAs($this->fullUser);

    Livewire::test('queue::pending-job-list')
        ->set('selected', [$id1, $id2])
        ->call('deleteSelected')
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed')
        ->assertSet('selected', []);

    expect(DB::table('jobs')->whereIn('id', [$id1, $id2])->count())->toBe(0);
});

test('it clears only the targeted queue', function () {
    seedPendingJob('App\\Jobs\\JobOne', 'emails');
    seedPendingJob('App\\Jobs\\JobTwo', 'exports');

    $this->actingAs($this->fullUser);

    Livewire::test('queue::pending-job-list')
        ->call('clearQueue', 'emails')
        ->assertDispatched('notify')
        ->assertDispatched('synapse-confirmed');

    expect(DB::table('jobs')->where('queue', 'emails')->count())->toBe(0);
    expect(DB::table('jobs')->where('queue', 'exports')->count())->toBe(1);
});

test('read only user cannot delete a pending job', function () {
    $id = seedPendingJob();

    $this->actingAs($this->readOnlyUser);

    Livewire::test('queue::pending-job-list')
        ->call('delete', $id)
        ->assertDispatched('notify');

    expect(DB::table('jobs')->where('id', $id)->exists())->toBeTrue();
});

test('it shows the driver warning and hides actions when queue driver is not database', function () {
    config(['queue.default' => 'redis']);

    $this->actingAs($this->fullUser);

    Livewire::test('queue::pending-job-list')
        ->assertOk()
        ->assertSee(__('queue::labels.pending_driver_warning', ['driver' => 'redis']))
        ->assertDontSee(__('queue::labels.clear_queue'));
});

test('service methods refuse to run against a non-database queue driver even when called directly', function () {
    $jobId = seedPendingJob();
    config(['queue.default' => 'redis']);

    $service = app(PendingJobService::class);

    expect(fn () => $service->list())->toThrow(RuntimeException::class);
    expect(fn () => $service->queueNames())->toThrow(RuntimeException::class);
    expect(fn () => $service->delete($jobId))->toThrow(RuntimeException::class);
    expect(fn () => $service->clearQueue('default'))->toThrow(RuntimeException::class);
    expect(fn () => $service->count())->toThrow(RuntimeException::class);
});
