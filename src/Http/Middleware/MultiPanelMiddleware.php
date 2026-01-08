<?php

declare(strict_types=1);

namespace MoonShine\MultiPanels\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use Symfony\Component\HttpFoundation\Response;

final class MultiPanelMiddleware
{
    public function __construct(
        private ConfiguratorContract $configurator,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->configurator->get('panels', false) === false) {
            return $next($request);
        }

        $defaultConfig = config('moonshine');

        unset($defaultConfig['panels']);

        $panels = $this->configurator->get('panels', [
            $this->configurator->get('prefix') => $defaultConfig,
        ]);

        $currentPanel = request()->getPanelName();

        if (! $currentConfig = $panels[$currentPanel]) {
            oops404();
        }

        foreach ($currentConfig as $name => $config) {
            $this->configurator->set($name, $config);
        }

        return $next($request);
    }
}
