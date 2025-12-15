@php
$isMultiple = $isMultiple();
$isDisabled = $isDisabled();
$isReadOnly = $field->isReadOnly();
$options = $getOrderedOptions();
$disabledOptions = $getDisabledOptions();
$statePath = $getStatePath();
$state = $getState();
$columns = $getGridColumns();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="{
            ...temporalPicker({
                type: 'weekday',
                statePath: @js($statePath),
                multiple: @js($isMultiple),
                disabled: @js($isDisabled),
                readOnly: @js($isReadOnly),
                disabledOptions: @js($disabledOptions),
                options: @js($options),
                valueFormat: @js($getValueFormat()),
            }),
            state: $wire.entangle('{{ $statePath }}'){{ $isLive() ? '.live' : '' }},
        }"
        {{ $attributes->merge($getExtraAttributes())->class([
            'fi-fo-temporal-picker fi-fo-weekday-picker',
        ]) }}>
        {{-- Always show as inline grid for weekday picker --}}
        <div class="grid gap-2" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
            @foreach($options as $value => $label)
            <button
                type="button"
                wire:loading.attr="disabled"
                x-on:click="toggleOption(@js($value))"
                :class="{
                        'fi-temporal-option-selected': isSelected(@js($value)),
                        'fi-temporal-option-disabled': isOptionDisabled(@js($value)) || @js($isDisabled),
                    }"
                :disabled="isOptionDisabled(@js($value)) || @js($isDisabled)"
                class="fi-temporal-option fi-temporal-option-outlined">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Actions for select/deselect all --}}
        @if($isMultiple)
        <div class="flex gap-2 mt-2">
            <button
                type="button"
                x-on:click="selectAll()"
                class="text-xs text-primary-600 hover:text-primary-500">
                {{ __('temporal-picker::temporal-picker.actions.select_all') }}
            </button>
            <span class="text-gray-300">|</span>
            <button
                type="button"
                x-on:click="deselectAll()"
                class="text-xs text-gray-500 hover:text-gray-700">
                {{ __('temporal-picker::temporal-picker.actions.deselect_all') }}
            </button>
        </div>
        @endif
    </div>
</x-dynamic-component>

