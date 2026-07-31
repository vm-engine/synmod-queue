<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Queue\Services\WorkerService;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;

new class extends Component
{
    public function mount(): void
    {
        synav()->setActiveMenu('backend.queue.worker');
    }

    public function title(): string
    {
        return __('queue::labels.worker_control');
    }

    public function restart(): void
    {
        if (! auth()->user()?->can('queue.worker.update')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.workers_restart_failed'));

            return;
        }

        app(WorkerService::class)->restart();

        unset($this->lastRestartedAt);

        $this->dispatch('synapse-confirmed');
        $this->dispatch('notify', variant: 'success', title: __('queue::labels.actions'), message: __('queue::labels.workers_restarted'));
    }

    #[Computed()]
    public function currentDriver(): string
    {
        return app(WorkerService::class)->currentDriver();
    }

    #[Computed()]
    public function lastRestartedAt(): ?Carbon
    {
        return app(WorkerService::class)->lastRestartedAt();
    }

    #[Computed()]
    public function breadcrumbs(): Breadcrumbs
    {
        return Breadcrumbs::make(
            label: __('queue::labels.worker_control'),
            icon: 'fa-solid fa-power-off',
        );
    }

    public function render()
    {
        return $this->view()->title(page_title($this->title()));
    }
};
