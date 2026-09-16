<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Queue\Services\PendingJobService;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;
use VmEngine\Synapse\Traits\RemembersQueryParams;

new class extends Component
{
    use RemembersQueryParams;
    use WithPagination;

    #[Url()]
    public string $q = '';

    #[Url()]
    public string $filterQueue = '';

    #[Url()]
    public string $sort = 'created_at';

    #[Url()]
    public string $sortDirection = 'desc';

    #[Url()]
    public int $perPage = 15;

    /** @var array<int, int> */
    public array $selected = [];

    /**
     * @return array<int, string>
     */
    protected function rememberedParams(): array
    {
        return ['q', 'filterQueue', 'sort', 'sortDirection', 'perPage'];
    }

    protected function rememberKey(): string
    {
        return 'synapse:query_params:queue.pending-job-list';
    }

    public function mount(): void
    {
        synav()->setActiveMenu('queue.pending');
    }

    public function title(): string
    {
        return __('queue::labels.pending_jobs_list');
    }

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function sortData(string $sort): void
    {
        if ($this->sort === $sort && $this->sortDirection === 'asc') {
            $this->sortDirection = 'desc';
        } elseif ($this->sort === $sort && $this->sortDirection === 'desc') {
            $this->reset('sort', 'sortDirection');

            return;
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sort = $sort;
    }

    public function select(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    public function selectAll(): void
    {
        if (! $this->isDatabaseDriver()) {
            return;
        }

        $this->selected = $this->pendingJobs->pluck('id')->toArray();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function delete(int $id): void
    {
        if (! auth()->user()?->can('queue.pending.delete')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.pending_job_delete_failed'));

            return;
        }

        app(PendingJobService::class)->delete($id);

        $this->selected = array_values(array_diff($this->selected, [$id]));

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.pending_job_deleted'));

        unset($this->pendingJobs);

        $lastPage = $this->pendingJobs->lastPage();
        if ($this->getPage() > $lastPage) {
            $this->setPage(max($lastPage, 1));
            unset($this->pendingJobs);
        }
    }

    public function deleteSelected(): void
    {
        if (! auth()->user()?->can('queue.pending.delete')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.pending_job_delete_failed'));

            return;
        }

        if (empty($this->selected)) {
            return;
        }

        $count = count($this->selected);
        $service = app(PendingJobService::class);

        foreach ($this->selected as $id) {
            $service->delete($id);
        }

        $this->selected = [];

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.pending_jobs_deleted', ['count' => $count]));

        unset($this->pendingJobs);

        $lastPage = $this->pendingJobs->lastPage();
        if ($this->getPage() > $lastPage) {
            $this->setPage(max($lastPage, 1));
            unset($this->pendingJobs);
        }
    }

    public function clearQueue(string $queue): void
    {
        if (! auth()->user()?->can('queue.pending.delete')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.queue_clear_failed'));

            return;
        }

        app(PendingJobService::class)->clearQueue($queue);

        $this->selected = [];

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.queue_cleared'));

        unset($this->pendingJobs);
        $this->resetPage();
    }

    #[Computed()]
    public function isDatabaseDriver(): bool
    {
        return app(PendingJobService::class)->isDatabaseDriver();
    }

    #[Computed()]
    public function pendingJobs()
    {
        return app(PendingJobService::class)->list($this->q, $this->filterQueue ?: null, $this->sort, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, string>
     */
    #[Computed()]
    public function queueNames(): array
    {
        return app(PendingJobService::class)->queueNames();
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: __('queue::labels.pending_jobs_list'),
            icon: 'fa-solid fa-hourglass-half',
        );
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};
