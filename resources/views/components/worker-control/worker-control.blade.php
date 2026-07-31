<div x-data="{ pageName: 'Worker Control', isHome: false }">
    @include('synapps::components.layouts.partials.loader')
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <!-- Confirmation Dialog -->
    <x-synapse-confirm-dialog />

    <div class="space-y-5 sm:space-y-6">
        <div class="dark:bg-white/3 rounded-2xl border border-gray-200 bg-white dark:border-gray-800">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    {{ __('queue::labels.worker_control') }}
                </h3>
            </div>

            <div class="space-y-4 border-t border-gray-100 px-5 py-4 sm:px-6 sm:py-5 dark:border-gray-800">
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

                <div
                    class="flex items-start gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-200"
                    role="alert"
                >
                    <span class="fa-solid fa-circle-info mt-0.5"></span>
                    <p class="text-sm">
                        {{ __('queue::labels.supervisor_note') }}
                    </p>
                </div>

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
        </div>
    </div>
</div>
