<?php

declare(strict_types=1);

namespace Arzou\MimeGuard;

use Arzou\MimeGuard\Listeners\AssetSavingListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Statamic\Events\AssetSaving;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Facades\YAML;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/js/cp.js',
    ];

    protected $viewNamespace = 'mime-guard';

    public function bootAddon(): void
    {
        $this->registerConfig();
        $this->registerTranslations();
        $this->registerListeners();
        $this->registerNavigation();
        $this->registerPermissions();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mime-guard');
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mime-guard.php',
            'mime-guard'
        );

        // Merge saved settings from YAML file
        $this->mergeSavedSettings();

        $this->publishes([
            __DIR__.'/../config/mime-guard.php' => config_path('mime-guard.php'),
        ], 'mime-guard-config');
    }

    protected function mergeSavedSettings(): void
    {
        $settingsPath = storage_path('statamic/addons/mime-guard/settings.yaml');

        if (File::exists($settingsPath)) {
            $saved = YAML::file($settingsPath)->parse();

            if (is_array($saved)) {
                config([
                    'mime-guard.restricted_by_default' => $saved['restricted_by_default'] ?? config('mime-guard.restricted_by_default'),
                    'mime-guard.containers' => $saved['containers'] ?? config('mime-guard.containers'),
                    'mime-guard.blueprints' => $saved['blueprints'] ?? config('mime-guard.blueprints'),
                    'mime-guard.logging.enabled' => $saved['logging']['enabled'] ?? config('mime-guard.logging.enabled'),
                ]);
            }
        }
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(
            __DIR__.'/../resources/lang',
            'mime-guard'
        );

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/mime-guard'),
        ], 'mime-guard-lang');
    }

    protected function registerListeners(): void
    {
        Event::listen(
            AssetSaving::class,
            AssetSavingListener::class
        );
    }

    protected function registerNavigation(): void
    {
        Nav::extend(function ($nav) {
            $nav->tools('MIME Guard')
                ->route('mime-guard.index')
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>')
                ->can('configure mime-guard');
        });
    }

    protected function registerPermissions(): void
    {
        Permission::register('configure mime-guard')
            ->label(__('mime-guard::messages.permission_configure'));
    }
}
