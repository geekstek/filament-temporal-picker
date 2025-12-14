<?php

namespace Geekstek\TemporalPicker\Forms\Components;

use Carbon\CarbonInterface;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Group;
use Illuminate\Support\Carbon;

class MonthRangePicker extends Field
{
    protected string $view = 'temporal-picker::forms.components.month-range-picker';

    protected string | Closure | null $startField = null;

    protected string | Closure | null $endField = null;

    protected CarbonInterface | string | Closure | null $minDate = null;

    protected CarbonInterface | string | Closure | null $maxDate = null;

    protected bool | Closure $showYear = true;

    protected string | Closure | null $format = null;

    protected string | Closure | null $startLabel = null;

    protected string | Closure | null $endLabel = null;

    /**
     * Configure the start and end field names.
     */
    public function fields(string | Closure $startField, string | Closure $endField): static
    {
        $this->startField = $startField;
        $this->endField = $endField;

        return $this;
    }

    /**
     * Set the minimum selectable date.
     */
    public function minDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->minDate = $date;

        return $this;
    }

    /**
     * Set the maximum selectable date.
     */
    public function maxDate(CarbonInterface | string | Closure | null $date): static
    {
        $this->maxDate = $date;

        return $this;
    }

    /**
     * Configure whether to show the year selector.
     */
    public function showYear(bool | Closure $condition = true): static
    {
        $this->showYear = $condition;

        return $this;
    }

    /**
     * Set the date format.
     */
    public function format(string | Closure | null $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Set custom labels for start and end fields.
     */
    public function labels(string | Closure | null $startLabel, string | Closure | null $endLabel): static
    {
        $this->startLabel = $startLabel;
        $this->endLabel = $endLabel;

        return $this;
    }

    /**
     * Get the start field name.
     */
    public function getStartField(): string
    {
        return $this->evaluate($this->startField) ?? $this->getName() . '_start';
    }

    /**
     * Get the end field name.
     */
    public function getEndField(): string
    {
        return $this->evaluate($this->endField) ?? $this->getName() . '_end';
    }

    /**
     * Get the formatted min date.
     */
    public function getMinDate(): ?string
    {
        $date = $this->evaluate($this->minDate);

        if ($date instanceof CarbonInterface) {
            return $date->format($this->getFormat());
        }

        return $date;
    }

    /**
     * Get the formatted max date.
     */
    public function getMaxDate(): ?string
    {
        $date = $this->evaluate($this->maxDate);

        if ($date instanceof CarbonInterface) {
            return $date->format($this->getFormat());
        }

        return $date;
    }

    /**
     * Check if year selector should be shown.
     */
    public function shouldShowYear(): bool
    {
        return (bool) $this->evaluate($this->showYear);
    }

    /**
     * Get the date format.
     */
    public function getFormat(): string
    {
        return $this->evaluate($this->format) ?? config('temporal-picker.formats.month', 'Y-m');
    }

    /**
     * Get the start field label.
     */
    public function getStartLabel(): string
    {
        return $this->evaluate($this->startLabel) ?? __('temporal-picker::temporal-picker.range.start');
    }

    /**
     * Get the end field label.
     */
    public function getEndLabel(): string
    {
        return $this->evaluate($this->endLabel) ?? __('temporal-picker::temporal-picker.range.end');
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

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function getMonthOptions(): array
    {
        $locale = config('temporal-picker.locale') ?? config('app.locale', 'en');
        $months = [];

        for ($i = 1; $i <= 12; $i++) {
            $date = Carbon::create(2000, $i, 1);
            $months[] = [
                'value' => $i,
                'label' => $date->locale($locale)->monthName,
            ];
        }

        return $months;
    }

    /**
     * Get the state from the model for both start and end fields.
     */
    public function getState(): mixed
    {
        $record = $this->getRecord();

        if (! $record) {
            return null;
        }

        $startField = $this->getStartField();
        $endField = $this->getEndField();

        return [
            'start' => data_get($record, $startField),
            'end' => data_get($record, $endField),
        ];
    }

    /**
     * Set the state to the model for both start and end fields.
     */
    public function saveRelationshipsUsing(?Closure $callback): static
    {
        // Not needed for simple field storage
        return $this;
    }
}
