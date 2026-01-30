<?php

declare(strict_types=1);

namespace Arzou\MimeGuard\Tests;

use Arzou\MimeGuard\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', __DIR__.'/Fixtures/content/assets');
    }
}
