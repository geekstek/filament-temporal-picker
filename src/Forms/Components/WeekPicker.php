<?php

declare(strict_types=1);

namespace Geekstek\TemporalPicker\Forms\Components;

use Closure;

class WeekPicker extends TemporalField
{
    protected string $view = 'temporal-picker::forms.components.week-picker';

    protected int | Closure | null $minYear = null;

    protected int | Closure | null $maxYear = null;

    protected bool | Closure $showWeekNumber = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule('regex:/^\d{4}-W\d{2}$/', static fn (self $component): bool => ! $component->isMultiple());
    }

    public function yearRange(int | Closure | null $min, int | Closure | null $max): static
    {
        $this->minYear = $min;
        $this->maxYear = $max;

        return $this;
    }

    public function minYear(int | Closure | null $year): static
    {
        $this->minYear = $year;

        return $this;
    }

    public function maxYear(int | Closure | null $year): static
    {
        $this->maxYear = $year;

        return $this;
    }

    public function showWeekNumber(bool | Closure $condition = true): static
    {
        $this->showWeekNumber = $condition;

        return $this;
    }

    public function getMinYear(): int
    {
        return $this->evaluate($this->minYear) ?? (int) date('Y') - 5;
    }

    public function getMaxYear(): int
    {
        return $this->evaluate($this->maxYear) ?? (int) date('Y') + 5;
    }

    public function shouldShowWeekNumber(): bool
    {
        return (bool) $this->evaluate($this->showWeekNumber);
    }

    protected function getDefaultFormat(): string
    {
        return config('temporal-picker.formats.week', 'Y-\WW');
    }

    /**
     * Get weeks for the current year as options.
     *
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        $options = [];
        $currentYear = (int) date('Y');

        // Get ISO week count for the year
        $weeksInYear = (int) date('W', mktime(0, 0, 0, 12, 28, $currentYear));

        for ($week = 1; $week <= $weeksInYear; $week++) {
            $weekKey = sprintf('%d-W%02d', $currentYear, $week);
            $options[$weekKey] = __('temporal-picker::temporal-picker.week') . ' ' . $week;
        }

        return $options;
    }

    public function getPickerType(): string
    {
        return 'week';
    }
}
