<?php

use Geekstek\TemporalPicker\Forms\Components\WeekdayPicker;

it('can create a weekday picker', function () {
    $picker = WeekdayPicker::make('working_days');

    expect($picker)->toBeInstanceOf(WeekdayPicker::class);
    expect($picker->getName())->toBe('working_days');
});

it('defaults to multiple selection', function () {
    $picker = WeekdayPicker::make('working_days');

    // WeekdayPicker defaults to multiple in setUp()
    expect($picker->isMultiple())->toBeTrue();
});

it('can use string format', function () {
    $picker = WeekdayPicker::make('working_days')
        ->asString();

    expect($picker->getValueFormat())->toBe('string');

    $options = $picker->getOptions();
    expect(array_keys($options))->toContain('monday');
});

it('can use integer format', function () {
    $picker = WeekdayPicker::make('working_days')
        ->asInteger();

    expect($picker->getValueFormat())->toBe('integer');

    $options = $picker->getOptions();
    expect(array_keys($options))->toContain(1);
});

it('can use short labels', function () {
    $picker = WeekdayPicker::make('working_days')
        ->shortLabels();

    expect($picker->shouldUseShortLabels())->toBeTrue();
});

it('can reorder based on first day of week', function () {
    $picker = WeekdayPicker::make('working_days')
        ->weekStartsOnSunday();

    expect($picker->getFirstDayOfWeek())->toBe(0);
});
