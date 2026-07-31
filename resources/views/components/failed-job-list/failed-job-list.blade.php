<div x-data="{ pageName: 'Failed Jobs', isHome: false }">
    @include('synapps::components.layouts.partials.loader')
    @include('synapps::components.layouts.partials.breadcrumbs', ['breadcrumbs' => $this->breadcrumbs])

    <!-- Confirmation Dialog -->
    <x-synapse-confirm-dialog />

    <div class="space-y-5 sm:space-y-6">
        <div class="dark:bg-white/3 rounded-2xl border border-gray-200 bg-white dark:border-gray-800">
            <div class="flex justify-between">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        {{ __('queue::labels.failed_jobs_list') }}
                    </h3>
                </div>
                <div class="my-auto flex gap-3 px-5">
                    @canAccess('queue.failed.delete')
                    <button
                        class="btn danger"
                        type="button"
                        data-title="{{ __('queue::labels.confirm_flush_all_title') }}"
                        data-message="{{ __('queue::labels.confirm_flush_all_message') }}"
                        data-confirm="{{ __('queue::labels.confirm_flush_all_confirm') }}"
                        data-cancel="{{ __('queue::labels.confirm_flush_all_cancel') }}"
                        data-component="{{ $this->getId() }}"
                        @click="$dispatch('confirm-dialog', {
                            title: $el.dataset.title,
                            message: $el.dataset.message,
                            confirmText: $el.dataset.confirm,
                            cancelText: $el.dataset.cancel,
                            confirmColor: 'danger',
                            icon: 'fa-solid fa-trash',
                            wireMethod: 'flushAll',
                            wireParams: [],
                            wireComponent: $el.dataset.component
                        })"
                    >
                        <span class="fa-solid fa-trash"></span>
                        {{ __('queue::labels.flush_all') }}
                    </button>
                    @endcanAccess
                    <div class="input-group">
                        <input
                            class="form-input p-2"
                            id="q"
                            name="q"
                            type="text"
                            placeholder="{{ __('queue::labels.search') }}"
                            wire:model.live.debounce="q"
                        >
                    </div>
                </div>
            </div>

            @if (count($selected) > 0)
                <div
                    class="flex items-center gap-3 border-t border-gray-100 bg-gray-50 px-5 py-3 dark:border-gray-800 dark:bg-white/5">
                    <span
                        class="text-sm text-gray-600 dark:text-gray-300">{{ __('queue::labels.items_selected', ['count' => count($selected)]) }}</span>
                    @canAccess('queue.failed.update')
                    <button
                        class="btn primary sm"
                        type="button"
                        data-title="{{ __('queue::labels.confirm_retry_selected_title') }}"
                        data-message="{{ __('queue::labels.confirm_retry_selected_message', ['count' => count($selected)]) }}"
                        data-confirm="{{ __('queue::labels.confirm_retry_confirm') }}"
                        data-cancel="{{ __('queue::labels.confirm_retry_cancel') }}"
                        data-component="{{ $this->getId() }}"
                        @click="$dispatch('confirm-dialog', {
                            title: $el.dataset.title,
                            message: $el.dataset.message,
                            confirmText: $el.dataset.confirm,
                            cancelText: $el.dataset.cancel,
                            confirmColor: 'primary',
                            icon: 'fa-solid fa-rotate-right',
                            wireMethod: 'retrySelected',
                            wireParams: [],
                            wireComponent: $el.dataset.component
                        })"
                    >
                        {{ __('queue::labels.retry_selected') }}
                    </button>
                    @endcanAccess
                    @canAccess('queue.failed.delete')
                    <button
                        class="btn danger sm"
                        type="button"
                        data-title="{{ __('queue::labels.confirm_delete_selected_title') }}"
                        data-message="{{ __('queue::labels.confirm_delete_selected_message', ['count' => count($selected)]) }}"
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
                    >
                        {{ __('queue::labels.clear_selection') }}
                    </button>
                </div>
            @endif

            <div class="border-t border-gray-100 dark:border-gray-800">
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
                                            {{ $this->failedJobs->count() }}"
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
                                <th class="sortable">
                                    <div>
                                        <p>{{ __('queue::labels.failed_at') }}</p>
                                        <a
                                            href="#"
                                            wire:click.prevent="sortData('failed_at')"
                                        >
                                            <span
                                                class="fa fa-solid {{ $sort != 'failed_at' ? 'fa-sort' : ($sortDirection == 'asc' ? 'fa-sort-up' : 'fa-sort-down') }}"
                                            ></span>
                                        </a>
                                    </div>
                                </th>
                                <th>
                                    <p>{{ __('queue::labels.exception') }}</p>
                                </th>
                                <th>
                                    <p>{{ __('queue::labels.actions') }}</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1; @endphp
                            @forelse ($this->failedJobs as $job)
                                <tr wire:key="failed-job-{{ $job->uuid }}">
                                    <td>
                                        <input
                                            type="checkbox"
                                            wire:click="select('{{ $job->uuid }}')"
                                            @checked(in_array($job->uuid, $selected, true))
                                        >
                                    </td>
                                    <td>
                                        <p>{{ ($this->failedJobs->currentPage() - 1) * $limit + $i }}</p>
                                    </td>
                                    <td>
                                        <p class="font-mono text-sm">{{ $job->display_name }}</p>
                                    </td>
                                    <td>
                                        <p>{{ $job->queue }}</p>
                                    </td>
                                    <td>
                                        <p>{{ $job->failed_at }}</p>
                                    </td>
                                    <td>
                                        <details>
                                            <summary
                                                class="cursor-pointer text-sm text-gray-500"
                                                title="{{ $job->exception }}"
                                            >
                                                {{ \Illuminate\Support\Str::limit($job->exception, 150) }}
                                            </summary>
                                            <pre class="mt-2 max-h-64 overflow-auto whitespace-pre-wrap text-xs">{{ $job->exception }}</pre>
                                        </details>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            @canAccess('queue.failed.update')
                                            <button
                                                class="btn-icon primary has-tooltip group"
                                                type="button"
                                                data-uuid="{{ $job->uuid }}"
                                                data-title="{{ __('queue::labels.confirm_retry_title') }}"
                                                data-message="{{ __('queue::labels.confirm_retry_message', ['name' => $job->display_name]) }}"
                                                data-confirm="{{ __('queue::labels.confirm_retry_confirm') }}"
                                                data-cancel="{{ __('queue::labels.confirm_retry_cancel') }}"
                                                data-component="{{ $this->getId() }}"
                                                @click="$dispatch('confirm-dialog', {
                                                    title: $el.dataset.title,
                                                    message: $el.dataset.message,
                                                    confirmText: $el.dataset.confirm,
                                                    cancelText: $el.dataset.cancel,
                                                    confirmColor: 'primary',
                                                    icon: 'fa-solid fa-rotate-right',
                                                    wireMethod: 'retry',
                                                    wireParams: [$el.dataset.uuid],
                                                    wireComponent: $el.dataset.component
                                                })"
                                            >
                                                <span class="fa-solid fa-rotate-right"></span>
                                                <span class="tooltip">{{ __('queue::labels.retry') }}</span>
                                            </button>
                                            @endcanAccess
                                            @canAccess('queue.failed.delete')
                                            <button
                                                class="btn-icon danger has-tooltip group"
                                                type="button"
                                                data-uuid="{{ $job->uuid }}"
                                                data-title="{{ __('queue::labels.confirm_delete_title') }}"
                                                data-message="{{ __('queue::labels.confirm_delete_message', ['name' => $job->display_name]) }}"
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
                                                    wireParams: [$el.dataset.uuid],
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
                                    <td colspan="7">
                                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('queue::labels.no_failed_jobs') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex gap-2 p-3">
                <div class="flex-none p-1">
                    <select
                        class="shadow-theme-sm rounded-sm p-1"
                        id="limit"
                        name="limit"
                        wire:model.live="limit"
                    >
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="pagination-container flex-1 grow">
                    {{ $this->failedJobs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
