<?php

declare(strict_types=1);

namespace MoonShine\MultiPanels;

use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use MoonShine\Laravel\Pages\Page;

/**
 * @template TResource of CrudResourceContract = CrudResourceContract
 *
 * @extends Page<TResource>
 */
abstract class MultiPanelPage extends Page
{
    protected string $panel = 'admin';

    public function getRouter(): RouterContract
    {
        return parent::getRouter()->withParams([
            'panelName' => $this->panel,
        ]);
    }
}
