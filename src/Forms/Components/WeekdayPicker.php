<?php

namespace Geekstek\TemporalPicker\Forms\Components;

use Closure;

class WeekdayPicker extends TemporalField
{
    protected string $view = 'temporal-picker::forms.components.weekday-picker';

    protected string | Closure $valueFormat = 'string';

    protected bool | Closure $useShortLabels = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Default to multiple selection for weekday picker
        $this->multiple();
        $this->gridColumns(7);
    }

    public function valueFormat(string | Closure $format): static
    {
        $this->valueFormat = $format;

        return $this;
    }

    public function asInteger(): static
    {
        return $this->valueFormat('integer');
    }

    public function asString(): static
    {
        return $this->valueFormat('string');
    }

    public function shortLabels(bool | Closure $condition = true): static
    {
        $this->useShortLabels = $condition;

        return $this;
    }

    public function getValueFormat(): string
    {
        return $this->evaluate($this->valueFormat);
    }

    public function shouldUseShortLabels(): bool
    {
        return (bool) $this->evaluate($this->useShortLabels);
    }

    protected function getDefaultFormat(): string
    {
        return config('temporal-picker.weekday_format', 'string');
    }

    /**
     * @return array<string|int, string>
     */
    public function getOptions(): array
    {
        $isInteger = $this->getValueFormat() === 'integer';
        $useShort = $this->shouldUseShortLabels();
        $langKey = $useShort ? 'weekdays_short' : 'weekdays';

        $weekdays = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        $options = [];

        foreach ($weekdays as $name => $number) {
            $key = $isInteger ? $number : $name;
            $options[$key] = __("temporal-picker::temporal-picker.{$langKey}.{$name}");
        }

        return $options;
    }

    /**
     * Reorder weekdays based on first day of week setting.
     *
     * @return array<string|int, string>
     */
    public function getOrderedOptions(): array
    {
        $options = $this->getOptions();
        $firstDay = $this->getFirstDayOfWeek();

        if ($firstDay === 1) {
            // Monday start - default order
            return $options;
        }

        if ($firstDay === 0) {
            // Sunday start
            $lastKey = array_key_last($options);
            $sunday = [$lastKey => $options[$lastKey]];
            unset($options[$lastKey]);

            return array_merge($sunday, $options);
        }

        return $options;
    }

    /**
     * Get the picker type for JavaScript.
     */
    public function getPickerType(): string
    {
        return 'weekday';
    }
}
