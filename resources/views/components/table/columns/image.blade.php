<img
    src="{{ $column->getFormattedValue($record) }}"
    alt="{{ $record->name ?? '' }}"
    class="object-cover {{ $column->isCircular() ? 'rounded-full' : 'rounded' }} {{ match($column->getSize()) {
        'sm' => 'w-8 h-8',
        'md' => 'w-10 h-10',
        'lg' => 'w-12 h-12',
        default => 'w-10 h-10'
    } }}"
    loading="lazy"
>
