<div
        x-data="tableComponent(@js($liveUpdateInterval))"
        @if($liveUpdateInterval) wire:poll.{{ $liveUpdateInterval }}s @endif
        class="{{ $scheme['responsive_classes'] ?? '' }}"
>
    {{-- Header Actions Bar --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        {{-- Left side: Global Search + Filters --}}
        <div class="flex items-center space-x-2 flex-1">
            {{-- Global Search --}}
            <div class="relative flex-1 max-w-md">
                <input
                        type="text"
                        wire:model.live.debounce.300ms="tableSearch"
                        placeholder="{{ __('Search') }}..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Global Filters Dropdown --}}
            @if($globalFilters->isNotEmpty())
                <div x-data="{ open: false }" class="relative">
                    <button
                            @click="open = !open"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>{{ __('Filters') }}</span>
                        @if(!empty(array_filter($tableFilters)))
                            <span class="ml-1 px-2 py-0.5 bg-blue-500 text-white text-xs rounded-full">
                                {{ count(array_filter($tableFilters)) }}
                            </span>
                        @endif
                    </button>

                    <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition
                            class="absolute z-10 mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-lg"
                    >
                        <div class="p-4 space-y-4">
                            @foreach($globalFilters as $filter)
                                {!! $filter->render() !!}
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Saved Filters --}}
            @if($presetsEnabled)
                <div x-data="{ open: false }" class="relative">
                    <button
                            @click="open = !open"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span>{{ __('Saved Filters') }}</span>
                        @if($activePresetId)
                            <span class="ml-1 w-2 h-2 bg-blue-500 rounded-full"></span>
                        @endif
                    </button>

                    <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition
                            class="absolute z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg"
                    >
                        <div class="p-2">
                            @if($presets->isNotEmpty())
                                <div class="mb-2 border-b border-gray-200 pb-2">
                                    @foreach($presets as $preset)
                                        <div class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 rounded group">
                                            <button
                                                    wire:click="loadPreset({{ $preset->id }})"
                                                    class="flex-1 text-left text-sm {{ $activePresetId === $preset->id ? 'text-blue-600 font-medium' : 'text-gray-700' }}"
                                            >
                                                {{ $preset->name }}
                                                @if($preset->is_default)
                                                    <span class="text-xs text-gray-500">({{ __('default') }})</span>
                                                @endif
                                            </button>
                                            <button
                                                    wire:click="deletePreset({{ $preset->id }})"
                                                    class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <button
                                    wire:click="$set('showPresetModal', true)"
                                    class="w-full px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded flex items-center justify-center space-x-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>{{ __('Save current filters') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Clear Filters --}}
            @if(!empty(array_filter($tableFilters)) || $tableSearch)
                <button
                        wire:click="clearFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 flex items-center space-x-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>{{ __('Clear filters') }}</span>
                </button>
            @endif
        </div>

        {{-- Right side: Column Toggle --}}
        @if($columnToggleEnabled)
            <div x-data="{ open: false }" class="relative">
                <button
                        @click="open = !open"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    <span>{{ __('Columns') }}</span>
                </button>

                <div
                        x-show="open"
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg"
                >
                    <div class="p-3 space-y-2">
                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">{{ __('Visible columns') }}</span>
                            <button
                                    wire:click="showAllColumns"
                                    class="text-xs text-blue-600 hover:text-blue-800"
                            >
                                {{ __('Show all') }}
                            </button>
                        </div>
                        @foreach($toggleableColumns as $column)
                            <label class="flex items-center space-x-2 px-2 py-1 hover:bg-gray-50 rounded cursor-pointer">
                                <input
                                        type="checkbox"
                                        wire:click="toggleColumn('{{ $column->getField() }}')"
                                        @checked(!in_array($column->getField(), $hiddenColumns))
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="text-sm text-gray-700">{{ $column->getLabel() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Table Container --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            {{-- Header Row --}}
            <tr>
                @if($bulkActions->isNotEmpty())
                    <th class="w-12 px-6 py-3">
                        <input
                                type="checkbox"
                                wire:model.live="tableSelectAll"
                                class="rounded border-gray-300"
                        >
                    </th>
                @endif

                @if($hasSubRows)
                    <th class="w-12 px-6 py-3"></th>
                @endif

                @foreach($columns as $column)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ $column->getResponsiveClasses() }}">
                        @if($column->isSortable())
                            <button
                                    wire:click="sortBy('{{ $column->getField() }}')"
                                    class="flex items-center space-x-1 hover:text-gray-700 group"
                            >
                                <span>{{ $column->getLabel() }}</span>
                                <svg
                                        class="w-4 h-4 {{ $tableSortColumn === $column->getField() ? 'text-blue-600' : 'text-gray-400' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($tableSortColumn === $column->getField() && $tableSortDirection === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    @elseif($tableSortColumn === $column->getField() && $tableSortDirection === 'desc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    @endif
                                </svg>
                            </button>
                        @else
                            {{ $column->getLabel() }}
                        @endif
                    </th>
                @endforeach

                @if($actions->isNotEmpty())
                    <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                @endif
            </tr>

            {{-- Filter Row --}}
            @if($filters->isNotEmpty())
                <tr class="bg-gray-100">
                    @if($bulkActions->isNotEmpty())
                        <th class="px-6 py-2"></th>
                    @endif

                    @if($hasSubRows)
                        <th class="px-6 py-2"></th>
                    @endif

                    @foreach($columns as $column)
                        <th class="px-6 py-2 {{ $column->getResponsiveClasses() }}">
                            @php
                                $filter = $filters->first(function($f) use ($column) {
                                    return $f->getColumn() === $column->getField();
                                });
                            @endphp

                            @if($filter)
                                <div class="min-w-0">
                                    {!! $filter !!}
                                </div>
                            @endif
                        </th>
                    @endforeach

                    @if($actions->isNotEmpty())
                        <th class="px-6 py-2"></th>
                    @endif
                </tr>
            @endif
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($data as $group)
                {{-- Group Header --}}
                @if($groupBy && $group['key'])
                    <tr class="bg-gray-100 hover:bg-gray-200 cursor-pointer"
                        wire:click="toggleGroup('{{ $group['key'] }}')">
                        <td colspan="100" class="px-6 py-3">
                            <div class="flex items-center space-x-2">
                                @if($isCollapsible)
                                    <svg
                                            class="w-5 h-5 transition-transform {{ in_array($group['key'], $expandedGroups) ? 'rotate-90' : '' }}"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                @endif
                                <span class="font-semibold text-gray-700">{{ $group['label'] }}</span>
                            </div>
                        </td>
                    </tr>
                @endif

                {{-- Group Items --}}
                @if(!$groupBy || !$isCollapsible || in_array($group['key'], $expandedGroups))
                    @foreach($group['items'] as $record)
                        <tr wire:key="row-{{ $record->id }}" class="hover:bg-gray-50">
                            @if($bulkActions->isNotEmpty())
                                <td class="px-6 py-4">
                                    <input
                                            type="checkbox"
                                            wire:model.live="tableSelected"
                                            value="{{ $record->id }}"
                                            class="rounded border-gray-300"
                                    >
                                </td>
                            @endif

                            @if($hasSubRows)
                                <td class="px-6 py-4">
                                    <button
                                            wire:click="toggleRow({{ $record->id }})"
                                            class="text-gray-400 hover:text-gray-600"
                                    >
                                        <svg
                                                class="w-5 h-5 transition-transform {{ in_array($record->id, $expandedRows) ? 'rotate-90' : '' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </td>
                            @endif

                            @foreach($columns as $column)
                                <td class="px-6 py-4 {{ $column->getResponsiveClasses() }}" data-label="{{ $column->getLabel() }}">
                                    {!! $column->for($record) !!}
                                </td>
                            @endforeach

                            @if($actions->isNotEmpty())
                                <td class="px-6 py-4 text-right space-x-2">
                                    @foreach($actions as $action)
                                        @include('livewire-table::components.table.actions.action', ['action' => $action, 'record' => $record])
                                    @endforeach
                                </td>
                            @endif
                        </tr>

                        {{-- Sub-row --}}
                        @if($hasSubRows && in_array($record->id, $expandedRows))
                            <tr wire:key="sub-{{ $record->id }}" class="bg-gray-50">
                                <td colspan="100" class="px-6 py-4">
                                    <div class="pl-8">
                                        {!! $table->renderSubRow($record) !!}
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="100" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center space-y-2">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <span class="text-lg font-medium">{{ __('No records') }}</span>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @php
        $paginatorData = null;
        if ($data->isNotEmpty()) {
            $firstGroup = $data->first();
            if (isset($firstGroup['items']) && $firstGroup['items'] instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $paginatorData = $firstGroup['items'];
            }
        }
    @endphp

    @if($paginatorData && $paginatorData->hasPages())
        <div class="mt-4 bg-gradient-to-r from-gray-50 to-white border-t border-gray-200 px-6 py-4 rounded-b-lg shadow-sm">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- Left: Info with icon --}}
                <div class="flex-1 flex items-center justify-start gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm text-gray-600">
                        Zobrazeno
                        <span class="font-semibold text-gray-900">{{ $paginatorData->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold text-gray-900">{{ $paginatorData->lastItem() ?? 0 }}</span>
                        z
                        <span class="font-semibold text-blue-600">{{ $paginatorData->total() }}</span>
                        {{ $paginatorData->total() === 1 ? 'záznamu' : ($paginatorData->total() < 5 ? 'záznamů' : 'záznamů') }}
                    </p>
                </div>

                {{-- Center: Per Page with modern styling --}}
                <div class="flex items-center justify-center gap-2 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <label for="perPageSelect" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                        Řádků:
                    </label>
                    <select
                            id="perPageSelect"
                            wire:model.live="tablePerPage"
                            class="px-3 py-1.5 text-sm font-medium border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white cursor-pointer transition-all hover:border-blue-400"
                    >
                        @foreach($pageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Right: Page Numbers with modern design --}}
                <div class="flex-1 flex justify-end">
                    <nav role="navigation" aria-label="Pagination Navigation" class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                        {{-- Previous Button --}}
                        @if ($paginatorData->onFirstPage())
                            <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $paginatorData->previousPageUrl() }}"
                               wire:navigate
                               class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-blue-50 hover:text-blue-600 focus:z-20 focus:outline-offset-0 transition-all bg-white">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $currentPage = $paginatorData->currentPage();
                            $lastPage = $paginatorData->lastPage();
                            $showPages = [];

                            // Always show first page
                            $showPages[] = 1;

                            // Show pages around current
                            for ($i = max(2, $currentPage - 1); $i <= min($lastPage - 1, $currentPage + 1); $i++) {
                                $showPages[] = $i;
                            }

                            // Always show last page
                            if ($lastPage > 1) {
                                $showPages[] = $lastPage;
                            }

                            $showPages = array_unique($showPages);
                            sort($showPages);
                        @endphp

                        @foreach ($showPages as $index => $page)
                            {{-- Show ellipsis if gap --}}
                            @if ($index > 0 && $page - $showPages[$index - 1] > 1)
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 ring-1 ring-inset ring-gray-300 bg-white">
                                    ...
                                </span>
                            @endif

                            @if ($page == $currentPage)
                                <span aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $paginatorData->url($page) }}"
                                   wire:navigate
                                   class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-blue-50 hover:text-blue-600 focus:z-20 focus:outline-offset-0 transition-all bg-white">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($paginatorData->hasMorePages())
                            <a href="{{ $paginatorData->nextPageUrl() }}"
                               wire:navigate
                               class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-blue-50 hover:text-blue-600 focus:z-20 focus:outline-offset-0 transition-all bg-white">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        @else
                            <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Actions Bar --}}
    @if($bulkActions->isNotEmpty() && count($tableSelected) > 0)
        <div
                x-data="{ show: true }"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="transform translate-y-full opacity-0"
                x-transition:enter-end="transform translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="transform translate-y-0 opacity-100"
                x-transition:leave-end="transform translate-y-full opacity-0"
                class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-white shadow-2xl rounded-lg border border-gray-200 z-50 min-w-[400px]"
        >
            <div class="p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">
                        {{ __('Selected') }}: <strong class="text-gray-900 text-lg">{{ count($tableSelected) }}</strong>
                    </span>
                    <div class="h-6 w-px bg-gray-300"></div>

                    {{-- Bulk Actions Select --}}
                    <select
                            wire:model="selectedBulkAction"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">{{ __('Choose action') }}...</option>
                        @foreach($bulkActions as $bulkAction)
                            <option value="{{ $bulkAction->getName() }}">{{ $bulkAction->getLabel() }}</option>
                        @endforeach
                    </select>

                    <button
                            wire:click="executeBulkAction"
                            x-bind:disabled="!$wire.selectedBulkAction"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium transition-colors"
                    >
                        {{ __('Execute') }}
                    </button>
                </div>

                <button
                        wire:click="$set('tableSelected', []); $set('tableSelectAll', false)"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                        title="{{ __('Cancel') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Save Preset Modal --}}
    @if($presetsEnabled)
        <div
                x-data
                x-show="$wire.showPresetModal"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen p-4">
                <div
                        class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                        @click="$wire.showPresetModal = false"
                ></div>

                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Save filters') }}</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Preset name') }}
                        </label>
                        <input
                                type="text"
                                wire:model="presetName"
                                placeholder="{{ __('e.g. Active users') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        @error('presetName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                                @click="$wire.showPresetModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                                wire:click="savePreset"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire-table::components.table.modal')
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tableComponent', (liveUpdateInterval) => ({
            init() {
                if (liveUpdateInterval) {
                    console.log(`Table auto-refresh every ${liveUpdateInterval} seconds`);
                }
            }
        }))
    })
</script>