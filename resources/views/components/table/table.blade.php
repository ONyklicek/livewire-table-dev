<div
    x-data="tableComponent(@js($liveUpdateInterval))"
    @if($liveUpdateInterval) wire:poll.{{ $liveUpdateInterval }}s @endif
    class="{{ $scheme['responsive_classes'] ?? '' }}"
>
    {{-- Header Actions Bar --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        {{-- Left side: Filters & Search --}}
        <div class="flex items-center space-x-2">
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
                        <span>Filtry</span>
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
                        <span>Uložené filtry</span>
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
                                        <div
                                            class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 rounded group">
                                            <button
                                                wire:click="loadPreset({{ $preset->id }})"
                                                class="flex-1 text-left text-sm {{ $activePresetId === $preset->id ? 'text-blue-600 font-medium' : 'text-gray-700' }}"
                                            >
                                                {{ $preset->name }}
                                                @if($preset->is_default)
                                                    <span class="text-xs text-gray-500">(výchozí)</span>
                                                @endif
                                            </button>
                                            <button
                                                wire:click="deletePreset({{ $preset->id }})"
                                                class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Uložit aktuální filtry</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Clear Filters --}}
            @if(!empty($tableFilters) || $tableSearch)
                <button
                    wire:click="clearFilters"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 flex items-center space-x-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Vymazat filtry</span>
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
                    <span>Sloupce</span>
                </button>

                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg"
                >
                    <div class="p-3 space-y-2">
                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Viditelné sloupce</span>
                            <button
                                wire:click="showAllColumns"
                                class="text-xs text-blue-600 hover:text-blue-800"
                            >
                                Zobrazit vše
                            </button>
                        </div>
                        @foreach($toggleableColumns as $column)
                            <label
                                class="flex items-center space-x-2 px-2 py-1 hover:bg-gray-50 rounded cursor-pointer">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 15l7-7 7 7"/>
                                    @elseif($tableSortColumn === $column->getField() && $tableSortDirection === 'desc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    @endif
                                </svg>
                            </button>
                        @else
                            {{ $column->getLabel() }}
                        @endif
                    </th>
                @endforeach

                @if($actions->isNotEmpty())
                    <th class="px-6 py-3 text-right">Akce</th>
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
                                    return $f->getColumn() === $column->getField() ||
                                           $f->getName() === $column->getField();
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5l7 7-7 7"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </td>
                            @endif

                            @foreach($columns as $column)
                                <td class="px-6 py-4 {{ $column->getResponsiveClasses() }}"
                                    data-label="{{ $column->getLabel() }}">
                                    {!! $column->for($record) !!}
                                </td>
                            @endforeach

                            @if($actions->isNotEmpty())
                                <td class="px-6 py-4 text-right space-x-2">
                                    @foreach($actions as $action)
                                        @include('components.table.actions.action', ['action' => $action, 'record' => $record])
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
                            <span class="text-lg font-medium">Žádné záznamy</span>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($data->isNotEmpty() && $data->first()['items'] instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $data->first()['items']->links() }}
        </div>
    @endif

    {{-- Bulk Actions Bar --}}
    @if($bulkActions->isNotEmpty() && count($tableSelected) > 0)
        <div
            x-data
            x-transition
            class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-white shadow-2xl rounded-lg p-4 flex items-center space-x-4 border border-gray-200 z-50"
        >
            <span class="text-sm text-gray-600">
                Vybráno: <strong class="text-gray-900">{{ count($tableSelected) }}</strong>
            </span>
            <div class="h-6 w-px bg-gray-300"></div>
            @foreach($bulkActions as $bulkAction)
                <button
                    wire:click="executeBulkAction('{{ $bulkAction->getName() }}')"
                    class="px-4 py-2 bg-{{ $bulkAction->getColor() }}-600 text-white rounded-lg hover:bg-{{ $bulkAction->getColor() }}-700 text-sm font-medium transition-colors"
                >
                    {{ $bulkAction->getLabel() }}
                </button>
            @endforeach
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Uložit filtry</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Název presetu
                        </label>
                        <input
                            type="text"
                            wire:model="presetName"
                            placeholder="Např. Aktivní uživatelé"
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
                            Zrušit
                        </button>
                        <button
                            wire:click="savePreset"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Uložit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
