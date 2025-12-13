<?php

use Geekstek\TemporalPicker\Forms\Components\YearPicker;

it('can create a year picker', function () {
    $picker = YearPicker::make('test_year');

    expect($picker)->toBeInstanceOf(YearPicker::class);
    expect($picker->getName())->toBe('test_year');
});

it('can set year range', function () {
    $picker = YearPicker::make('test_year')
        ->range(2020, 2030);

    expect($picker->getMinYear())->toBe(2020);
    expect($picker->getMaxYear())->toBe(2030);
});

it('can enable multiple selection', function () {
    $picker = YearPicker::make('test_year')
        ->multiple();

    expect($picker->isMultiple())->toBeTrue();
});

it('can set disabled options', function () {
    $picker = YearPicker::make('test_year')
        ->disabledOptions([2021, 2022]);

    expect($picker->getDisabledOptions())->toBe([2021, 2022]);
    expect($picker->isOptionDisabled(2021))->toBeTrue();
    expect($picker->isOptionDisabled(2023))->toBeFalse();
});

it('generates correct options', function () {
    $picker = YearPicker::make('test_year')
        ->range(2020, 2023);

    $options = $picker->getOptions();

    expect($options)->toHaveCount(4);
    expect(array_keys($options))->toBe([2023, 2022, 2021, 2020]);
});
