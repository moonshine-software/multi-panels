<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use MoonShine\Laravel\DefaultRoutes;

foreach (moonshineConfig()->get('panels', [
    'admin' => moonshineConfig()->getDefaultRouteGroup(),
]) as $name => $config) {
    $defaultConfig = moonshineConfig()->getDefaultRouteGroup();

    Route::moonshine(static function (Router $router, DefaultRoutes $defaultRoutes) use($config): void {
        $defaultRoutes($router, $config);
    }, groupParameters: [
        'prefix' => isset($config['prefix']) ? $config['prefix'] : $name,
        'domain' => $config['domain'] ?? $defaultConfig['domain'] ?? '',
        'as' => "moonshine.{$name}.",
        'middleware' => $config['middleware'] ?? $defaultConfig['middleware'] ?? [],
    ]);
}
