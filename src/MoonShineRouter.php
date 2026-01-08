<?php

declare(strict_types=1);

namespace MoonShine\MultiPanels;

use MoonShine\Laravel\DependencyInjection\MoonShineRouter as BaseRouter;

final class MoonShineRouter extends BaseRouter
{
    public function to(string $name = '', array $params = []): string
    {
        $prefix = $this->getParam('panelName', $params['panelName'] ?? request()->getPanelName());

        $this->forgetParam('panelName');
        unset($params['panelName']);

        return route(
            $this->getName(($prefix ? "$prefix." : '') . $name),
            $this->getParams($params)
        );
    }
}
