<div
    x-data="{
        editing: false,
        value: @js($column->getValue($record)),
        originalValue: @js($column->getValue($record)),
        loading: false
    }"
    @click.away="if (editing && value !== originalValue) {
        editing = false;
        $wire.updateCell({{ $record->id }}, '{{ $column->getField() }}', value);
    }"
    class="group"
>
    <div x-show="!editing" class="flex items-center space-x-2">
        <span class="text-sm text-gray-900">{{ $column->getFormattedValue($record) }}</span>
        <button
            @click="editing = true; $nextTick(() => $refs.input?.focus())"
            class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </button>
    </div>

    <div x-show="editing" x-cloak>
        @if($column->getInputType() === 'text')
            <input
                x-ref="input"
                x-model="value"
                @keydown.enter="editing = false; $wire.updateCell({{ $record->id }}, '{{ $column->getField() }}', value)"
                @keydown.escape="value = originalValue; editing = false"
                type="text"
                class="w-full px-2 py-1 text-sm border border-blue-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        @elseif($column->getInputType() === 'select')
            <select
                x-ref="input"
                x-model="value"
                @change="editing = false; $wire.updateCell({{ $record->id }}, '{{ $column->getField() }}', value)"
                class="w-full px-2 py-1 text-sm border border-blue-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                @foreach($column->getOptions() as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                @endforeach
            </select>
        @elseif($column->getInputType() === 'textarea')
            <textarea
                x-ref="input"
                x-model="value"
                @keydown.enter.prevent="editing = false; $wire.updateCell({{ $record->id }}, '{{ $column->getField() }}', value)"
                @keydown.escape="value = originalValue; editing = false"
                rows="3"
                class="w-full px-2 py-1 text-sm border border-blue-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
        @elseif($column->getInputType() === 'number')
            <input
                x-ref="input"
                x-model="value"
                @keydown.enter="editing = false; $wire.updateCell({{ $record->id }}, '{{ $column->getField() }}', value)"
                @keydown.escape="value = originalValue; editing = false"
                type="number"
                class="w-full px-2 py-1 text-sm border border-blue-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        @elseif($column->getInputType() === 'date')
            <input
                x-ref="input"
                x-model="value"
                @change="editing = false; $wire.updateCell({{ $record->id }}, '{{ $column->getField() }}', value)"
                type="date"
                class="w-full px-2 py-1 text-sm border border-blue-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        @endif
    </div>
</div>
