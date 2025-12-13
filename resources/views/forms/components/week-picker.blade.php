@php
$isMultiple = $isMultiple();
$isDisabled = $isDisabled();
$isReadOnly = $field->isReadOnly();
$disabledOptions = $getDisabledOptions();
$statePath = $getStatePath();
$state = $getState();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="temporalPicker({
            type: 'week',
            state: @js($state),
            statePath: @js($statePath),
            multiple: @js($isMultiple),
            disabled: @js($isDisabled),
            readOnly: @js($isReadOnly),
            disabledOptions: @js($disabledOptions),
            minYear: @js($getMinYear()),
            maxYear: @js($getMaxYear()),
            showWeekNumber: @js($shouldShowWeekNumber()),
            firstDayOfWeek: @js($getFirstDayOfWeek()),
            format: @js($getFormat()),
            locale: @js($getLocale()),
        })"
        x-on:change="syncState()"
        wire:ignore
        {{ $attributes->merge($getExtraAttributes())->class([
            'fi-fo-temporal-picker fi-fo-week-picker',
        ]) }}>
        <div class="relative">
            <button
                type="button"
                x-ref="trigger"
                x-on:click="open = !open"
                :disabled="@js($isDisabled)"
                class="fi-input w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                <span x-text="getDisplayValue() || '{{ __('temporal-picker::temporal-picker.placeholders.week') }}'"></span>
                <x-filament::icon
                    icon="heroicon-m-calendar-days"
                    class="h-5 w-5 text-gray-400" />
            </button>

            {{-- Week picker calendar will be rendered by Vanilla Calendar Pro --}}
            <div
                x-ref="picker"
                x-show="open"
                x-on:click.away="open = false"
                x-transition
                class="absolute z-50 mt-1 rounded-lg shadow-lg border fi-temporal-dropdown">
                {{-- Calendar container for Vanilla Calendar Pro --}}
                <div x-ref="calendar" class="vanilla-calendar"></div>
            </div>
        </div>
    </div>
</x-dynamic-component>