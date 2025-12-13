<?php

namespace Geekstek\TemporalPicker\Forms\Components\Concerns;

use Closure;

trait HasLocalization
{
    protected string | Closure | null $locale = null;

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
}
