<p align="center">
<a href="https://packagist.org/packages/moonshine/multi-panels"><img src="https://img.shields.io/packagist/dt/moonshine/multi-panels" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/moonshine/multi-panels"><img src="https://img.shields.io/packagist/v/moonshine/multi-panels" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/moonshine/multi-panels"><img src="https://img.shields.io/packagist/l/moonshine/multi-panels" alt="License"></a>
</p>
<p align="center">
    <a href="https://laravel.com"><img alt="Laravel 10+" src="https://img.shields.io/badge/Laravel-10+-FF2D20?style=for-the-badge&logo=laravel"></a>
    <a href="https://laravel.com"><img alt="PHP 8.2+" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php"></a>
</p>

# MoonShine Multi-Panels

> **⚠️ EXPERIMENTAL BETA VERSION**
>
> This package is under active development. API may change in future versions.

Package for creating multiple admin panels in one MoonShine application with independent settings, routes and interfaces.

## Requirements

- MoonShine 4.4+
- Laravel 10+
- PHP 8.2+

## Installation

```bash
composer require moonshine/multi-panels
```

## Configuration

### 1. Disable default MoonShine routes

Open `config/moonshine.php` and set:

```php
'use_routes' => false,
```

### 2. Add MultiPanelMiddleware

**IMPORTANT:** Add `MultiPanelMiddleware` to the middleware configuration. You can add it at the top-level (for all panels) or to specific panels.

#### Option 1: Add to all panels (recommended)

```php
// config/moonshine.php
'middleware' => [
    // ... other middleware
    \MoonShine\MultiPanels\Http\Middleware\MultiPanelMiddleware::class,
],
```

#### Option 2: Add to specific panels only

```php
// config/moonshine.php
'panels' => [
    'admin' => [
        'prefix' => 'admin',
        'middleware' => [
            'moonshine',
            \MoonShine\MultiPanels\Http\Middleware\MultiPanelMiddleware::class,
        ],
    ],
    'test' => [
        'prefix' => 'test',
        'middleware' => [
            'moonshine',
            \MoonShine\MultiPanels\Http\Middleware\MultiPanelMiddleware::class,
        ],
    ],
],
```

### 3. Configure panels

In the same `config/moonshine.php` file add `panels` section:

```php
'panels' => [
    'admin' => [
        'prefix' => '',
        'title' => 'Admin panel',
    ],
    'test' => [
        'prefix' => 'test',
        'title' => 'Test panel',
        'palette' => SkyPalette::class,
        // 'layout' => AppLayout::class,
    ],
],
```

### Panel configuration structure

Each panel is a key in the `panels` array, where:
- **Key** - unique panel identifier (e.g., `admin`, `test`)
- **Value** - array of panel settings

#### Available panel settings

Inside a panel you can override **any parameter** from the top-level MoonShine configuration:

```php
'panels' => [
    'admin' => [
        'prefix' => '',                    // URL prefix (optional)
        'title' => 'Admin Panel',          // Panel title
        'layout' => AdminLayout::class,    // Custom layout
        'palette' => BluePalette::class,   // Color scheme
        'logo' => '/images/admin-logo.png',
        'logo_small' => '/images/admin-logo-small.png',
        // ... any other settings from moonshine.php
    ],
    'client' => [
        'prefix' => 'client',
        'title' => 'Client Area',
        'layout' => ClientLayout::class,
        'palette' => GreenPalette::class,
        // ... settings for client panel
    ],
],
```

**Important:**
- If a parameter is not specified in the panel, the default value from the top-level configuration is used
- If `prefix` is not specified, the panel key is used as the prefix
- You can set `prefix => ''` for a panel without prefix (root URL)

## Usage

### Binding resources to a panel

To make a resource appear only in a specific panel, extend `MultiPanelResource` and specify the `$panel` property:

```php
<?php

namespace App\MoonShine\Resources;

use MoonShine\MultiPanels\MultiPanelResource;

class CarResource extends MultiPanelResource
{
    protected string $panel = 'test';

    // ... rest of the resource code
}
```

### Binding pages to a panel

Similarly for pages, use `MultiPanelPage`:

```php
<?php

namespace App\MoonShine\Pages;

use MoonShine\MultiPanels\MultiPanelPage;

class Components extends MultiPanelPage
{
    protected string $panel = 'test';

    // ... rest of the page code
}
```

### Custom layouts

You can create a unique layout for each panel:

1. Create a custom Layout class:

```php
<?php

namespace App\MoonShine\Layouts;

use MoonShine\Laravel\Layouts\AppLayout;

class ClientLayout extends AppLayout
{
    protected function getMenu(): array
    {
        return [
            // Your custom menu for client panel
        ];
    }
}
```

2. Specify it in the panel configuration:

```php
'panels' => [
    'client' => [
        'prefix' => 'client',
        'layout' => ClientLayout::class,
        // ...
    ],
],
```

## Usage examples

### Example 1: Admin panel and client panel

```php
// config/moonshine.php
'panels' => [
    'admin' => [
        'prefix' => 'admin',
        'title' => 'Admin Panel',
        'layout' => AdminLayout::class,
        'palette' => BluePalette::class,
    ],
    'client' => [
        'prefix' => 'client',
        'title' => 'Client Area',
        'layout' => ClientLayout::class,
        'palette' => GreenPalette::class,
    ],
],
```

URLs:
- `https://yourdomain.com/admin` - admin panel
- `https://yourdomain.com/client` - client panel

### Example 2: Multiple admin panels with different permissions

```php
'panels' => [
    'superadmin' => [
        'prefix' => '',  // root URL
        'title' => 'Super Admin',
        'palette' => RedPalette::class,
    ],
    'manager' => [
        'prefix' => 'manager',
        'title' => 'Manager Panel',
        'palette' => OrangePalette::class,
    ],
    'support' => [
        'prefix' => 'support',
        'title' => 'Support Panel',
        'palette' => BluePalette::class,
    ],
],
```

## How it works

1. Each panel works as an independent MoonShine application
2. Resources and pages are bound to a specific panel via the `$panel` property
3. Panel configuration overrides global MoonShine settings
4. Routes are built automatically based on the panel prefix
5. The `MultiPanelMiddleware` determines the current panel and applies its settings

## Service Provider Registration

If not registered automatically, add `MultiPanelServiceProvider` in `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    MoonShine\MultiPanels\Providers\MultiPanelServiceProvider::class,
],
```
