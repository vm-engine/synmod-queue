<div x-data="{ pageName: 'Worker Control', isHome: false }">
    @include('synapps::components.layouts.partials.loader')
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <!-- Confirmation Dialog -->
    <x-synapse-confirm-dialog />

    <!-- OTP verification for the restart action (must sit outside modals to avoid z-index conflicts) -->
    <livewire:synauth-otp-verify-action />

    <div class="space-y-5 sm:space-y-6">
        <x-synapse-panel :title="__('queue::labels.worker_control')">
            <div class="syn-panel-body space-y-4 px-5 py-4 sm:px-6 sm:py-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('queue::labels.current_driver') }}
                    </span>
                    <span class="font-mono text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ $this->currentDriver }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('queue::labels.last_restarted_at') }}
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                        @if ($this->lastRestartedAt)
                            {{ $this->lastRestartedAt->diffForHumans() }}
                            ({{ $this->lastRestartedAt->toDateTimeString() }})
                        @else
                            {{ __('queue::labels.never_restarted') }}
                        @endif
                    </span>
                </div>

                <x-synapse-alert
                    type="info"
                    :message="__('queue::labels.supervisor_note')"
                    :dismissible="false"
                />

                @canAccess('queue.worker.update')
                <div>
                    <button
                        class="btn danger"
                        type="button"
                        data-title="{{ __('queue::labels.confirm_restart_title') }}"
                        data-message="{{ __('queue::labels.confirm_restart_message') }}"
                        data-confirm="{{ __('queue::labels.confirm_restart_confirm') }}"
                        data-cancel="{{ __('queue::labels.confirm_restart_cancel') }}"
                        data-component="{{ $this->getId() }}"
                        @click="$dispatch('confirm-dialog', {
                            title: $el.dataset.title,
                            message: $el.dataset.message,
                            confirmText: $el.dataset.confirm,
                            cancelText: $el.dataset.cancel,
                            confirmColor: 'danger',
                            icon: 'fa-solid fa-power-off',
                            wireMethod: 'restart',
                            wireParams: [],
                            wireComponent: $el.dataset.component
                        })"
                    >
                        <span class="fa-solid fa-power-off"></span>
                        {{ __('queue::labels.restart_workers') }}
                    </button>
                </div>
                @endcanAccess
            </div>
        </x-synapse-panel>
    </div>
</div>
