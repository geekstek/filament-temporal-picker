<?php

declare(strict_types=1);

namespace Geekstek\TemporalPicker\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\CanBeReadOnly;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

abstract class TemporalField extends Field
{
    use CanBeReadOnly;
    use HasExtraAlpineAttributes;

    // Multiple selection properties
    protected bool | Closure $isMultiple = false;

    protected int | Closure | null $minSelections = null;

    protected int | Closure | null $maxSelections = null;

    // Range selection properties
    protected bool | Closure $isRangeSelection = false;

    protected string | Closure $rangeStartKey = 'start';

    protected string | Closure $rangeEndKey = 'end';

    // Disabled options
    /** @var array<int|string>|Closure */
    protected array | Closure $disabledOptions = [];

    // Localization
    protected string | Closure | null $locale = null;

    // Format properties
    protected string | Closure | null $format = null;

    protected string | Closure | null $displayFormat = null;

    // Layout properties
    protected int | Closure $gridColumns = 1;

    protected bool | Closure $isInline = false;

    protected ?int $firstDayOfWeek = null;

    // ==========================================
    // Multiple Selection Methods
    // ==========================================

    public function multiple(bool | Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function minSelections(int | Closure | null $count): static
    {
        $this->minSelections = $count;

        $this->rule(static function (self $component) {
            $min = $component->getMinSelections();

            return "min:{$min}";
        }, static fn (self $component): bool => $component->isMultiple() && $component->getMinSelections() !== null);

        return $this;
    }

    public function maxSelections(int | Closure | null $count): static
    {
        $this->maxSelections = $count;

        $this->rule(static function (self $component) {
            $max = $component->getMaxSelections();

            return "max:{$max}";
        }, static fn (self $component): bool => $component->isMultiple() && $component->getMaxSelections() !== null);

        return $this;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    public function getMinSelections(): ?int
    {
        return $this->evaluate($this->minSelections);
    }

    public function getMaxSelections(): ?int
    {
        return $this->evaluate($this->maxSelections);
    }

    // ==========================================
    // Range Selection Methods
    // ==========================================

    public function rangeSelection(bool | Closure $condition = true): static
    {
        $this->isRangeSelection = $condition;

        return $this;
    }

    public function rangeKeys(string | Closure $startKey, string | Closure $endKey): static
    {
        $this->rangeStartKey = $startKey;
        $this->rangeEndKey = $endKey;

        return $this;
    }

    public function isRangeSelection(): bool
    {
        return (bool) $this->evaluate($this->isRangeSelection);
    }

    public function getRangeStartKey(): string
    {
        return $this->evaluate($this->rangeStartKey);
    }

    public function getRangeEndKey(): string
    {
        return $this->evaluate($this->rangeEndKey);
    }

    public function getRangeStart(): mixed
    {
        $state = $this->getState();

        if (! is_array($state)) {
            return null;
        }

        return $state[$this->getRangeStartKey()] ?? null;
    }

    public function getRangeEnd(): mixed
    {
        $state = $this->getState();

        if (! is_array($state)) {
            return null;
        }

        return $state[$this->getRangeEndKey()] ?? null;
    }

    // ==========================================
    // Disabled Options Methods
    // ==========================================

    /**
     * @param  array<int|string>|Closure  $options
     */
    public function disabledOptions(array | Closure $options): static
    {
        $this->disabledOptions = $options;

        return $this;
    }

    /**
     * @return array<int|string>
     */
    public function getDisabledOptions(): array
    {
        return $this->evaluate($this->disabledOptions);
    }

    public function isOptionDisabled(mixed $option): bool
    {
        return in_array($option, $this->getDisabledOptions(), strict: true);
    }

    // ==========================================
    // Localization Methods
    // ==========================================

    public function locale(string | Closure | null $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->evaluate($this->locale) ?? config('temporal-picker.locale') ?? config('app.locale');
    }

    /**
     * Get translated weekday names based on locale.
     *
     * @return array<string, string>
     */
    public function getWeekdayLabels(): array
    {
        return [
            'monday' => __('temporal-picker::temporal-picker.weekdays.monday'),
            'tuesday' => __('temporal-picker::temporal-picker.weekdays.tuesday'),
            'wednesday' => __('temporal-picker::temporal-picker.weekdays.wednesday'),
            'thursday' => __('temporal-picker::temporal-picker.weekdays.thursday'),
            'friday' => __('temporal-picker::temporal-picker.weekdays.friday'),
            'saturday' => __('temporal-picker::temporal-picker.weekdays.saturday'),
            'sunday' => __('temporal-picker::temporal-picker.weekdays.sunday'),
        ];
    }

    /**
     * Get translated month names based on locale.
     *
     * @return array<int, string>
     */
    public function getMonthLabels(): array
    {
        return [
            1 => __('temporal-picker::temporal-picker.months_short.1'),
            2 => __('temporal-picker::temporal-picker.months_short.2'),
            3 => __('temporal-picker::temporal-picker.months_short.3'),
            4 => __('temporal-picker::temporal-picker.months_short.4'),
            5 => __('temporal-picker::temporal-picker.months_short.5'),
            6 => __('temporal-picker::temporal-picker.months_short.6'),
            7 => __('temporal-picker::temporal-picker.months_short.7'),
            8 => __('temporal-picker::temporal-picker.months_short.8'),
            9 => __('temporal-picker::temporal-picker.months_short.9'),
            10 => __('temporal-picker::temporal-picker.months_short.10'),
            11 => __('temporal-picker::temporal-picker.months_short.11'),
            12 => __('temporal-picker::temporal-picker.months_short.12'),
        ];
    }

    // ==========================================
    // Format Methods
    // ==========================================

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

    public function getFormat(): string
    {
        return $this->evaluate($this->format) ?? $this->getDefaultFormat();
    }

    public function getDisplayFormat(): string
    {
        return $this->evaluate($this->displayFormat) ?? $this->getFormat();
    }

    // ==========================================
    // Layout Methods
    // ==========================================

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

    // ==========================================
    // Abstract Methods
    // ==========================================

    abstract protected function getDefaultFormat(): string;

    /**
     * Get available options for the picker.
     *
     * @return array<int|string, string>
     */
    abstract public function getOptions(): array;

    /**
     * Get the picker type for JavaScript.
     */
    abstract public function getPickerType(): string;
}
