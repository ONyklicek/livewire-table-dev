<div>
    <label class="block text-sm font-medium text-gray-700 mb-1 hidden">
        {{ $filter->getLabel() }}
    </label>
    <input
        type="text"
        wire:model.live.debounce.300ms="tableFilters.{{ $filter->getName() }}"
        placeholder="{{ $filter->getPlaceholder() ?? 'Hledat...' }}"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    >
</div>
