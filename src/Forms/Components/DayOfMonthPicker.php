<?php

namespace Geekstek\TemporalPicker\Forms\Components;

use Closure;

class DayOfMonthPicker extends TemporalField
{
    protected string $view = 'temporal-picker::forms.components.day-of-month-picker';

    protected int | Closure $minDay = 1;

    protected int | Closure $maxDay = 31;

    protected bool | Closure $showCalendarGrid = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gridColumns(7);

        $this->rule('integer', static fn (self $component): bool => ! $component->isMultiple());
        $this->rule('array', static fn (self $component): bool => $component->isMultiple());
        $this->rule('min:1');
        $this->rule('max:31');
    }

    public function dayRange(int | Closure $min, int | Closure $max): static
    {
        $this->minDay = $min;
        $this->maxDay = $max;

        return $this;
    }

    public function minDay(int | Closure $day): static
    {
        $this->minDay = $day;

        return $this;
    }

    public function maxDay(int | Closure $day): static
    {
        $this->maxDay = $day;

        return $this;
    }

    public function showCalendarGrid(bool | Closure $condition = true): static
    {
        $this->showCalendarGrid = $condition;

        return $this;
    }

    public function getMinDay(): int
    {
        return max(1, $this->evaluate($this->minDay));
    }

    public function getMaxDay(): int
    {
        return min(31, $this->evaluate($this->maxDay));
    }

    public function shouldShowCalendarGrid(): bool
    {
        return (bool) $this->evaluate($this->showCalendarGrid);
    }

    protected function getDefaultFormat(): string
    {
        return 'd';
    }

    /**
     * @return array<int, string>
     */
    public function getOptions(): array
    {
        $options = [];
        $min = $this->getMinDay();
        $max = $this->getMaxDay();

        for ($day = $min; $day <= $max; $day++) {
            $options[$day] = (string) $day;
        }

        return $options;
    }

    /**
     * Get the picker type for JavaScript.
     */
    public function getPickerType(): string
    {
        return 'dayOfMonth';
    }
}
