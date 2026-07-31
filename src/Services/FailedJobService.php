<?php

declare(strict_types=1);

namespace VmEngine\Queue\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use VmEngine\SynAuth\Facades\SynAuth;

class FailedJobService
{
    /**
     * Sortable columns whitelist (protects against arbitrary orderBy calls).
     *
     * @var array<int, string>
     */
    private const SORTABLE_COLUMNS = ['failed_at', 'queue', 'connection'];

    /**
     * List failed jobs with optional search and sorting.
     */
    public function list(?string $search = null, string $sort = 'failed_at', string $sortDirection = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::connection(config('queue.failed.database'))
            ->table(config('queue.failed.table', 'failed_jobs'));

        if ($search !== null && strlen($search) > 2) {
            $query->where(function ($q) use ($search): void {
                $q->where('payload', 'like', "%{$search}%")
                    ->orWhere('queue', 'like', "%{$search}%");
            });
        }

        $column = in_array($sort, self::SORTABLE_COLUMNS, true) ? $sort : 'failed_at';
        $direction = $sortDirection === 'asc' ? 'asc' : 'desc';

        $paginator = $query->orderBy($column, $direction)->paginate($perPage);

        $paginator->getCollection()->transform(function (object $row): object {
            $row->display_name = $this->displayName($row->payload ?? null);

            return $row;
        });

        return $paginator;
    }

    /**
     * Retry one or more failed jobs by UUID.
     *
     * @param  string|array<int, string>  $uuid
     */
    public function retry(string|array $uuid): void
    {
        $ids = (array) $uuid;

        Artisan::call('queue:retry', ['id' => $ids]);

        $this->log(auth()->id(), 'queue.failed.retry', 'Retried failed job(s): '.implode(', ', $ids), 'failed');
    }

    /**
     * Permanently delete a single failed job.
     */
    public function delete(string $uuid): void
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        $this->log(auth()->id(), 'queue.failed.delete', 'Deleted failed job: '.$uuid, 'failed');
    }

    /**
     * Delete all failed jobs.
     */
    public function flushAll(): void
    {
        Artisan::call('queue:flush');

        $this->log(auth()->id(), 'queue.failed.flush', 'Flushed all failed jobs', 'failed');
    }

    /**
     * Total count of failed jobs.
     */
    public function count(): int
    {
        return DB::connection(config('queue.failed.database'))
            ->table(config('queue.failed.table', 'failed_jobs'))
            ->count();
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
     * Write a failed-job activity entry to the auth activity log.
     *
     * Skipped when no user context is available.
     */
    private function log(?int $userId, string $action, string $description, string $feature): void
    {
        if ($userId === null) {
            return;
        }

        SynAuth::logActivity($userId, $action, $description, $feature, 'queue');
    }
}
