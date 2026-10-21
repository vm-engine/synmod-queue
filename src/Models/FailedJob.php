<?php

declare(strict_types=1);

namespace VmEngine\Queue\Models;

use Illuminate\Database\Eloquent\Model;
use VmEngine\Synapse\Traits\WithDeleteToken;

/**
 * A row of Laravel's `failed_jobs` table. Only issues and checks delete tokens, which carry
 * the job's uuid; rows are still listed through FailedJobService and deleted via queue:forget.
 */
class FailedJob extends Model
{
    use WithDeleteToken;

    public $timestamps = false;

    public function getTable(): string
    {
        return (string) config('queue.failed.table', 'failed_jobs');
    }
}
