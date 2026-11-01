<?php

declare(strict_types=1);

namespace MoonShine\MultiPanels\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use MoonShine\Laravel\Http\Middleware\Authenticate;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Providers\MoonShineServiceProvider;
use MoonShine\MultiPanels\Http\Middleware\MultiPanelMiddleware;
use MoonShine\MultiPanels\Providers\MultiPanelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param Application $app
     *
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            MoonShineServiceProvider::class,
            MultiPanelServiceProvider::class,
        ];
    }

    /**
     * The provider registers panel routes in register(), so panels must be configured before that.
     *
     * @param Application $app
     */
    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('auth.guards.client', ['driver' => 'session', 'provider' => 'moonshine']);
        $app['config']->set('auth.providers.moonshine', ['driver' => 'eloquent', 'model' => MoonshineUser::class]);

        $app['config']->set('moonshine.use_routes', false);
        $app['config']->set('moonshine.use_notifications', false);
        $app['config']->set('moonshine.use_database_notifications', false);
        $app['config']->set('moonshine.middleware', [
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            MultiPanelMiddleware::class,
        ]);
        $app['config']->set('moonshine.panels', [
            'admin' => [
                'prefix' => 'admin',
                'title' => 'Admin panel',
            ],
            'client' => [
                'prefix' => 'client',
                'title' => 'Client panel',
                'auth' => [
                    'enabled' => true,
                    'guard' => 'client',
                    'model' => MoonshineUser::class,
                    'middleware' => [Authenticate::class],
                    'pipelines' => [],
                ],
            ],
        ]);
    }

    /**
     * MoonShine rebinds its configurator on every resolve in unit tests;
     * a real application keeps one instance for the whole request.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(ConfiguratorContract::class, MoonShineConfigurator::class);
    }
}
