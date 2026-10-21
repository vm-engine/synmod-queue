<?php

declare(strict_types=1);

namespace VmEngine\Queue\Models;

use Illuminate\Database\Eloquent\Model;
use VmEngine\Synapse\Traits\WithDeleteToken;

/**
 * A row of Laravel's database-queue `jobs` table. Only issues and checks delete tokens;
 * rows are still listed and deleted through PendingJobService's query builder.
 */
class PendingJob extends Model
{
    use WithDeleteToken;

    public $timestamps = false;

    public function getTable(): string
    {
        return (string) config('queue.connections.database.table', 'jobs');
    }
}
