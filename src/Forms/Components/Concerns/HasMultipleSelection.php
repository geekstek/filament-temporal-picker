<?php

namespace Geekstek\TemporalPicker\Forms\Components\Concerns;

use Closure;

trait HasMultipleSelection
{
    protected bool | Closure $isMultiple = false;

    protected int | Closure | null $minSelections = null;

    protected int | Closure | null $maxSelections = null;

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
}
