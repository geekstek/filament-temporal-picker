# Filament Temporal Picker

A Filament 4 plugin providing flexible temporal selection components including year, month, week, weekday, and day-of-month pickers with multi-select and range support.

## Installation

```bash
composer require geekstek/filament-temporal-picker
```

## Components

### MonthPicker

Select a month with year navigation.

```php
use Geekstek\TemporalPicker\Forms\Components\MonthPicker;

MonthPicker::make('billing_month')
    ->label('Billing Month')
    ->required()
    ->minDate('2024-01')
    ->maxDate('2025-12')
    ->showYear()
```

**Multiple Selection:**

```php
MonthPicker::make('selected_months')
    ->label('Select Months')
    ->multiple()
    ->minSelections(1)
    ->maxSelections(6)
```

### YearPicker

Select a year from a range.

```php
use Geekstek\TemporalPicker\Forms\Components\YearPicker;

YearPicker::make('fiscal_year')
    ->label('Fiscal Year')
    ->range(2020, 2030)
```

**Inline Grid Mode:**

```php
YearPicker::make('year')
    ->label('Year')
    ->inline()
    ->gridColumns(4)
    ->range(2020, 2025)
```

### WeekPicker

Select a week using a calendar interface.

```php
use Geekstek\TemporalPicker\Forms\Components\WeekPicker;

WeekPicker::make('work_week')
    ->label('Work Week')
    ->yearRange(2024, 2025)
    ->showWeekNumber()
    ->weekStartsOnMonday()
```

### WeekdayPicker

Select one or multiple days of the week.

```php
use Geekstek\TemporalPicker\Forms\Components\WeekdayPicker;

WeekdayPicker::make('working_days')
    ->label('Working Days')
    ->multiple()
    ->shortLabels()
    ->disabledOptions(['saturday', 'sunday'])
```

**Integer Values:**

```php
WeekdayPicker::make('working_days')
    ->asInteger() // Returns 1-7 instead of 'monday'-'sunday'
```

### DayOfMonthPicker

Select one or multiple days of the month (1-31).

```php
use Geekstek\TemporalPicker\Forms\Components\DayOfMonthPicker;

DayOfMonthPicker::make('billing_days')
    ->label('Billing Days')
    ->multiple()
    ->dayRange(1, 28)
    ->disabledOptions([29, 30, 31])
```

**Dropdown Mode:**

```php
DayOfMonthPicker::make('day')
    ->showCalendarGrid(false) // Use dropdown instead of grid
```

## Common Options

All temporal pickers support:

- `multiple(bool)` - Enable multiple selection
- `minSelections(int)` - Minimum selections required (when multiple)
- `maxSelections(int)` - Maximum selections allowed (when multiple)
- `disabledOptions(array)` - Disable specific options
- `locale(string)` - Set component locale
- `format(string)` - Storage format
- `displayFormat(string)` - Display format
- `inline(bool)` - Show inline instead of dropdown
- `gridColumns(int)` - Number of grid columns
- `readOnly()` - Make component read-only
- `disabled()` - Disable the component

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=temporal-picker-config
```

```php
// config/temporal-picker.php
return [
    'locale' => null, // Defaults to app locale
    'first_day_of_week' => 1, // 0 = Sunday, 1 = Monday
    'weekday_format' => 'string', // 'string' or 'integer'
    'year_range' => [
        'min' => 1900,
        'max' => 2100,
    ],
    'formats' => [
        'year' => 'Y',
        'month' => 'Y-m',
        'week' => 'Y-\WW',
        'date' => 'Y-m-d',
    ],
];
```

## Translations

Publish translations:

```bash
php artisan vendor:publish --tag=temporal-picker-translations
```

Available locales: `en`, `zh_CN`

## License

MIT License

