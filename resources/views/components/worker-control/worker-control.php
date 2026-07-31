<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use VmEngine\Queue\Services\WorkerService;
use VmEngine\Synapse\Services\Helper\Breadcrumbs;
use VmEngine\SynAuth\Traits\WithOtpProtection;

new class extends Component
{
    use WithOtpProtection;

    private const OTP_PURPOSE = 'queue.restart-workers';

    public function mount(): void
    {
        synav()->setActiveMenu('queue.worker');
    }

    public function title(): string
    {
        return __('queue::labels.worker_control');
    }

    /**
     * First step: confirm-dialog callback. Closes the confirm-dialog and
     * hands off to OTP verification before the restart actually runs.
     */
    public function restart(): void
    {
        if (! auth()->user()?->can('queue.worker.update')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.workers_restart_failed'));

            return;
        }

        $this->dispatch('synapse-confirmed');
        $this->requireOtp(self::OTP_PURPOSE);
    }

    /**
     * Second step: runs only after the user verifies via the OTP modal
     * dispatched by requireOtp() above.
     */
    #[On('otp-verified')]
    public function handleOtpVerified(string $token, string $purpose): void
    {
        if ($purpose !== self::OTP_PURPOSE) {
            return;
        }

        if (! $this->validateOtpToken($token)) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.otp_invalid'));

            return;
        }

        if (! auth()->user()?->can('queue.worker.update')) {
            $this->dispatch('notify', variant: 'danger', title: __('queue::labels.actions'), message: __('queue::labels.workers_restart_failed'));

            return;
        }

        app(WorkerService::class)->restart();

        unset($this->lastRestartedAt);

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
