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

        // State - will be set via initState() with entangled Livewire state
        state: config.state ?? null,
        open: false,
        calendar: null,
        currentYear: new Date().getFullYear(),
        rangeStart: null,
        rangeEnd: null,
        _entangledState: null,
        _initialized: false,

        // Initialize with entangled Livewire state for proper two-way binding
        initState(entangledState) {
            // Store reference to the entangled state
            this._entangledState = entangledState;
            
            // Only set up the property definition once
            if (!this._initialized) {
                this._initialized = true;

                // Create a getter/setter that proxies to the entangled state
                Object.defineProperty(this, 'state', {
                    get: () => this._entangledState,
                    set: (value) => {
                        this._entangledState = value;
                    },
                    configurable: true
                });
            }

            // Parse range state if applicable
            if (this.rangeSelection && this.state) {
                this.rangeStart = this.state.start || null;
                this.rangeEnd = this.state.end || null;
            }
            
            // Update currentYear to match current state
            if (this.state && !this.rangeSelection) {
                const stateStr = String(this.state);
                if (stateStr.length >= 4) {
                    this.currentYear = parseInt(stateStr.substring(0, 4));
                }
            }
        },

        // Update constraints dynamically when Livewire re-renders with new minDate/maxDate
        updateConstraints(newMinDate, newMaxDate) {
            this.minDate = newMinDate;
            this.maxDate = newMaxDate;
        },

        init() {
            // Watch for dropdown open to lazy init calendar
            this.$watch('open', (value) => {
                if (value && this.type === 'week' && !this.calendar && this.$refs.calendar) {
                    // Slight delay to ensure x-show transition has started/element is visible
                    setTimeout(() => this.initWeekCalendar(), 10);
                } else if (value && this.calendar) {
                    this.calendar.update();
                }
            });

            // Watch for Livewire state updates (for range selection)
            this.$watch('_entangledState', (value) => {
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
                let currentState = Array.isArray(this.state) ? [...this.state] : [];

                const index = currentState.indexOf(value);
                if (index === -1) { // if not found
                    currentState.push(value); // add
                } else { // if found
                    currentState.splice(index, 1); // remove
                }

                // Sort for consistent order if values are numbers
                if (typeof value === 'number') {
                    currentState.sort((a, b) => a - b);
                }

                // Assign new array to trigger reactivity
                this.state = currentState;
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
                this.open = false;
            }
            // No need to call syncState() - entanglement handles sync automatically
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
                // No need to call syncState() - entanglement handles sync automatically
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

                // Auto-close if complete (entanglement handles sync automatically)
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
            // Entanglement handles sync automatically
        },

        deselectAll() {
            if (this.disabled || this.readOnly) return;

            this.state = [];
            // Entanglement handles sync automatically
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
            // Deprecated: entanglement now handles state sync automatically
            // This method is kept for backward compatibility
            if (this.$wire) {
                this.$wire.set(this.statePath, this.state);
            }
        },

        clearSelection() {
            if (this.disabled || this.readOnly) return;

            this.state = this.multiple ? [] : null;
            // Entanglement handles sync automatically
            this.open = false;
        },
    };
}

// Make available globally
window.temporalPicker = temporalPicker;

// Register as Alpine component with robust timing handling
(function registerTemporalPicker() {
    let registered = false;
    
    function doRegister(source) {
        if (registered) return;
        if (typeof window.Alpine !== 'undefined' && typeof window.Alpine.data === 'function') {
            window.Alpine.data('temporalPicker', temporalPicker);
            registered = true;
            console.log('temporalPicker Alpine component registered via ' + source);
        }
    }
    
    // Try immediate registration
    doRegister('immediate');
    
    // Listen for alpine:init (in case it fires after our script)
    document.addEventListener('alpine:init', () => doRegister('alpine:init'));
    
    // Listen for livewire:init
    document.addEventListener('livewire:init', () => doRegister('livewire:init'));
    
    // Listen for DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => doRegister('DOMContentLoaded'));
    } else {
        doRegister('DOMContentLoaded-already');
    }
    
    // Poll for Alpine availability (handles all edge cases)
    let attempts = 0;
    const maxAttempts = 100; // 10 seconds max
    const checkInterval = setInterval(() => {
        attempts++;
        doRegister('poll-' + attempts);
        if (registered || attempts >= maxAttempts) {
            clearInterval(checkInterval);
            if (!registered) {
                console.warn('temporalPicker: Failed to register after ' + maxAttempts + ' attempts');
            }
        }
    }, 100);
})();
