<?php

declare(strict_types=1);

namespace Geekstek\TemporalPicker;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TemporalPickerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'temporal-picker';

    public static string $viewNamespace = 'temporal-picker';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
    }

    public function packageBooted(): void
    {
        // Register assets with Filament
        FilamentAsset::register(
            assets: $this->getAssets(),
            package: 'geekstek/filament-temporal-picker'
        );
    }

    /**
     * @return array<\Filament\Support\Assets\Asset>
     */
    protected function getAssets(): array
    {
        return [
            // Load the script immediately on page load, not on-demand
            // This ensures the Alpine component is registered before Livewire modals use it
            Js::make('temporal-picker', __DIR__ . '/../resources/dist/temporal-picker.js')
                ->loadedOnRequest(false),
            Css::make('temporal-picker-styles', __DIR__ . '/../resources/dist/temporal-picker.css')
                ->loadedOnRequest(false),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [];
    }
}
