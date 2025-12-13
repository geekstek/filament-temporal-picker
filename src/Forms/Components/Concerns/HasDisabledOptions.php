<?php

namespace Geekstek\TemporalPicker\Forms\Components\Concerns;

use Closure;

trait HasDisabledOptions
{
    /**
     * @var array<int|string>|Closure
     */
    protected array | Closure $disabledOptions = [];

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
}
