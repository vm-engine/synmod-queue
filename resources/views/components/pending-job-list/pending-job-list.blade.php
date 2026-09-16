<div x-data="{ pageName: 'Pending Jobs', isHome: false }">
    @include('synapps::components.layouts.partials.loader')
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    @if (!$this->isDatabaseDriver)
        <div class="space-y-5 sm:space-y-6">
            <x-synapse-alert
                type="warning"
                :message="__('queue::labels.pending_driver_warning', ['driver' => config('queue.default')])"
                :dismissible="false"
            />
        </div>
    @else
        <!-- Confirmation Dialog -->
        <x-synapse-confirm-dialog />

        <div class="space-y-5 sm:space-y-6">
            <x-synapse-panel :title="__('queue::labels.pending_jobs_list')">
                <x-slot:toolbar>
                    @canAccess('queue.pending.delete')
                    <button
                        class="btn danger"
                        type="button"
                        data-title="{{ __('queue::labels.confirm_clear_queue_title') }}"
                        data-message="{{ __('queue::labels.confirm_clear_queue_message') }}"
                        data-confirm="{{ __('queue::labels.confirm_clear_queue_confirm') }}"
                        data-cancel="{{ __('queue::labels.confirm_clear_queue_cancel') }}"
                        data-component="{{ $this->getId() }}"
                        data-queue="{{ $filterQueue }}"
                        @disabled($filterQueue === '')
                        @click="$dispatch('confirm-dialog', {
                            title: $el.dataset.title,
                            message: $el.dataset.message,
                            confirmText: $el.dataset.confirm,
                            cancelText: $el.dataset.cancel,
                            confirmColor: 'danger',
                            icon: 'fa-solid fa-broom',
                            wireMethod: 'clearQueue',
                            wireParams: [$el.dataset.queue],
                            wireComponent: $el.dataset.component
                        })"
                    >
                        <span class="fa-solid fa-broom"></span>
                        {{ __('queue::labels.clear_queue') }}
                    </button>
                    @endcanAccess
                    <select
                        class="shadow-theme-sm rounded-sm p-2"
                        id="filterQueue"
                        name="filterQueue"
                        wire:model.live="filterQueue"
                    >
                        <option value="">{{ __('queue::labels.filter') }}: {{ __('queue::labels.queue') }}
                        </option>
                        @foreach ($this->queueNames as $queueName)
                            <option value="{{ $queueName }}">{{ $queueName }}</option>
                        @endforeach
                    </select>
                    <x-synapse-search-box
                        id="q"
                        name="q"
                        :placeholder="__('queue::labels.search')"
                        wire:model.live.debounce="q"
                    />
                </x-slot:toolbar>

                <div class="syn-selection-bar">
                    <span
                        class="text-sm text-gray-600 dark:text-gray-300">{{ __('queue::labels.items_selected', ['count' => count($selected)]) }}</span>
                    @canAccess('queue.pending.delete')
                    <button
                        class="btn danger sm"
                        type="button"
                        data-title="{{ __('queue::labels.confirm_delete_pending_selected_title') }}"
                        data-message="{{ __('queue::labels.confirm_delete_pending_selected_message', ['count' => count($selected)]) }}"
                        data-confirm="{{ __('queue::labels.confirm_delete_confirm') }}"
                        data-cancel="{{ __('queue::labels.confirm_delete_cancel') }}"
                        data-component="{{ $this->getId() }}"
                        @disabled(count($selected) === 0)
                        @click="$dispatch('confirm-dialog', {
                            title: $el.dataset.title,
                            message: $el.dataset.message,
                            confirmText: $el.dataset.confirm,
                            cancelText: $el.dataset.cancel,
                            confirmColor: 'danger',
                            icon: 'fa-solid fa-trash',
                            wireMethod: 'deleteSelected',
                            wireParams: [],
                            wireComponent: $el.dataset.component
                        })"
                    >
                        {{ __('queue::labels.delete_selected') }}
                    </button>
                    @endcanAccess
                    <button
                        class="btn sm"
                        type="button"
                        wire:click="clearSelection"
                        @disabled(count($selected) === 0)
                    >
                        {{ __('queue::labels.clear_selection') }}
                    </button>
                </div>

                <div class="syn-panel-body">
                    <div class="max-w-full overflow-x-auto">
                        <table
                            class="datatable min-w-full"
                            width="100%"
                            cellspacing="0"
                        >
                            <thead>
                                <tr>
                                    <th>
                                        <input
                                            type="checkbox"
                                            @change="$event.target.checked ? $wire.selectAll() : $wire.clearSelection()"
                                            :checked="{{ count($selected) }} > 0 && {{ count($selected) }} ===
                                                {{ $this->pendingJobs->count() }}"
                                        >
                                    </th>
                                    <th>
                                        <p>{{ __('queue::labels.no') }}</p>
                                    </th>
                                    <th>
                                        <p>{{ __('queue::labels.job_class') }}</p>
                                    </th>
                                    <th class="sortable">
                                        <div>
                                            <p>{{ __('queue::labels.queue') }}</p>
                                            <a
                                                href="#"
                                                wire:click.prevent="sortData('queue')"
                                            >
                                                <span
                                                    class="fa fa-solid {{ $sort != 'queue' ? 'fa-sort' : ($sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down') }}"
                                                ></span>
                                            </a>
                                        </div>
                                    </th>
                                    <th>
                                        <p>{{ __('queue::labels.attempts') }}</p>
                                    </th>
                                    <th class="sortable">
                                        <div>
                                            <p>{{ __('queue::labels.available_at') }}</p>
                                            <a
                                                href="#"
                                                wire:click.prevent="sortData('available_at')"
                                            >
                                                <span
                                                    class="fa fa-solid {{ $sort != 'available_at' ? 'fa-sort' : ($sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down') }}"
                                                ></span>
                                            </a>
                                        </div>
                                    </th>
                                    <th class="sortable">
                                        <div>
                                            <p>{{ __('queue::labels.created_at') }}</p>
                                            <a
                                                href="#"
                                                wire:click.prevent="sortData('created_at')"
                                            >
                                                <span
                                                    class="fa fa-solid {{ $sort != 'created_at' ? 'fa-sort' : ($sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down') }}"
                                                ></span>
                                            </a>
                                        </div>
                                    </th>
                                    <th>
                                        <p>{{ __('queue::labels.actions') }}</p>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @forelse ($this->pendingJobs as $job)
                                    <tr wire:key="pending-job-{{ $job->id }}">
                                        <td>
                                            <input
                                                type="checkbox"
                                                wire:click="select({{ $job->id }})"
                                                @checked(in_array($job->id, $selected, true))
                                            >
                                        </td>
                                        <td>
                                            <p>{{ ($this->pendingJobs->currentPage() - 1) * $perPage + $i }}</p>
                                        </td>
                                        <td>
                                            <p class="font-mono text-sm">{{ $job->display_name }}</p>
                                        </td>
                                        <td>
                                            <p>{{ $job->queue }}</p>
                                        </td>
                                        <td>
                                            <p>{{ $job->attempts }}</p>
                                        </td>
                                        <td>
                                            <p>{{ $job->available_at }}</p>
                                        </td>
                                        <td>
                                            <p>{{ $job->created_at }}</p>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                @canAccess('queue.pending.delete')
                                                <button
                                                    class="btn-icon danger has-tooltip group"
                                                    type="button"
                                                    data-id="{{ $job->id }}"
                                                    data-title="{{ __('queue::labels.confirm_delete_pending_title') }}"
                                                    data-message="{{ __('queue::labels.confirm_delete_pending_message', ['name' => $job->display_name, 'queue' => $job->queue]) }}"
                                                    data-confirm="{{ __('queue::labels.confirm_delete_confirm') }}"
                                                    data-cancel="{{ __('queue::labels.confirm_delete_cancel') }}"
                                                    data-component="{{ $this->getId() }}"
                                                    @click="$dispatch('confirm-dialog', {
                                                        title: $el.dataset.title,
                                                        message: $el.dataset.message,
                                                        confirmText: $el.dataset.confirm,
                                                        cancelText: $el.dataset.cancel,
                                                        confirmColor: 'danger',
                                                        icon: 'fa-solid fa-trash',
                                                        wireMethod: 'delete',
                                                        wireParams: [{{ $job->id }}],
                                                        wireComponent: $el.dataset.component
                                                    })"
                                                >
                                                    <span class="fa-solid fa-trash"></span>
                                                    <span class="tooltip">{{ __('queue::labels.delete') }}</span>
                                                </button>
                                                @endcanAccess
                                            </div>
                                        </td>
                                    </tr>
                                    @php $i++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('queue::labels.no_pending_jobs') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <x-synapse-per-page-selector :paginator="$this->pendingJobs" />
            </x-synapse-panel>
        </div>
    @endif
</div>
