<?php

namespace Geekstek\TemporalPicker;

use Filament\Contracts\Plugin;
use Filament\Panel;

class TemporalPickerPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'temporal-picker';
    }

    public function register(Panel $panel): void
    {
        // Register any resources, pages, or widgets here
    }

    public function boot(Panel $panel): void
    {
        // Boot logic that should only run when the panel is active
    }
}
