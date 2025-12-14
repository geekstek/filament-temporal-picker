# Filament Temporal Picker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/geekstek/filament-temporal-picker.svg?style=flat-square)](https://packagist.org/packages/geekstek/filament-temporal-picker)
[![Total Downloads](https://img.shields.io/packagist/dt/geekstek/filament-temporal-picker.svg?style=flat-square)](https://packagist.org/packages/geekstek/filament-temporal-picker)

A Filament 4 plugin providing flexible temporal selection components including year, month, week, weekday, and day-of-month pickers with multi-select and range support.

## Features

- 🗓️ **5 Picker Types**: Year, Month, Week, Weekday, Day of Month
- 🔄 **Multi-Select Support**: Select multiple values with min/max constraints
- 🚫 **Disabled Options**: Block specific options from selection
- 🌐 **Localization**: Full i18n support (English & Chinese included)
- 🎨 **Tailwind CSS 4**: Native dark mode support
- ⚡ **Livewire & Alpine.js**: Reactive and performant
- 💾 **Flexible Storage**: Single values or JSON arrays for multiple selections

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

Select a single month or multiple months.

```php
use Geekstek\TemporalPicker\Forms\Components\MonthPicker;

// Single month selection
MonthPicker::make('billing_month')
    ->label('Billing Month')
    ->minDate('2023-01')
    ->maxDate('2025-12')
    ->format('Y-m')
    ->default('2024-01')
    ->locale('zh_CN');

// Multiple months selection
MonthPicker::make('available_months')
    ->label('Available Months')
    ->multiple()
    ->minDate('2024-01')
    ->maxDate('2024-12')
    ->default(['2024-01', '2024-03', '2024-06'])
    ->minSelections(1)
    ->maxSelections(6);
```

#### Month Range Selection Pattern

To create a month range selector, use two `MonthPicker` fields with reactive validation:

```php
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Geekstek\TemporalPicker\Forms\Components\MonthPicker;

Grid::make(2)
    ->schema([
        MonthPicker::make('campaign_start')
            ->label('Start Month')
            ->minDate('2024-01')
            ->maxDate(fn (Get $get) => $get('campaign_end') ?? '2025-12')
            ->live()
            ->afterStateUpdated(function ($state, $set, Get $get) {
                $endValue = $get('campaign_end');
                if ($state && $endValue && $state > $endValue) {
                    $set('campaign_end', null);
                }
            }),
            
        MonthPicker::make('campaign_end')
            ->label('End Month')
            ->minDate(fn (Get $get) => $get('campaign_start') ?? '2024-01')
            ->maxDate('2025-12')
            ->live()
            ->afterStateUpdated(function ($state, $set, Get $get) {
                $startValue = $get('campaign_start');
                if ($state && $startValue && $state < $startValue) {
                    $set('campaign_start', null);
                }
            }),
    ]);
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

| Picker | Single Select | Multiple Select |
|--------|---------------|-----------------|
| YearPicker | `2024` (int) | `[2023, 2024, 2025]` (JSON array) |
| MonthPicker | `"2024-03"` (string) | `["2024-01", "2024-03"]` (JSON array) |
| WeekPicker | `"2024-W15"` (string) | `["2024-W10", "2024-W15"]` (JSON array) |
| WeekdayPicker | `"monday"` or `1` | `["monday", "wednesday"]` or `[1, 3]` (JSON array) |
| DayOfMonthPicker | `15` (int) | `[1, 15, 28]` (JSON array) |

### Database Schema Recommendations

For fields using **multiple selection**, use a JSON column type:

```php
// Migration example
Schema::create('schedules', function (Blueprint $table) {
    $table->id();
    
    // Single selection - use string or date column
    $table->string('billing_month', 7)->nullable();  // Format: Y-m
    
    // Multiple selection - use JSON column
    $table->json('available_months')->nullable();
    
    // Range selection pattern - use two separate columns
    $table->string('campaign_start', 7)->nullable();
    $table->string('campaign_end', 7)->nullable();
    $table->index(['campaign_start', 'campaign_end']);  // Recommended for queries
    
    $table->timestamps();
});

// Model cast example
protected function casts(): array
{
    return [
        'billing_month' => 'string',
        'available_months' => 'array',  // Auto JSON encode/decode
        'campaign_start' => 'string',
        'campaign_end' => 'string',
    ];
}
```

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

## Complete Usage Example

Here's a complete example showing all pickers in a Filament Resource:

```php
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Geekstek\TemporalPicker\Forms\Components\{
    YearPicker,
    MonthPicker,
    WeekPicker,
    WeekdayPicker,
    DayOfMonthPicker
};

public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Section::make('Temporal Fields')
            ->schema([
                // Single year
                YearPicker::make('fiscal_year')
                    ->label('Fiscal Year')
                    ->default(now()->year),
                
                // Multiple years
                YearPicker::make('target_years')
                    ->label('Target Years')
                    ->multiple()
                    ->default([2024, 2025]),
                
                // Single month
                MonthPicker::make('billing_month')
                    ->label('Billing Month')
                    ->default('2024-01'),
                
                // Multiple months
                MonthPicker::make('available_months')
                    ->label('Available Months')
                    ->multiple()
                    ->default(['2024-01', '2024-06', '2024-12']),
                
                // Month range pattern (two separate fields)
                Grid::make(2)
                    ->schema([
                        MonthPicker::make('campaign_start')
                            ->label('Campaign Start')
                            ->live()
                            ->maxDate(fn (Get $get) => $get('campaign_end')),
                        
                        MonthPicker::make('campaign_end')
                            ->label('Campaign End')
                            ->live()
                            ->minDate(fn (Get $get) => $get('campaign_start')),
                    ]),
                
                // Week picker
                WeekPicker::make('report_week')
                    ->label('Report Week')
                    ->format('Y-\WW')
                    ->weekStartsOnMonday(),
                
                // Weekday picker (for recurring schedules)
                WeekdayPicker::make('working_days')
                    ->label('Working Days')
                    ->multiple()
                    ->asString()
                    ->default(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                
                // Day of month (for monthly recurring payments)
                DayOfMonthPicker::make('payment_days')
                    ->label('Payment Days')
                    ->multiple()
                    ->default([1, 15])
                    ->disabledOptions([29, 30, 31]),
            ]),
    ]);
}
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
