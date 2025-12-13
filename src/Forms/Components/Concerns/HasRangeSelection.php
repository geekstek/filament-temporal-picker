<?php

namespace Geekstek\TemporalPicker\Forms\Components\Concerns;

use Closure;

trait HasRangeSelection
{
    protected bool | Closure $isRangeSelection = false;

    protected string | Closure $rangeStartKey = 'start';

    protected string | Closure $rangeEndKey = 'end';

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
}
