<div x-data="dataTable({
    headers: @js($headers),
    searchQuery: '{{ $searchQuery }}',
    sortColumn: '{{ $sortColumn }}',
    sortDirection: '{{ $sortDirection }}'
})">

    <div class="mb-4 sm:mb-6">
        @if ($showBulkActions && $selectedRowsCount > 0)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-bold text-blue-800">{{ $selectedRowsCount }}</span>
                        @if ($selectAll)
                            {{ __('items selected across all pages.') }}
                        @else
                            {{ __('items selected.') }}
                        @endif
                        @if ($selectPage && !$selectAll && $data->total() > $data->count())
                            <button wire:click="selectAll" class="ml-2 text-blue-600 hover:underline focus:outline-none">
                                {{ __('Select all :total items.', ['total' => $data->total()]) }}
                            </button>
                        @endif
                        <button wire:click="clearSelection" class="ml-2 text-red-600 hover:underline focus:outline-none">
                            {{ __('Clear selection.') }}
                        </button>
                    </div>
                    <div>
                        @if ($bulkDeleteAction)
                            <button wire:click="{{ $bulkDeleteAction }}"
                                wire:confirm="{{ __('Are you sure you want to delete the selected items?') }}"
                                class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
                                {{ __('Delete Selected') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:hidden">
            @if ($showSearch)
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="{{ __($searchPlaceholder) }}" value="{{ $searchQuery }}"
                        class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 whitespace-nowrap">{{ __('Show:') }}</label>
                    <select wire:model.live="perPage"
                        class="px-2 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                @if ($showCreate)
                    <x-utils.create-button :route="$this->getCreateRoute($createRoute)" :createButtonName="$createButtonName" />
                @endif
            </div>
        </div>

        <div class="hidden sm:flex sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                @if ($showSearch)
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="{{ __($searchPlaceholder) }}" value="{{ $searchQuery }}"
                            class="w-64 px-4 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">{{ __('Show:') }}</label>
                    <select wire:model.live="perPage"
                        class="px-2 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-600">{{ __('entries') }}</span>
                </div>
            </div>
            @if ($showCreate)
                <x-utils.create-button :route="$createRoute" :createButtonName="$createButtonName" />
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="sm:hidden">
            @forelse($data as $row)
                <div
                    class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors duration-150 {{ in_array($row->id, $selectedRows) ? 'bg-blue-50' : '' }}">
                    <div class="flex">
                        @if ($showBulkActions)
                            <div class="pr-4 flex-shrink-0">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $row->id }}"
                                    class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mt-1">
                            </div>
                        @endif
                        <div class="flex-grow min-w-0">
                            @foreach ($headers as $header)
                                @if ($this->shouldShowOnMobile($header))
                                    <div class="flex items-start py-1 gap-4">
                                        <span
                                            class="text-sm font-medium text-gray-500">{{ __($header['label']) }}:</span>
                                        <span class="flex-1 min-w-0 text-sm text-gray-900 text-right break-words">
                                            @php $value = $this->getHeaderValue($header, $row) @endphp
                                            @include('components.partials.data-table.cell', [
                                                'header' => $header,
                                                'value' => $value,
                                            ])
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @if ($showActions)
                        <div class="flex items-center justify-center gap-2 mt-3 pt-3 border-t border-gray-100">
                            @if ($viewRoute && $this->canViewRow($row))
                                <x-utils.view-button :route="$this->getViewRoute($row)" />
                            @endif
                            @if ($editRoute && $this->canEditRow($row))
                                <x-utils.update-button :route="$this->getEditRoute($row)" />
                            @endif
                            @if ($deleteAction && $this->canDeleteRow($row))
                                <x-utils.delete-button :wireClick="$this->getDeleteAction($row)" />
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                @include('components.partials.data-table.empty')
            @endforelse
        </div>

        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        @if ($showBulkActions)
                            <th scope="col" class="px-3 lg:px-6 py-3">
                                <input type="checkbox" wire:model.live="selectPage"
                                    class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            </th>
                        @endif
                        @foreach ($headers as $header)
                            <th scope="col"
                                class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap"
                                @if (isset($header['sortable']) && $header['sortable']) style="cursor: pointer;"
                            wire:click="sortBy('{{ $header['key'] }}')" @endif>
                                <div class="flex items-center gap-2 hover:text-gray-700 transition-colors">
                                    <span>{{ __($header['label']) }}</span>
                                    @if (isset($header['sortable']) && $header['sortable'])
                                        @include('components.partials.data-table.sort-icon', [
                                            'header' => $header,
                                        ])
                                    @endif
                                </div>
                            </th>
                        @endforeach
                        @if ($showActions)
                            <th scope="col"
                                class="px-3 lg:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Actions') }}
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $row)
                        <tr
                            class="hover:bg-gray-50 transition-colors duration-150 {{ in_array($row->id, $selectedRows) ? 'bg-blue-50' : '' }}">
                            @if ($showBulkActions)
                                <td class="px-3 lg:px-6 py-4">
                                    <input type="checkbox" wire:model.live="selectedRows" value="{{ $row->id }}"
                                        class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                </td>
                            @endif
                            @foreach ($headers as $header)
                                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @include('components.partials.data-table.cell', [
                                        'header' => $header,
                                        'value' => $this->getHeaderValue($header, $row),
                                    ])
                                </td>
                            @endforeach
                            @if ($showActions)
                                <td class="px-3 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        @if ($viewRoute && $this->canViewRow($row))
                                            <x-utils.view-button :route="$this->getViewRoute($row)" />
                                        @endif
                                        @if ($editRoute && $this->canEditRow($row))
                                            <x-utils.update-button :route="$this->getEditRoute($row)" />
                                        @endif
                                        @if ($deleteAction && $this->canDeleteRow($row))
                                            <x-utils.delete-button :wireClick="$this->getDeleteAction($row)" />
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headers) + ($showActions ? 1 : 0) + ($showBulkActions ? 1 : 0) }}"
                                class="text-center py-12">
                                @include('components.partials.data-table.empty')
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->hasPages())
            <div class="p-4 border-t border-gray-200 rounded-b-lg">
                {{ $data->links() }}
            </div>
        @endif
    </div>
</div>
<script>
    function dataTable(config) {
        return {
            headers: config.headers,
            searchQuery: config.searchQuery,
            sortColumn: config.sortColumn,
            sortDirection: config.sortDirection,
            init() {}
        }
    }
</script>
