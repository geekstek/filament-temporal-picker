# Filament Temporal Picker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/geekstek/filament-temporal-picker.svg?style=flat-square)](https://packagist.org/packages/geekstek/filament-temporal-picker)
[![Total Downloads](https://img.shields.io/packagist/dt/geekstek/filament-temporal-picker.svg?style=flat-square)](https://packagist.org/packages/geekstek/filament-temporal-picker)

A Filament 4 plugin providing flexible temporal selection components including year, month, week, weekday, and day-of-month pickers with multi-select and range support.

## Features

- 🗓️ **5 Picker Types**: Year, Month, Week, Weekday, Day of Month
- 🔄 **Multi-Select Support**: Select multiple values with min/max constraints
- 📅 **Range Selection**: Select date ranges (start to end)
- 🚫 **Disabled Options**: Block specific options from selection
- 🌐 **Localization**: Full i18n support (English & Chinese included)
- 🎨 **Tailwind CSS 4**: Native dark mode support
- ⚡ **Livewire & Alpine.js**: Reactive and performant

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Filament 4.x

## Installation

```bash
composer require geekstek/filament-temporal-picker
```

### Local Development (Path Repository)

If you are developing this package locally, add the following to your application's `composer.json` before requiring:

```json
"repositories": [
    {
        "type": "path",
        "url": "../path/to/date-picker",
        "options": {
            "symlink": true
        }
    }
]
```

Then run:

```bash
composer require geekstek/filament-temporal-picker @dev
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag="temporal-picker-config"
```

Publish the language files (optional):

```bash
php artisan vendor:publish --tag="temporal-picker-translations"
```

## Usage

### YearPicker

```php
use Geekstek\TemporalPicker\Forms\Components\YearPicker;

YearPicker::make('fiscal_year')
    ->label('Fiscal Year')
    ->range(2020, 2030)
    ->default(now()->year)
    ->multiple()
    ->disabledOptions([2021, 2022])
    ->required();
```

### MonthPicker

```php
use Geekstek\TemporalPicker\Forms\Components\MonthPicker;

MonthPicker::make('billing_period')
    ->label('Billing Period')
    ->minDate('2023-01')
    ->maxDate('2025-12')
    ->format('Y-m')
    ->rangeSelection()  // Enable range selection
    ->locale('zh_CN');
```

### WeekPicker

```php
use Geekstek\TemporalPicker\Forms\Components\WeekPicker;

WeekPicker::make('report_week')
    ->label('Report Week')
    ->yearRange(2023, 2025)
    ->format('Y-\WW')  // ISO week format: 2024-W15
    ->weekStartsOnMonday()
    ->showWeekNumber();
```

### WeekdayPicker

```php
use Geekstek\TemporalPicker\Forms\Components\WeekdayPicker;

WeekdayPicker::make('working_days')
    ->label('Working Days')
    ->multiple()  // Default for weekday picker
    ->asString()  // or ->asInteger()
    ->disabledOptions(['sunday'])
    ->default(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
    ->gridColumns(7)
    ->shortLabels();  // Use Mon, Tue, Wed...
```

### DayOfMonthPicker

```php
use Geekstek\TemporalPicker\Forms\Components\DayOfMonthPicker;

DayOfMonthPicker::make('payment_days')
    ->label('Payment Days')
    ->multiple()
    ->disabledOptions([29, 30, 31])  // Disable dates that may not exist
    ->default([1, 15])
    ->gridColumns(7)
    ->showCalendarGrid();  // Calendar-style grid
```

## Data Storage Formats

| Picker | Single Select | Multiple Select | Range Selection |
|--------|---------------|-----------------|-----------------|
| YearPicker | `2024` (int) | `[2023, 2024, 2025]` | - |
| MonthPicker | `"2024-03"` (string) | `["2024-01", "2024-03"]` | `{"start": "2024-01", "end": "2024-06"}` |
| WeekPicker | `"2024-W15"` (string) | `["2024-W10", "2024-W15"]` | - |
| WeekdayPicker | `"monday"` or `1` | `["monday", "wednesday"]` or `[1, 3]` | - |
| DayOfMonthPicker | `15` (int) | `[1, 15, 28]` | - |

## Configuration

```php
// config/temporal-picker.php

return [
    'locale' => null,  // Defaults to app.locale
    'first_day_of_week' => 1,  // 0 = Sunday, 1 = Monday
    'weekday_format' => 'string',  // 'string' or 'integer'
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

## Localization

Language files are located in `resources/lang/{locale}/temporal-picker.php`.

Included locales:
- `en` - English
- `zh_CN` - Simplified Chinese

To add a new locale, publish the translations and create your language file:

```bash
php artisan vendor:publish --tag="temporal-picker-translations"
```

## Testing

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.
