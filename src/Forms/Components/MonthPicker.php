<?php

declare(strict_types=1);

namespace Geekstek\TemporalPicker\Forms\Components;

use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Carbon;

class MonthPicker extends TemporalField
{
    protected string $view = 'temporal-picker::forms.components.month-picker';

    protected CarbonInterface | string | Closure | null $minDate = null;

    protected CarbonInterface | string | Closure | null $maxDate = null;

    protected bool | Closure $showYear = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Single selection: validate date format
        $this->rule(
            static fn (self $component) => "date_format:{$component->getFormat()}",
            static fn (self $component): bool => ! $component->isMultiple()
        );

        // Multiple selection: validate array with date_format for each element
        $this->rule(
            static fn (self $component) => [
                'array',
                function ($attribute, $value, $fail) use ($component) {
                    if (! is_array($value)) {
                        return;
                    }

                    foreach ($value as $item) {
                        $date = Carbon::createFromFormat($component->getFormat(), $item);
                        if (! $date || $date->format($component->getFormat()) !== $item) {
                            $fail("Each item must be in {$component->getFormat()} format.");

                            return;
                        }
                    }
                },
            ],
            static fn (self $component): bool => $component->isMultiple()
        );
    }

    public function minDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->minDate = $date;

        return $this;
    }

    public function maxDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->maxDate = $date;

        return $this;
    }

    public function showYear(bool | Closure $condition = true): static
    {
        $this->showYear = $condition;

        return $this;
    }

    public function getMinDate(): ?string
    {
        $date = $this->evaluate($this->minDate);

        if ($date instanceof CarbonInterface) {
            return $date->format($this->getFormat());
        }

        return $date;
    }

    public function getMaxDate(): ?string
    {
        $date = $this->evaluate($this->maxDate);

        if ($date instanceof CarbonInterface) {
            return $date->format($this->getFormat());
        }

        return $date;
    }

    public function shouldShowYear(): bool
    {
        return (bool) $this->evaluate($this->showYear);
    }

    protected function getDefaultFormat(): string
    {
        return config('temporal-picker.formats.month', 'Y-m');
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function getOptions(): array
    {
        $options = $this->getMonthLabels();
        $formatted = [];

        foreach ($options as $value => $label) {
            $formatted[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $formatted;
    }

    /**
     * Get the min/max years for the month picker.
     *
     * @return array{min: int, max: int}
     */
    public function getYearRange(): array
    {
        $minDate = $this->getMinDate();
        $maxDate = $this->getMaxDate();

        $minYear = $minDate ? Carbon::createFromFormat($this->getFormat(), $minDate)->year : (int) date('Y') - 10;
        $maxYear = $maxDate ? Carbon::createFromFormat($this->getFormat(), $maxDate)->year : (int) date('Y') + 10;

        return [
            'min' => $minYear,
            'max' => $maxYear,
        ];
    }

    public function getPickerType(): string
    {
        return 'month';
    }
}
