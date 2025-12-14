console.log('Temporal Picker Script Loaded');
import VanillaCalendar from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/build/vanilla-calendar.min.css';
import '../css/temporal-picker.css';

function temporalPicker(config) {
    return {
        // Configuration
        type: config.type || 'year',
        statePath: config.statePath,
        multiple: config.multiple || false,
        rangeSelection: config.rangeSelection || false,
        disabled: config.disabled || false,
        readOnly: config.readOnly || false,
        disabledOptions: config.disabledOptions || [],
        options: config.options || {},
        format: config.format || null,
        displayFormat: config.displayFormat || null,
        locale: (config.locale || 'en').replace('_', '-'),
        firstDayOfWeek: config.firstDayOfWeek ?? 1,
        valueFormat: config.valueFormat || 'string',

        // Type-specific config
        minYear: config.minYear,
        maxYear: config.maxYear,
        minDate: config.minDate,
        maxDate: config.maxDate,
        yearRange: config.yearRange,
        minDay: config.minDay || 1,
        maxDay: config.maxDay || 31,
        showWeekNumber: config.showWeekNumber ?? true,

        // State
        state: null,
        open: false,
        calendar: null,
        currentYear: new Date().getFullYear(),
        rangeStart: null,
        rangeEnd: null,

        init() {
            // Initialize state from config
            this.state = config.state;

            // Parse range state if applicable
            if (this.rangeSelection && this.state) {
                this.rangeStart = this.state.start || null;
                this.rangeEnd = this.state.end || null;
            }

            // Watch for dropdown open to lazy init calendar
            this.$watch('open', (value) => {
                if (value && this.type === 'week' && !this.calendar && this.$refs.calendar) {
                    // Slight delay to ensure x-show transition has started/element is visible
                    setTimeout(() => this.initWeekCalendar(), 10);
                } else if (value && this.calendar) {
                    this.calendar.update();
                }
            });

            // Watch for Livewire state updates
            this.$watch('state', (value) => {
                if (this.rangeSelection && value) {
                    this.rangeStart = value.start || null;
                    this.rangeEnd = value.end || null;
                }
            });
        },

        initWeekCalendar() {
            console.log('Init WeekCalendar with Range:', this.minYear, this.maxYear);

            const minDate = this.minYear ? `${this.minYear}-01-01` : undefined;
            const maxDate = this.maxYear ? `${this.maxYear}-12-31` : undefined;

            this.calendar = new VanillaCalendar(this.$refs.calendar, {
                type: 'default',
                settings: {
                    lang: this.locale,
                    iso8601: this.firstDayOfWeek === 1,
                    visibility: {
                        weekNumbers: this.showWeekNumber,
                    },
                    range: {
                        min: minDate,
                        max: maxDate,
                        disablePast: !!minDate,
                        disableFuture: !!maxDate,
                    },
                },
                actions: {
                    clickWeekNumber: (e, weekNumber, days, year) => {
                        console.log('Week Clicked:', { weekNumber, year, min: this.minYear, max: this.maxYear });

                        // Strict safety check for boundaries
                        const clickedYear = parseInt(year);
                        const minY = parseInt(this.minYear);
                        const maxY = parseInt(this.maxYear);

                        if (!isNaN(minY) && clickedYear < minY) {
                            console.warn('Blocked past selection');
                            return;
                        }
                        if (!isNaN(maxY) && clickedYear > maxY) {
                            console.warn('Blocked future selection');
                            return;
                        }

                        const weekValue = `${year}-W${String(weekNumber).padStart(2, '0')}`;
                        this.toggleOption(weekValue);
                    },
                    clickArrow: (e, year, month) => {
                        console.log('Arrow Clicked:', { year, month });
                        this.enforceBoundaries(year);
                    },
                    changeMonth: (e, calendar, year, month) => {
                        console.log('Month Changed:', { year, month });
                        this.enforceBoundaries(year);
                    },
                },
            });
            this.calendar.init();
        },

        enforceBoundaries(year) {
            const currentYear = parseInt(year);
            const minY = parseInt(this.minYear);
            const maxY = parseInt(this.maxYear);

            if (!isNaN(minY) && currentYear < minY) {
                console.warn('Navigated before minYear, reverting...');
                // Jump to Jan 01 of minYear
                /* setTimeout to allow render to finish first if needed, 
                   but usually immediate update is better to prevent flash */
                this.calendar.settings.selected.year = minY;
                this.calendar.settings.selected.month = 0;
                this.calendar.update();
                return;
            }

            if (!isNaN(maxY) && currentYear > maxY) {
                console.warn('Navigated after maxYear, reverting...');
                // Jump to Dec 31 of maxYear
                this.calendar.settings.selected.year = maxY;
                this.calendar.settings.selected.month = 11;
                this.calendar.update();
                return;
            }
        },

        toggleOption(value) {
            if (this.disabled || this.readOnly) return;
            if (this.isOptionDisabled(value)) return;

            if (this.multiple) {
                // Multi-select mode
                // Ensure state is an array for multiple selection
                if (!Array.isArray(this.state)) {
                    this.state = [];
                }

                const index = this.state.indexOf(value);
                if (index === -1) { // if not found
                    this.state.push(value); // add
                } else { // if found
                    this.state.splice(index, 1); // remove
                }

                // Sort for consistent order if values are numbers
                if (typeof value === 'number') {
                    this.state.sort((a, b) => a - b);
                }
            } else {
                // Single select mode
                // Strict string comparison to avoid Proxy/Type mismatches
                const currentState = this.state ? String(this.state) : '';
                const targetValue = String(value);

                if (currentState === targetValue) {
                    this.state = null;
                } else {
                    this.state = value;
                }
                this.syncState();
                this.open = false;
            }

            this.syncState();
        },

        selectMonth(month) {
            if (this.disabled || this.readOnly) return;

            const monthStr = String(month).padStart(2, '0');
            const value = `${this.currentYear}-${monthStr}`;

            if (this.rangeSelection) {
                this.handleRangeSelection(value);
            } else if (this.multiple) {
                this.toggleOption(value);
            } else {
                this.state = value;
                this.syncState();
                this.open = false;
            }
        },

        handleRangeSelection(value) {
            console.log('Selecting:', value, 'Start:', this.rangeStart, 'End:', this.rangeEnd);

            try {
                // Logic to set rangeStart and rangeEnd
                if (this.rangeStart && this.rangeEnd) {
                    this.rangeStart = value;
                    this.rangeEnd = null;
                } else if (!this.rangeStart) {
                    this.rangeStart = value;
                    this.rangeEnd = null;
                } else {
                    if (value < this.rangeStart) {
                        this.rangeEnd = this.rangeStart;
                        this.rangeStart = value;
                    } else {
                        this.rangeEnd = value;
                    }
                }

                // Update state object
                this.state = {
                    start: this.rangeStart,
                    end: this.rangeEnd
                };

                console.log('Range computed:', this.state);

                // Attempt to sync
                this.syncState();

                // Auto-close if complete
                if (this.rangeStart && this.rangeEnd) {
                    this.open = false;
                }
            } catch (e) {
                console.error('Error in handleRangeSelection:', e);
                // Ensure UI at least reflects the selection even if sync fails
                if (this.rangeStart && this.rangeEnd) {
                    this.open = false;
                }
            }
        },

        hoverDate: null,

        handleHover(month) {
            if (this.rangeSelection && this.rangeStart && !this.rangeEnd) {
                const monthStr = String(month).padStart(2, '0');
                this.hoverDate = `${this.currentYear}-${monthStr}`;
            }
        },

        handleMouseLeave() {
            this.hoverDate = null;
        },

        selectAll() {
            if (this.disabled || this.readOnly) return;

            const allValues = Object.keys(this.options).map(k => {
                const parsed = parseInt(k);
                return isNaN(parsed) ? k : parsed;
            }).filter(v => !this.isOptionDisabled(v));

            this.state = allValues;
            this.syncState();
        },

        deselectAll() {
            if (this.disabled || this.readOnly) return;

            this.state = [];
            this.syncState();
        },

        previousYear() {
            if (this.yearRange && this.currentYear > this.yearRange.min) {
                this.currentYear--;
            }
        },

        nextYear() {
            if (this.yearRange && this.currentYear < this.yearRange.max) {
                this.currentYear++;
            }
        },

        isSelected(value) {
            if (this.multiple && Array.isArray(this.state)) {
                return this.state.includes(value);
            }
            return this.state === value;
        },

        isMonthSelected(month) {
            const monthStr = String(month).padStart(2, '0');
            const value = `${this.currentYear}-${monthStr}`;

            if (this.rangeSelection) {
                return value === this.rangeStart || value === this.rangeEnd;
            }

            if (this.multiple && Array.isArray(this.state)) {
                return this.state.includes(value);
            }

            return this.state === value;
        },

        isMonthDisabled(month) {
            const monthStr = String(month).padStart(2, '0');
            const value = `${this.currentYear}-${monthStr}`;

            if (this.minDate && value < this.minDate) return true;
            if (this.maxDate && value > this.maxDate) return true;

            return this.disabledOptions.includes(month) || this.disabledOptions.includes(value);
        },

        isInRange(month) {
            if (!this.rangeSelection || !this.rangeStart) return false;

            const monthStr = String(month).padStart(2, '0');
            const value = `${this.currentYear}-${monthStr}`;

            // If range is complete
            if (this.rangeEnd) {
                return value > this.rangeStart && value < this.rangeEnd;
            }

            // If range is incomplete (hover preview)
            if (this.hoverDate) {
                const start = this.rangeStart < this.hoverDate ? this.rangeStart : this.hoverDate;
                const end = this.rangeStart < this.hoverDate ? this.hoverDate : this.rangeStart;
                return value > start && value < end;
            }

            return false;
        },

        isOptionDisabled(value) {
            return this.disabledOptions.includes(value);
        },

        getDisplayValue() {
            if (!this.state) return '';

            if (this.rangeSelection && typeof this.state === 'object') {
                if (this.state.start && this.state.end) {
                    return `${this.state.start} → ${this.state.end}`;
                }
                return this.state.start || '';
            }

            if (this.multiple && Array.isArray(this.state)) {
                if (this.state.length === 0) return '';

                if (this.type === 'weekday') {
                    return this.state.map(v => this.options[v] || v).join(', ');
                }

                return this.state.join(', ');
            }

            if (this.options[this.state]) {
                return this.options[this.state];
            }

            return String(this.state);
        },

        syncState() {
            // Update Livewire state
            this.$wire.set(this.statePath, this.state);
        },

        clearSelection() {
            if (this.disabled || this.readOnly) return;
            
            if (this.multiple) {
                this.state = [];
            } else {
                this.state = null;
            }
            
            this.syncState();
            this.open = false;
        },
    };
}

// Make available globally
window.temporalPicker = temporalPicker;

// Register as Alpine component
const register = () => {
    if (typeof window.Alpine !== 'undefined') {
        window.Alpine.data('temporalPicker', temporalPicker);
    }
};

if (typeof window.Alpine !== 'undefined') {
    register();
} else {
    document.addEventListener('alpine:init', register);
}
