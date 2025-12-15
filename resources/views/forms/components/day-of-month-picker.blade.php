@php
$isMultiple = $isMultiple();
$isDisabled = $isDisabled();
$isReadOnly = $field->isReadOnly();
$options = $getOptions();
$disabledOptions = $getDisabledOptions();
$statePath = $getStatePath();
$state = $getState();
$columns = $getGridColumns();
$showCalendarGrid = $shouldShowCalendarGrid();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="{
            ...temporalPicker({
                type: 'dayOfMonth',
                statePath: @js($statePath),
                multiple: @js($isMultiple),
                disabled: @js($isDisabled),
                readOnly: @js($isReadOnly),
                disabledOptions: @js($disabledOptions),
                options: @js($options),
                minDay: @js($getMinDay()),
                maxDay: @js($getMaxDay()),
            }),
            state: $wire.entangle('{{ $statePath }}'){{ $isLive() ? '.live' : '' }},
        }"
        {{ $attributes->merge($getExtraAttributes())->class([
            'fi-fo-temporal-picker fi-fo-day-of-month-picker',
        ]) }}>
        @if($showCalendarGrid)
        {{-- Calendar-style grid --}}
        <div class="grid gap-1" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
            @foreach($options as $day => $label)
            <button
                type="button"
                wire:loading.attr="disabled"
                x-on:click="toggleOption({{ $day }})"
                :class="{
                            'fi-temporal-option-selected': isSelected({{ $day }}),
                            'fi-temporal-option-disabled': isOptionDisabled({{ $day }}) || @js($isDisabled),
                        }"
                :disabled="isOptionDisabled({{ $day }}) || @js($isDisabled)"
                class="fi-temporal-option fi-temporal-option-outlined aspect-square">
                {{ $label }}
            </button>
            @endforeach
        </div>
        @else
        {{-- Compact dropdown mode --}}
        <div class="relative" x-data="{ open: false }">
            <button
                type="button"
                x-on:click="open = !open"
                :disabled="@js($isDisabled)"
                class="fi-input w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                <span x-text="getDisplayValue() || '{{ __('temporal-picker::temporal-picker.placeholders.day_of_month') }}'"></span>
                <x-filament::icon
                    icon="heroicon-m-chevron-down"
                    class="h-5 w-5 text-gray-400" />
            </button>

            <div
                x-show="open"
                x-on:click.away="open = false"
                x-transition
                class="absolute z-50 mt-1 w-full max-h-60 overflow-auto rounded-lg bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 p-2">
                <div class="grid gap-1" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
                    <template x-for="day in Object.keys(options).map(Number)" :key="day">
                        <button
                            type="button"
                            x-on:click="toggleOption(day)"
                            :class="{
                                    'fi-temporal-option-selected': isSelected(day),
                                    'fi-temporal-option-disabled': isOptionDisabled(day),
                                }"
                            :disabled="isOptionDisabled(day)"
                            class="fi-temporal-option aspect-square"
                            x-text="day"></button>
                    </template>
                </div>
            </div>
        </div>
        @endif

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

