<div>
    <label class="block text-sm font-medium text-gray-700 mb-1 hidden">
        {{ $filter->getLabel() }}
    </label>
    <select
        wire:model.live="tableFilters.{{ $filter->getName() }}"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    >
        <option value="">{{ $filter->getPlaceholder() ?? 'Vyberte...' }}</option>
        @foreach($filter->getOptions() as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
