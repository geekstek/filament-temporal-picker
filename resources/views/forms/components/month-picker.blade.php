@php
$isMultiple = $isMultiple();
$isDisabled = $isDisabled();
$isReadOnly = $field->isReadOnly();
$options = $getOptions();
$disabledOptions = $getDisabledOptions();
$statePath = $getStatePath();
$state = $getState();
$yearRange = $getYearRange();
$minDate = $getMinDate();
$maxDate = $getMaxDate();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="temporalPicker({
            type: 'month',
            state: @js($state),
            statePath: @js($statePath),
            multiple: @js($isMultiple),
            rangeSelection: false,
            disabled: @js($isDisabled),
            readOnly: @js($isReadOnly),
            disabledOptions: @js($disabledOptions),
            options: @js($options),
            minDate: @js($minDate),
            maxDate: @js($maxDate),
            yearRange: @js($yearRange),
            format: @js($getFormat()),
            displayFormat: @js($getDisplayFormat()),
            locale: @js($getLocale()),
        })"
        wire:key="month-picker-{{ $statePath }}-{{ $minDate }}-{{ $maxDate }}"
        x-on:change="syncState()"
        {{ $attributes->merge($getExtraAttributes())->class([
            'fi-fo-temporal-picker fi-fo-month-picker',
        ]) }}>
        <div class="relative">
            <div class="relative">
                <button
                    type="button"
                    x-ref="trigger"
                    x-on:click="open = !open"
                    :disabled="@js($isDisabled)"
                    class="fi-input w-full flex items-center justify-between px-3 py-2 pr-20 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                    <span x-text="getDisplayValue() || '{{ __('temporal-picker::temporal-picker.placeholders.month') }}'"></span>
                </button>
                
                {{-- Clear button (absolute positioned outside main button) --}}
                <div 
                    x-show="state && !@js($isDisabled) && !@js($isReadOnly)"
                    @click.stop.prevent="clearSelection()"
                    class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition cursor-pointer flex items-center justify-center w-5 h-5 z-10">
                    <x-filament::icon
                        icon="heroicon-m-x-mark"
                        class="h-4 w-4" />
                </div>
                
                {{-- Calendar icon --}}
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    <x-filament::icon
                        icon="heroicon-m-calendar"
                        class="h-5 w-5 text-gray-400" />
                </div>
            </div>

            <div
                x-ref="picker"
                x-show="open"
                x-on:click.away="open = false"
                x-transition
                class="absolute z-50 mt-1 w-72 rounded-lg shadow-lg border p-3 fi-temporal-dropdown">
                {{-- Year selector --}}
                @if($shouldShowYear())
                <div class="flex items-center justify-between mb-3">
                    <button
                        type="button"
                        x-on:click="previousYear()"
                        class="fi-temporal-header-btn">
                        <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5" />
                    </button>
                    <span class="font-semibold" x-text="currentYear"></span>
                    <button
                        type="button"
                        x-on:click="nextYear()"
                        class="fi-temporal-header-btn">
                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5" />
                    </button>
                </div>
                @endif

                {{-- Month grid --}}
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; width: 100%;">
                    <template x-for="option in options" :key="option.value">
                        <button
                            type="button"
                            x-on:click="selectMonth(option.value)"
                            x-on:mouseenter="handleHover(option.value)"
                            x-on:mouseleave="handleMouseLeave()"
                            :class="{
                                'fi-temporal-option-selected': isMonthSelected(option.value),
                                'fi-temporal-option-disabled': isMonthDisabled(option.value)
                            }"
                            :disabled="isMonthDisabled(option.value)"
                            class="fi-temporal-option"
                            x-text="option.label"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>