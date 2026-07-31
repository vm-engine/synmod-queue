<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use VmEngine\Queue\Services\FailedJobService;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;
use VmEngine\Synapse\Traits\RemembersQueryParams;

new class extends Component
{
    use RemembersQueryParams;
    use WithPagination;

    #[Url()]
    public string $q = '';

    #[Url()]
    public string $sort = 'failed_at';

    #[Url()]
    public string $sortDirection = 'desc';

    #[Url()]
    public int $limit = 15;

    /** @var array<int, string> */
    public array $selected = [];

    /**
     * @return array<int, string>
     */
    protected function rememberedParams(): array
    {
        return ['q', 'sort', 'sortDirection', 'limit'];
    }

    protected function rememberKey(): string
    {
        return 'synapse:query_params:queue.failed-job-list';
    }

    public function mount(): void
    {
        synav()->setActiveMenu('queue.failed');
    }

    public function title(): string
    {
        return __('queue::labels.failed_jobs_list');
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

    public function select(string $uuid): void
    {
        if (in_array($uuid, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$uuid]));
        } else {
            $this->selected[] = $uuid;
        }
    }

    public function selectAll(): void
    {
        $this->selected = $this->failedJobs->pluck('uuid')->toArray();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function retry(string $uuid): void
    {
        if (! auth()->user()?->can('queue.failed.update')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.job_retry_failed'));

            return;
        }

        app(FailedJobService::class)->retry($uuid);

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.job_retried'));

        unset($this->failedJobs);
    }

    public function delete(string $uuid): void
    {
        if (! auth()->user()?->can('queue.failed.delete')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.job_delete_failed'));

            return;
        }

        app(FailedJobService::class)->delete($uuid);

        $this->selected = array_values(array_diff($this->selected, [$uuid]));

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.job_deleted'));

        unset($this->failedJobs);

        $lastPage = $this->failedJobs->lastPage();
        if ($this->getPage() > $lastPage) {
            $this->setPage(max($lastPage, 1));
            unset($this->failedJobs);
        }
    }

    public function retrySelected(): void
    {
        if (! auth()->user()?->can('queue.failed.update')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.job_retry_failed'));

            return;
        }

        if (empty($this->selected)) {
            return;
        }

        $count = count($this->selected);

        app(FailedJobService::class)->retry($this->selected);

        $this->selected = [];

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.jobs_retried', ['count' => $count]));

        unset($this->failedJobs);
    }

    public function deleteSelected(): void
    {
        if (! auth()->user()?->can('queue.failed.delete')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.job_delete_failed'));

            return;
        }

        if (empty($this->selected)) {
            return;
        }

        $count = count($this->selected);
        $service = app(FailedJobService::class);

        foreach ($this->selected as $uuid) {
            $service->delete($uuid);
        }

        $this->selected = [];

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.jobs_deleted', ['count' => $count]));

        unset($this->failedJobs);

        $lastPage = $this->failedJobs->lastPage();
        if ($this->getPage() > $lastPage) {
            $this->setPage(max($lastPage, 1));
            unset($this->failedJobs);
        }
    }

    public function flushAll(): void
    {
        if (! auth()->user()?->can('queue.failed.delete')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.jobs_flush_failed'));

            return;
        }

        app(FailedJobService::class)->flushAll();

        $this->selected = [];

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.jobs_flushed'));

        unset($this->failedJobs);
        $this->resetPage();
    }

    #[Computed()]
    public function failedJobs()
    {
        return app(FailedJobService::class)->list($this->q, $this->sort, $this->sortDirection, $this->limit);
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: __('queue::labels.failed_jobs_list'),
            icon: 'fa-solid fa-triangle-exclamation',
        );
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};
