@if($action->getModal())
    <button
        @click="$dispatch('open-modal', {
            action: '{{ $action->getName() }}',
            recordId: {{ $record->id }},
            modal: @js($action->getModal())
        })"
        class="text-{{ $action->getColor() }}-600 hover:text-{{ $action->getColor() }}-900"
        title="{{ $action->getLabel() }}"
    >
        @if($action->getIcon())
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $action->getIcon() !!}
            </svg>
        @else
            {{ $action->getLabel() }}
        @endif
    </button>
@elseif($action->requiresConfirmation())
    <button
        @click="if(confirm('{{ $action->getConfirmationText() }}')) $wire.executeAction('{{ $action->getName() }}', {{ $record->id }})"
        class="text-{{ $action->getColor() }}-600 hover:text-{{ $action->getColor() }}-900"
        title="{{ $action->getLabel() }}"
    >
        @if($action->getIcon())
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $action->getIcon() !!}
            </svg>
        @else
            {{ $action->getLabel() }}
        @endif
    </button>
@else
    <button
        wire:click="executeAction('{{ $action->getName() }}', {{ $record->id }})"
        class="text-{{ $action->getColor() }}-600 hover:text-{{ $action->getColor() }}-900"
        title="{{ $action->getLabel() }}"
    >
        @if($action->getIcon())
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $action->getIcon() !!}
            </svg>
        @else
            {{ $action->getLabel() }}
        @endif
    </button>
@endif
