<?php

use Geekstek\TemporalPicker\Forms\Components\MonthPicker;

it('can be instantiated', function () {
    $picker = MonthPicker::make('test');
    
    expect($picker)->toBeInstanceOf(MonthPicker::class);
});

it('stores single month as string', function () {
    $picker = MonthPicker::make('test')
        ->default('2024-01');
    
    expect($picker->getState())->toBe('2024-01');
});

it('supports multiple selection', function () {
    $picker = MonthPicker::make('test')
        ->multiple()
        ->default(['2024-01', '2024-06', '2024-12']);
    
    expect($picker->isMultiple())->toBeTrue()
        ->and($picker->getState())->toBeArray()
        ->and($picker->getState())->toHaveCount(3)
        ->and($picker->getState())->toContain('2024-01', '2024-06', '2024-12');
});

it('can set min and max dates', function () {
    $picker = MonthPicker::make('test')
        ->minDate('2023-01')
        ->maxDate('2025-12');
    
    expect($picker->getMinDate())->toBe('2023-01')
        ->and($picker->getMaxDate())->toBe('2025-12');
});

it('uses correct format', function () {
    $picker = MonthPicker::make('test')
        ->format('Y-m');
    
    expect($picker->getFormat())->toBe('Y-m');
});

it('uses default format from config', function () {
    $picker = MonthPicker::make('test');
    
    expect($picker->getFormat())->toBe(config('temporal-picker.formats.month', 'Y-m'));
});

it('can set min and max selections for multiple mode', function () {
    $picker = MonthPicker::make('test')
        ->multiple()
        ->minSelections(2)
        ->maxSelections(6);
    
    expect($picker->getMinSelections())->toBe(2)
        ->and($picker->getMaxSelections())->toBe(6);
});

it('can toggle year display', function () {
    $picker = MonthPicker::make('test')
        ->showYear(false);
    
    expect($picker->shouldShowYear())->toBeFalse();
    
    $picker->showYear(true);
    expect($picker->shouldShowYear())->toBeTrue();
});

it('generates correct year range', function () {
    $picker = MonthPicker::make('test')
        ->minDate('2020-01')
        ->maxDate('2030-12');
    
    $yearRange = $picker->getYearRange();
    
    expect($yearRange)->toBeArray()
        ->and($yearRange)->toHaveKeys(['min', 'max'])
        ->and($yearRange['min'])->toBe(2020)
        ->and($yearRange['max'])->toBe(2030);
});

it('provides month options', function () {
    $picker = MonthPicker::make('test');
    $options = $picker->getOptions();
    
    expect($options)->toBeArray()
        ->and($options)->toHaveCount(12)
        ->and($options[0])->toHaveKeys(['value', 'label'])
        ->and($options[0]['value'])->toBe(1);
});

it('does not support range selection', function () {
    $picker = MonthPicker::make('test');
    
    // rangeSelection() method should not exist or should be from parent trait but not used
    expect($picker->isRangeSelection())->toBeFalse();
});
