<?php

namespace MoonShine\MultiPanels\Providers;

use MoonShine\MultiPanels\MoonShineRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;

class MultiPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Request::macro('getPanelName', function (): string {
            $panels = moonshineConfig()->get('panels', []);

            $pathPrefix = request()->path() === '' ? '' : (string) Str::of(request()->path())->before('/');

            $panelKey = Collection::make($panels)
                ->mapWithKeys(fn(array $panel, string $key): array => [$key => [
                    ...$panel,
                    'prefix' => $panel['prefix'] ?? $key,
                ]])
                ->filter(fn(array $panel): bool => $panel['prefix'] === $pathPrefix)
                ->keys()
                ->first();

            if($panelKey !== null) {
                return $panelKey;
            }

            return (string) Str::of(request()->route()?->getName() ?? '')
                ->betweenFirst('moonshine.', '.');
        });

        $this->app->bind(RouterContract::class, MoonShineRouter::class);

        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');
    }

    public function boot(): void
    {
        //
    }
}
