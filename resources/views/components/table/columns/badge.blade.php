@php

    $colorClasses = [
        'green' => 'bg-green-100 text-green-800 ring-green-600/20',
        'red' => 'bg-red-100 text-red-800 ring-red-600/20',
        'blue' => 'bg-blue-100 text-blue-800 ring-blue-600/20',
        'yellow' => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
        'gray' => 'bg-gray-100 text-gray-800 ring-gray-600/20',
        'orange' => 'bg-orange-100 text-orange-800 ring-orange-600/20',
        'purple' => 'bg-purple-100 text-purple-800 ring-purple-600/20',
    ];

    $dotClasses = [
        'green' => 'fill-green-500',
        'red' => 'fill-red-500',
        'blue' => 'fill-blue-500',
        'yellow' => 'fill-yellow-500',
        'gray' => 'fill-gray-500',
        'orange' => 'fill-orange-500',
        'purple' => 'fill-purple-500',
    ];

    $textSizes = [
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-base',
        'xl' => 'text-lg',
    ];

    $textSize = $textSizes[$column->getSize()] ?? 'lg';
    $color = $column->getColor($rawValue);
    $classes = $colorClasses[$color] ?? $colorClasses['gray'];
    $dotClass = $dotClasses[$color] ?? $dotClasses['gray'];
@endphp

<span
    class="inline-flex items-center gap-x-1.5 rounded-full px-2.5 py-1 {{ $textSize }} font-semibold ring-1 ring-inset {{ $classes }}">
    @if($icon = $column->getIcon($value))
        <svg class="h-1.5 w-1.5 {{ $dotClass }}" viewBox="0 0 6 6">
            <circle cx="3" cy="3" r="3"/>
        </svg>
    @endif

    <span>{{ $value }}</span>
</span>
