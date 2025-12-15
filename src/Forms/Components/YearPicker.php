<?php

declare(strict_types=1);

namespace Geekstek\TemporalPicker\Forms\Components;

use Closure;

class YearPicker extends TemporalField
{
    protected string $view = 'temporal-picker::forms.components.year-picker';

    protected int | Closure | null $minYear = null;

    protected int | Closure | null $maxYear = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule('integer', static fn (self $component): bool => ! $component->isMultiple());
        $this->rule('array', static fn (self $component): bool => $component->isMultiple());
    }

    public function range(int | Closure | null $min, int | Closure | null $max): static
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

    public function getMinYear(): int
    {
        return $this->evaluate($this->minYear) ?? config('temporal-picker.year_range.min', 1900);
    }

    public function getMaxYear(): int
    {
        return $this->evaluate($this->maxYear) ?? config('temporal-picker.year_range.max', 2100);
    }

    protected function getDefaultFormat(): string
    {
        return config('temporal-picker.formats.year', 'Y');
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function getOptions(): array
    {
        $options = [];
        $min = $this->getMinYear();
        $max = $this->getMaxYear();

        for ($year = $min; $year <= $max; $year++) {
            $options[] = [
                'value' => $year,
                'label' => (string) $year,
            ];
        }

        return $options;
    }

    public function getPickerType(): string
    {
        return 'year';
    }
}
