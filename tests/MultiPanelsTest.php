<?php

declare(strict_types=1);

namespace MoonShine\MultiPanels\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;
use MoonShine\Laravel\Exceptions\MoonShineNotFoundException;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\MultiPanels\Http\Middleware\MultiPanelMiddleware;
use MoonShine\MultiPanels\MoonShineRouter;
use MoonShine\MultiPanels\MultiPanelPage;
use MoonShine\MultiPanels\MultiPanelResource;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class MultiPanelsTest extends TestCase
{
    #[Test]
    public function it_registers_routes_for_every_panel(): void
    {
        $this->assertTrue(Route::has('moonshine.admin.login'));
        $this->assertTrue(Route::has('moonshine.client.login'));
        $this->assertSame('admin/login', Route::getRoutes()->getByName('moonshine.admin.login')?->uri());
        $this->assertSame('client/login', Route::getRoutes()->getByName('moonshine.client.login')?->uri());
    }

    #[Test]
    public function it_binds_the_panel_aware_router(): void
    {
        $this->assertInstanceOf(MoonShineRouter::class, $this->app->make(RouterContract::class));
    }

    #[Test]
    public function it_applies_the_panel_configuration_to_the_request(): void
    {
        $this->get('/client/login')->assertOk();

        $this->assertSame('Client panel', moonshineConfig()->get('title'));
    }

    #[Test]
    public function resources_and_pages_build_urls_for_their_own_panel(): void
    {
        $core = $this->app->make(CoreContract::class);

        $resource = new class ($core) extends MultiPanelResource {
            protected string $panel = 'client';

            protected string $model = MoonshineUser::class;
        };

        $page = new class ($core) extends MultiPanelPage {
            protected string $panel = 'client';

            protected function components(): iterable
            {
                return [];
            }
        };

        $this->assertStringContainsString('/client/login', $resource->getRouter()->to('login'));
        $this->assertStringContainsString('/client/login', $page->getRouter()->to('login'));
    }

    #[Test]
    public function a_guest_is_sent_to_the_login_of_the_requested_panel(): void
    {
        $this->get('/client')->assertRedirect('/client/login');
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    #[Test]
    public function a_panel_with_its_own_guard_ignores_a_session_from_another_guard(): void
    {
        $this->actingAs($this->user(), 'moonshine');

        $this->get('/client')->assertRedirect('/client/login');
    }

    #[Test]
    public function a_panel_with_its_own_guard_accepts_its_own_session(): void
    {
        $this->actingAs($this->user(), 'client');

        $response = $this->get('/client');

        $this->assertNotSame(Response::HTTP_FOUND, $response->getStatusCode());
    }

    #[Test]
    public function an_unknown_panel_is_not_found(): void
    {
        $this->expectException(MoonShineNotFoundException::class);

        Request::macro('getPanelName', static fn (): string => 'missing');

        $this->app->make(MultiPanelMiddleware::class)->handle(
            Request::create('/missing'),
            static fn (): Response => new Response(),
        );
    }

    private function user(): MoonshineUser
    {
        $user = new MoonshineUser();
        $user->forceFill(['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.test']);
        $user->exists = true;

        return $user;
    }
}
