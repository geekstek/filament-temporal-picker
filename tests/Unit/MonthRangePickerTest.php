<?php

use Geekstek\TemporalPicker\Forms\Components\MonthRangePicker;

it('can be instantiated', function () {
    $picker = MonthRangePicker::make('test');
    
    expect($picker)->toBeInstanceOf(MonthRangePicker::class);
});

it('can configure field names', function () {
    $picker = MonthRangePicker::make('test')
        ->fields('start_month', 'end_month');
    
    expect($picker->getStartField())->toBe('start_month')
        ->and($picker->getEndField())->toBe('end_month');
});

it('generates default field names from component name', function () {
    $picker = MonthRangePicker::make('campaign_period');
    
    expect($picker->getStartField())->toBe('campaign_period_start')
        ->and($picker->getEndField())->toBe('campaign_period_end');
});

it('can set custom labels', function () {
    $picker = MonthRangePicker::make('test')
        ->labels('Start Month', 'End Month');
    
    expect($picker->getStartLabel())->toBe('Start Month')
        ->and($picker->getEndLabel())->toBe('End Month');
});

it('uses default labels from translation', function () {
    $picker = MonthRangePicker::make('test');
    
    expect($picker->getStartLabel())->toBe(__('temporal-picker::temporal-picker.range.start'))
        ->and($picker->getEndLabel())->toBe(__('temporal-picker::temporal-picker.range.end'));
});

it('can set min and max dates', function () {
    $picker = MonthRangePicker::make('test')
        ->minDate('2023-01')
        ->maxDate('2025-12');
    
    expect($picker->getMinDate())->toBe('2023-01')
        ->and($picker->getMaxDate())->toBe('2025-12');
});

it('uses correct format', function () {
    $picker = MonthRangePicker::make('test')
        ->format('Y-m');
    
    expect($picker->getFormat())->toBe('Y-m');
});

it('uses default format from config', function () {
    $picker = MonthRangePicker::make('test');
    
    expect($picker->getFormat())->toBe(config('temporal-picker.formats.month', 'Y-m'));
});

it('can toggle year display', function () {
    $picker = MonthRangePicker::make('test')
        ->showYear(false);
    
    expect($picker->shouldShowYear())->toBeFalse();
    
    $picker->showYear(true);
    expect($picker->shouldShowYear())->toBeTrue();
});

it('generates correct year range', function () {
    $picker = MonthRangePicker::make('test')
        ->minDate('2020-01')
        ->maxDate('2030-12');
    
    $yearRange = $picker->getYearRange();
    
    expect($yearRange)->toBeArray()
        ->and($yearRange)->toHaveKeys(['min', 'max'])
        ->and($yearRange['min'])->toBe(2020)
        ->and($yearRange['max'])->toBe(2030);
});

it('provides month options', function () {
    $picker = MonthRangePicker::make('test');
    $options = $picker->getMonthOptions();
    
    expect($options)->toBeArray()
        ->and($options)->toHaveCount(12)
        ->and($options[0])->toHaveKeys(['value', 'label'])
        ->and($options[0]['value'])->toBe(1);
});

it('works with carbon dates', function () {
    $picker = MonthRangePicker::make('test')
        ->minDate(\Carbon\Carbon::create(2023, 1, 1))
        ->maxDate(\Carbon\Carbon::create(2025, 12, 31));
    
    expect($picker->getMinDate())->toBe('2023-01')
        ->and($picker->getMaxDate())->toBe('2025-12');
});
