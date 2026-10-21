<?php

declare(strict_types=1);

namespace VmEngine\Queue\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use VmEngine\Queue\Models\PendingJob;
use VmEngine\SynAuth\Facades\SynAuth;

class PendingJobService
{
    /**
     * Sortable columns whitelist (protects against arbitrary orderBy calls).
     *
     * @var array<int, string>
     */
    private const SORTABLE_COLUMNS = ['id', 'queue', 'available_at', 'created_at'];

    /**
     * Whether the current queue default connection is the database driver.
     */
    public function isDatabaseDriver(): bool
    {
        return config('queue.default') === 'database';
    }

    /**
     * List pending (jobs table) records with optional search, queue filter, and sorting.
     *
     * @return LengthAwarePaginator<int, \stdClass>
     */
    public function list(?string $search = null, ?string $queueFilter = null, string $sort = 'id', string $sortDirection = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        $this->ensureDatabaseDriver();

        $query = DB::connection(config('queue.connections.database.connection'))
            ->table(config('queue.connections.database.table', 'jobs'));

        if ($queueFilter !== null && $queueFilter !== '') {
            $query->where('queue', $queueFilter);
        }

        if ($search !== null && strlen($search) > 2) {
            $query->where(function ($q) use ($search): void {
                $q->where('payload', 'like', "%{$search}%")
                    ->orWhere('queue', 'like', "%{$search}%");
            });
        }

        $column = in_array($sort, self::SORTABLE_COLUMNS, true) ? $sort : 'id';
        $direction = $sortDirection === 'asc' ? 'asc' : 'desc';

        $paginator = $query->orderBy($column, $direction)->paginate($perPage);

        $tokens = new PendingJob;

        $paginator->getCollection()->transform(function (\stdClass $row) use ($tokens): \stdClass {
            $row->display_name = $this->displayName($row->payload ?? null);
            $row->delete_token = $tokens->generateDeleteToken($row->id);

            return $row;
        });

        return $paginator;
    }

    /**
     * Distinct queue names, for filter dropdowns.
     *
     * @return array<int, string>
     */
    public function queueNames(): array
    {
        $this->ensureDatabaseDriver();

        return DB::connection(config('queue.connections.database.connection'))
            ->table(config('queue.connections.database.table', 'jobs'))
            ->distinct()
            ->pluck('queue')
            ->all();
    }

    /**
     * Delete a single pending job row.
     */
    public function delete(int $id): void
    {
        $this->ensureDatabaseDriver();

        DB::connection(config('queue.connections.database.connection'))
            ->table(config('queue.connections.database.table', 'jobs'))
            ->where('id', $id)
            ->delete();

        $this->log(auth()->id(), 'queue.pending.delete', 'Deleted pending job #'.$id, 'pending');
    }

    /**
     * Clear all pending jobs from a given queue.
     */
    public function clearQueue(string $queue): void
    {
        $this->ensureDatabaseDriver();

        Artisan::call('queue:clear', [
            'connection' => 'database',
            '--queue' => $queue,
            '--force' => true,
        ]);

        $this->log(auth()->id(), 'queue.pending.clear', 'Cleared queue: '.$queue, 'pending');
    }

    /**
     * Total count of pending jobs.
     */
    public function count(): int
    {
        $this->ensureDatabaseDriver();

        return DB::connection(config('queue.connections.database.connection'))
            ->table(config('queue.connections.database.table', 'jobs'))
            ->count();
    }

    /**
     * Guard against querying/mutating the jobs table on a non-database queue driver,
     * regardless of whether the caller already checked isDatabaseDriver() itself.
     */
    private function ensureDatabaseDriver(): void
    {
        if (! $this->isDatabaseDriver()) {
            throw new RuntimeException('Pending job management requires the database queue driver.');
        }
    }

    /**
     * Decode a job payload defensively and extract a display name.
     */
    private function displayName(?string $payload): string
    {
        if ($payload === null) {
            return 'Unknown';
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return 'Unknown';
        }

        if (isset($decoded['displayName']) && is_string($decoded['displayName'])) {
            return $decoded['displayName'];
        }

        $data = $decoded['data'] ?? null;
        if (is_array($data) && isset($data['commandName']) && is_string($data['commandName'])) {
            return $data['commandName'];
        }

        if (isset($decoded['job']) && is_string($decoded['job'])) {
            return $decoded['job'];
        }

        return 'Unknown';
    }

    /**
     * Write a pending-job activity entry to the auth activity log.
     *
     * Skipped when no user context is available.
     */
    private function log(int|string|null $userId, string $action, string $description, string $feature): void
    {
        if ($userId === null) {
            return;
        }

        SynAuth::logActivity((int) $userId, $action, $description, $feature, 'queue');
    }
}
