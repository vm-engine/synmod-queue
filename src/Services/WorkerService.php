<?php

declare(strict_types=1);

namespace VmEngine\Queue\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use VmEngine\SynAuth\Facades\SynAuth;

class WorkerService
{
    /**
     * Signal all queue workers to gracefully restart.
     */
    public function restart(): void
    {
        Artisan::call('queue:restart');

        $this->log(auth()->id(), 'queue.worker.restart', 'Signaled queue workers to restart', 'worker');
    }

    /**
     * Timestamp of the last restart signal, if any.
     */
    public function lastRestartedAt(): ?Carbon
    {
        $timestamp = Cache::get('illuminate:queue:restart');

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    /**
     * The current default queue driver.
     */
    public function currentDriver(): string
    {
        return (string) config('queue.default');
    }

    /**
     * Write a worker activity entry to the auth activity log.
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
