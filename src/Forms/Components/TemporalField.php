<?php

namespace Geekstek\TemporalPicker\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\CanBeReadOnly;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Geekstek\TemporalPicker\Forms\Components\Concerns\HasDisabledOptions;
use Geekstek\TemporalPicker\Forms\Components\Concerns\HasLocalization;
use Geekstek\TemporalPicker\Forms\Components\Concerns\HasMultipleSelection;
use Geekstek\TemporalPicker\Forms\Components\Concerns\HasRangeSelection;

abstract class TemporalField extends Field
{
    use CanBeReadOnly;
    use HasDisabledOptions;
    use HasExtraAlpineAttributes;
    use HasLocalization;
    use HasMultipleSelection;
    use HasRangeSelection;

    protected string | Closure | null $format = null;

    protected string | Closure | null $displayFormat = null;

    protected int | Closure $gridColumns = 1;

    protected bool | Closure $isInline = false;

    protected ?int $firstDayOfWeek = null;

    public function format(string | Closure | null $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function displayFormat(string | Closure | null $format): static
    {
        $this->displayFormat = $format;

        return $this;
    }

    public function gridColumns(int | Closure $columns): static
    {
        $this->gridColumns = $columns;

        return $this;
    }

    public function inline(bool | Closure $condition = true): static
    {
        $this->isInline = $condition;

        return $this;
    }

    public function firstDayOfWeek(?int $day): static
    {
        if ($day !== null && ($day < 0 || $day > 7)) {
            $day = null;
        }

        $this->firstDayOfWeek = $day;

        return $this;
    }

    public function weekStartsOnMonday(): static
    {
        return $this->firstDayOfWeek(1);
    }

    public function weekStartsOnSunday(): static
    {
        return $this->firstDayOfWeek(0);
    }

    public function getFormat(): string
    {
        return $this->evaluate($this->format) ?? $this->getDefaultFormat();
    }

    public function getDisplayFormat(): string
    {
        return $this->evaluate($this->displayFormat) ?? $this->getFormat();
    }

    public function getGridColumns(): int
    {
        return $this->evaluate($this->gridColumns);
    }

    public function isInline(): bool
    {
        return (bool) $this->evaluate($this->isInline);
    }

    public function getFirstDayOfWeek(): int
    {
        return $this->firstDayOfWeek ?? config('temporal-picker.first_day_of_week', 1);
    }

    abstract protected function getDefaultFormat(): string;

    /**
     * Get available options for the picker.
     *
     * @return array<int|string, string>
     */
    abstract public function getOptions(): array;
}
