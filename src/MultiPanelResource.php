<?php

declare(strict_types=1);

namespace MoonShine\MultiPanels;

use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use MoonShine\Crud\Contracts\Page\DetailPageContract;
use MoonShine\Crud\Contracts\Page\FormPageContract;
use MoonShine\Crud\Contracts\Page\IndexPageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @template TData of Model
 * @template-covariant TIndexPage of null|IndexPageContract = null
 * @template-covariant TFormPage of null|FormPageContract = null
 * @template-covariant TDetailPage of null|DetailPageContract = null
 *
 * @extends ModelResource<TData, TIndexPage, TFormPage, TDetailPage>
 */
abstract class MultiPanelResource extends ModelResource
{
    protected string $panel = 'admin';

    public function getRouter(): RouterContract
    {
        return parent::getRouter()->withParams([
            'panelName' => $this->panel,
        ]);
    }
}
