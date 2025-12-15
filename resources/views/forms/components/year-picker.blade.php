@php
$isMultiple = $isMultiple();
$isDisabled = $isDisabled();
$isReadOnly = $field->isReadOnly();
$options = $getOptions();
$disabledOptions = $getDisabledOptions();
$statePath = $getStatePath();
$state = $getState();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="temporalPicker({
            type: 'year',
            state: @js($state),
            statePath: @js($statePath),
            multiple: @js($isMultiple),
            disabled: @js($isDisabled),
            readOnly: @js($isReadOnly),
            disabledOptions: @js($disabledOptions),
            options: @js($options),
            minYear: @js($getMinYear()),
            maxYear: @js($getMaxYear()),
        })"
        x-on:change="syncState()"
        wire:ignore
        {{ $attributes->merge($getExtraAttributes())->class([
            'fi-fo-temporal-picker fi-fo-year-picker',
        ]) }}>
        @if($isInline())
        {{-- Inline grid view --}}
        <div class="grid gap-2" style="grid-template-columns: repeat({{ $getGridColumns() }}, minmax(0, 1fr));">
            @foreach($options as $option)
            <button
                type="button"
                x-on:click="toggleOption({{ $option['value'] }})"
                :class="{
                            'fi-temporal-option-selected': isSelected({{ $option['value'] }}),
                            'fi-temporal-option-disabled': isOptionDisabled({{ $option['value'] }}),
                        }"
                :disabled="isOptionDisabled({{ $option['value'] }}) || @js($isDisabled)"
                class="fi-temporal-option">
                {{ $option['label'] }}
            </button>
            @endforeach
        </div>
        @else
        {{-- Dropdown/Modal view with Vanilla Calendar Pro --}}
        <div class="relative">
            <button
                type="button"
                x-ref="trigger"
                x-on:click="open = !open"
                :disabled="@js($isDisabled)"
                class="fi-input w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                <span x-text="getDisplayValue() || '{{ __('temporal-picker::temporal-picker.placeholders.year') }}'"></span>
                <x-filament::icon
                    icon="heroicon-m-chevron-down"
                    class="h-5 w-5 text-gray-400" />
            </button>

            <div
                x-ref="picker"
                x-show="open"
                x-on:click.away="open = false"
                x-transition
                class="absolute z-50 mt-1 w-full max-h-60 overflow-auto rounded-lg shadow-lg border fi-temporal-dropdown">
                <div class="grid gap-1 p-2" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                    <template x-for="option in options" :key="option.value">
                        <button
                            type="button"
                            x-on:click="toggleOption(option.value)"
                            :class="{
                                    'fi-temporal-option-selected': isSelected(option.value),
                                    'fi-temporal-option-disabled': isOptionDisabled(option.value),
                                }"
                            :disabled="isOptionDisabled(option.value)"
                            class="fi-temporal-option"
                            x-text="option.label"></button>
                    </template>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-dynamic-component>

