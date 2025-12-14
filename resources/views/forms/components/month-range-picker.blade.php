@php
$startField = $getStartField();
$endField = $getEndField();
$isDisabled = $isDisabled();
$options = $getMonthOptions();
$yearRange = $getYearRange();
$state = $getState() ?? ['start' => null, 'end' => null];
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="monthRangePicker({
            startField: @js($startField),
            endField: @js($endField),
            startState: @js($state['start']),
            endState: @js($state['end']),
            disabled: @js($isDisabled),
            options: @js($options),
            minDate: @js($getMinDate()),
            maxDate: @js($getMaxDate()),
            yearRange: @js($yearRange),
            format: @js($getFormat()),
        })"
        wire:ignore
        {{ $attributes->merge($getExtraAttributes())->class([
            'fi-fo-temporal-picker fi-fo-month-range-picker',
        ]) }}>
        <div class="grid grid-cols-2 gap-4">
            {{-- Start Month --}}
            <div class="relative">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                    {{ $getStartLabel() }}
                </label>
                <input
                    type="hidden"
                    x-model="startValue"
                    wire:model="{{ $startField }}" />
                <button
                    type="button"
                    x-on:click="openStart = !openStart"
                    :disabled="@js($isDisabled)"
                    class="fi-input w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                    <span x-text="getDisplayValue('start') || '{{ __('temporal-picker::temporal-picker.placeholders.month') }}'"></span>
                    <x-filament::icon
                        icon="heroicon-m-calendar"
                        class="h-5 w-5 text-gray-400" />
                </button>

                <div
                    x-show="openStart"
                    x-on:click.away="openStart = false"
                    x-transition
                    class="absolute z-50 mt-1 w-72 rounded-lg shadow-lg border p-3 fi-temporal-dropdown">
                    {{-- Year selector for start --}}
                    @if($shouldShowYear())
                    <div class="flex items-center justify-between mb-3">
                        <button
                            type="button"
                            x-on:click="previousYear('start')"
                            class="fi-temporal-header-btn">
                            <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5" />
                        </button>
                        <span class="font-semibold" x-text="startYear"></span>
                        <button
                            type="button"
                            x-on:click="nextYear('start')"
                            class="fi-temporal-header-btn">
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5" />
                        </button>
                    </div>
                    @endif

                    {{-- Month grid for start --}}
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; width: 100%;">
                        <template x-for="option in options" :key="'start-' + option.value">
                            <button
                                type="button"
                                x-on:click="selectMonth('start', option.value)"
                                :class="{
                                    'fi-temporal-option-selected': isMonthSelected('start', option.value),
                                    'fi-temporal-option-disabled': isMonthDisabled('start', option.value)
                                }"
                                :disabled="isMonthDisabled('start', option.value)"
                                class="fi-temporal-option"
                                x-text="option.label"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- End Month --}}
            <div class="relative">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                    {{ $getEndLabel() }}
                </label>
                <input
                    type="hidden"
                    x-model="endValue"
                    wire:model="{{ $endField }}" />
                <button
                    type="button"
                    x-on:click="openEnd = !openEnd"
                    :disabled="@js($isDisabled)"
                    class="fi-input w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                    <span x-text="getDisplayValue('end') || '{{ __('temporal-picker::temporal-picker.placeholders.month') }}'"></span>
                    <x-filament::icon
                        icon="heroicon-m-calendar"
                        class="h-5 w-5 text-gray-400" />
                </button>

                <div
                    x-show="openEnd"
                    x-on:click.away="openEnd = false"
                    x-transition
                    class="absolute z-50 mt-1 w-72 rounded-lg shadow-lg border p-3 fi-temporal-dropdown">
                    {{-- Year selector for end --}}
                    @if($shouldShowYear())
                    <div class="flex items-center justify-between mb-3">
                        <button
                            type="button"
                            x-on:click="previousYear('end')"
                            class="fi-temporal-header-btn">
                            <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5" />
                        </button>
                        <span class="font-semibold" x-text="endYear"></span>
                        <button
                            type="button"
                            x-on:click="nextYear('end')"
                            class="fi-temporal-header-btn">
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5" />
                        </button>
                    </div>
                    @endif

                    {{-- Month grid for end --}}
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; width: 100%;">
                        <template x-for="option in options" :key="'end-' + option.value">
                            <button
                                type="button"
                                x-on:click="selectMonth('end', option.value)"
                                :class="{
                                    'fi-temporal-option-selected': isMonthSelected('end', option.value),
                                    'fi-temporal-option-disabled': isMonthDisabled('end', option.value)
                                }"
                                :disabled="isMonthDisabled('end', option.value)"
                                class="fi-temporal-option"
                                x-text="option.label"></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>

@push('scripts')
<script>
    function monthRangePicker(config) {
        return {
            startField: config.startField,
            endField: config.endField,
            startValue: config.startState,
            endValue: config.endState,
            startYear: null,
            endYear: null,
            openStart: false,
            openEnd: false,
            disabled: config.disabled,
            options: config.options,
            minDate: config.minDate,
            maxDate: config.maxDate,
            yearRange: config.yearRange,
            format: config.format,

            init() {
                const now = new Date();
                this.startYear = this.startValue ? parseInt(this.startValue.split('-')[0]) : now.getFullYear();
                this.endYear = this.endValue ? parseInt(this.endValue.split('-')[0]) : now.getFullYear();

                // Watch for changes
                this.$watch('startValue', (value) => {
                    this.$wire.set(this.startField, value);
                });
                this.$watch('endValue', (value) => {
                    this.$wire.set(this.endField, value);
                });
            },

            selectMonth(type, month) {
                const year = type === 'start' ? this.startYear : this.endYear;
                const value = `${year}-${String(month).padStart(2, '0')}`;

                if (type === 'start') {
                    this.startValue = value;
                    this.openStart = false;

                    // Validate: start cannot be after end
                    if (this.endValue && value > this.endValue) {
                        this.endValue = value;
                    }
                } else {
                    this.endValue = value;
                    this.openEnd = false;

                    // Validate: end cannot be before start
                    if (this.startValue && value < this.startValue) {
                        this.startValue = value;
                    }
                }
            },

            isMonthSelected(type, month) {
                const year = type === 'start' ? this.startYear : this.endYear;
                const value = `${year}-${String(month).padStart(2, '0')}`;
                return type === 'start' ? this.startValue === value : this.endValue === value;
            },

            isMonthDisabled(type, month) {
                if (this.disabled) return true;

                const year = type === 'start' ? this.startYear : this.endYear;
                const value = `${year}-${String(month).padStart(2, '0')}`;

                if (this.minDate && value < this.minDate) return true;
                if (this.maxDate && value > this.maxDate) return true;

                // For end date, cannot be before start
                if (type === 'end' && this.startValue && value < this.startValue) {
                    return true;
                }

                // For start date, cannot be after end
                if (type === 'start' && this.endValue && value > this.endValue) {
                    return true;
                }

                return false;
            },

            getDisplayValue(type) {
                const value = type === 'start' ? this.startValue : this.endValue;
                return value || '';
            },

            previousYear(type) {
                if (type === 'start') {
                    if (this.startYear > this.yearRange.min) {
                        this.startYear--;
                    }
                } else {
                    if (this.endYear > this.yearRange.min) {
                        this.endYear--;
                    }
                }
            },

            nextYear(type) {
                if (type === 'start') {
                    if (this.startYear < this.yearRange.max) {
                        this.startYear++;
                    }
                } else {
                    if (this.endYear < this.yearRange.max) {
                        this.endYear++;
                    }
                }
            },
        };
    }
</script>
@endpush
