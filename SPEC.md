# shelfwood/instance-config

**Laravel package for multi-instance configuration management**

## Overview

A Laravel package that provides a unified interface for accessing instance-specific configuration. Combines settings loading, content management, and context resolution into a cohesive system with facade, middleware, and helper function support.

## Installation

```bash
composer require shelfwood/instance-config
```

## Requirements

- PHP 8.2+
- Laravel 11.0+
- shelfwood/settings-yaml ^1.0
- shelfwood/multi-tenant-content ^1.0

## Features

- Unified instance configuration facade
- HTTP middleware for instance context resolution
- Global `instance()` helper function
- Automatic routing to appropriate settings files
- Laravel Context integration for queue job propagation
- Configurable instance detection strategies

## Configuration

```php
// config/instance-config.php
return [
    // Default instance when none detected
    'default' => env('INSTANCE_DEFAULT', 'default'),

    // Instance detection strategies (in priority order)
    'detection' => [
        'header' => 'X-Instance',           // HTTP header
        'config' => 'instance.default',     // Config key
        'env' => 'INSTANCE',                // Environment variable
    ],

    // Settings file routing
    'routing' => [
        'site' => 'main.md',
        'contact' => 'main.md',
        'social' => 'main.md',
        'mail' => 'main.md',
        'theme' => 'theme.md',
        'properties' => 'properties.md',
        'booking' => 'booking.md',
    ],

    // Base path for instance directories
    'base_path' => base_path('instance'),

    // Shared directory name
    'shared_directory' => '_shared',
];
```

## API Design

### Instance Facade

```php
use Shelfwood\InstanceConfig\Facades\Instance;

// Get current instance ID
$id = Instance::id();

// Get any configuration value (routed to appropriate file)
$value = Instance::get('site.name');
$value = Instance::get('theme.colors.primary', '#333');

// Check if key exists
if (Instance::has('pms.providers')) {
    // ...
}

// Get all configuration
$all = Instance::all();

// Refresh cached configuration
Instance::refresh();

// Section-specific accessors
$siteName = Instance::site('name');
$theme = Instance::theme();               // All theme settings
$primary = Instance::theme('colors.primary');
$booking = Instance::booking('prepayment_percentage');
$pms = Instance::pms('providers');
$mail = Instance::mail('from.address');
$contact = Instance::contact('phone');
$pricing = Instance::pricing('vat');
```

### Helper Function

```php
// Get settings object
$settings = instance();

// Get specific value
$name = instance('site.name');
$theme = instance('theme.colors.primary', '#333');

// Get instance ID
$id = instance('id');
```

### Middleware

```php
// app/Http/Kernel.php
protected $middleware = [
    \Shelfwood\InstanceConfig\Http\Middleware\ResolveInstanceContext::class,
    // ...
];
```

Middleware behavior:
1. Detects instance from X-Instance header
2. Falls back to config value
3. Falls back to default instance
4. Sets Laravel Context for queue propagation
5. Updates config for backward compatibility

### InstanceConfigRepository Contract

```php
interface InstanceConfigRepository
{
    public function get(string $key, mixed $default = null): mixed;
    public function all(): array;
    public function has(string $key): bool;
    public function id(): string;
    public function refresh(): void;

    // Section accessors
    public function site(?string $key = null, mixed $default = null): mixed;
    public function theme(?string $key = null, mixed $default = null): mixed;
    public function properties(?string $key = null, mixed $default = null): mixed;
    public function booking(?string $key = null, mixed $default = null): mixed;
    public function pages(?string $key = null, mixed $default = null): mixed;
    public function pms(?string $key = null, mixed $default = null): mixed;
    public function services(?string $key = null, mixed $default = null): mixed;
    public function mail(?string $key = null, mixed $default = null): mixed;
    public function contact(?string $key = null, mixed $default = null): mixed;
    public function pricing(?string $key = null, mixed $default = null): mixed;
}
```

## Directory Structure

```
src/
├── InstanceConfig.php                      # Main implementation
├── InstanceConfigServiceProvider.php       # Service provider
├── Facades/
│   └── Instance.php                        # Facade
├── Http/
│   └── Middleware/
│       └── ResolveInstanceContext.php      # Context middleware
├── Contracts/
│   └── InstanceConfigRepository.php        # Repository contract
├── Support/
│   ├── ConfigRouter.php                    # Routes keys to files
│   └── InstanceDetector.php                # Detection strategies
└── helpers.php                             # instance() function

config/
└── instance-config.php

tests/
├── Unit/
│   ├── InstanceConfigTest.php
│   ├── ConfigRouterTest.php
│   └── InstanceDetectorTest.php
├── Feature/
│   ├── MiddlewareTest.php
│   ├── FacadeTest.php
│   └── HelperFunctionTest.php
└── Pest.php
```

## Key Routing

The `instance()` helper and facade automatically route configuration keys to the appropriate settings file:

| Key Prefix | Settings File | Example |
|------------|---------------|---------|
| `site.*` | main.md | `instance('site.name')` |
| `contact.*` | main.md | `instance('contact.email')` |
| `social.*` | main.md | `instance('social.instagram')` |
| `mail.*` | main.md | `instance('mail.from.address')` |
| `pms.*` | main.md | `instance('pms.providers')` |
| `pricing.*` | main.md | `instance('pricing.vat')` |
| `theme.*` | theme.md | `instance('theme.colors.primary')` |
| `properties.*` | properties.md | `instance('properties.routing.base_url')` |
| `booking.*` | booking.md | `instance('booking.prepayment_percentage')` |
| Other | main.md | `instance('custom_key')` |

## Context Propagation

Instance context automatically propagates to:
- Queue jobs (via Laravel Context)
- Log entries (for correlation)
- Child requests

```php
// In a controller
Instance::id(); // 'example.com'

// Dispatched job automatically receives same context
MyJob::dispatch($data);

// In the job
Instance::id(); // 'example.com' (propagated)
```

## Test Coverage Requirements

### Unit Tests (InstanceConfigTest.php)

```php
describe('InstanceConfig', function () {
    describe('get()', function () {
        it('routes site.* keys to main.md');
        it('routes theme.* keys to theme.md');
        it('routes properties.* keys to properties.md');
        it('routes booking.* keys to booking.md');
        it('routes unknown keys to main.md');
        it('returns default for missing keys');
    });

    describe('id()', function () {
        it('returns current instance ID');
        it('returns default when not set');
    });

    describe('has()', function () {
        it('checks existence in correct file');
    });

    describe('all()', function () {
        it('returns all main settings');
    });

    describe('refresh()', function () {
        it('clears settings cache');
    });

    describe('section accessors', function () {
        it('site() returns full settings when no key');
        it('site() returns specific key with dot notation');
        it('theme() routes to theme.md');
        it('booking() routes to booking.md');
        // etc for all section methods
    });
});
```

### Unit Tests (ConfigRouterTest.php)

```php
describe('ConfigRouter', function () {
    describe('getFileForKey()', function () {
        it('returns main.md for site prefix');
        it('returns theme.md for theme prefix');
        it('returns properties.md for properties prefix');
        it('returns booking.md for booking prefix');
        it('returns main.md for unknown prefix');
    });

    describe('stripPrefix()', function () {
        it('removes known prefixes');
        it('returns key unchanged for unknown prefix');
    });
});
```

### Unit Tests (InstanceDetectorTest.php)

```php
describe('InstanceDetector', function () {
    describe('detect()', function () {
        it('detects from X-Instance header');
        it('falls back to config value');
        it('falls back to default instance');
        it('respects priority order');
    });
});
```

### Feature Tests (MiddlewareTest.php)

```php
describe('ResolveInstanceContext Middleware', function () {
    it('sets instance from X-Instance header');
    it('falls back to config when no header');
    it('adds instance to Laravel Context');
    it('updates config for backward compatibility');
    it('propagates to subsequent requests');
});
```

### Feature Tests (FacadeTest.php)

```php
describe('Instance Facade', function () {
    it('provides access to instance ID');
    it('provides access to configuration');
    it('section methods return correct data');
    it('refresh clears cache');
});
```

### Feature Tests (HelperFunctionTest.php)

```php
describe('instance() helper', function () {
    it('returns settings object when no key');
    it('returns instance ID for "id" key');
    it('routes to correct settings file');
    it('returns default for missing keys');
});
```

## Source File Mapping

| Package File | Original Location |
|--------------|-------------------|
| `src/InstanceConfig.php` | `modules/Content/Instance/Services/FileBasedInstanceConfig.php` |
| `src/Contracts/InstanceConfigRepository.php` | `modules/Content/Instance/Contracts/InstanceConfigRepository.php` |
| `src/Facades/Instance.php` | `app/Facades/Instance.php` |
| `src/Http/Middleware/ResolveInstanceContext.php` | `app/Http/Middleware/ResolveInstanceContext.php` |
| `src/helpers.php` | `app/helpers.php` (instance function) |

## Changes from Original

1. **Namespace**: Various → `Shelfwood\InstanceConfig`
2. **Configurable routing**: Settings file routing via config
3. **Configurable detection**: Instance detection strategies via config
4. **Extract ConfigRouter**: Separate class for key→file routing
5. **Extract InstanceDetector**: Separate class for detection logic
6. **Remove hardcoded defaults**: All defaults in config file

## composer.json

```json
{
    "name": "shelfwood/instance-config",
    "description": "Laravel multi-instance configuration management",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Shelfwood",
            "email": "packages@shelfwood.dev"
        }
    ],
    "require": {
        "php": "^8.2",
        "illuminate/support": "^11.0",
        "illuminate/http": "^11.0",
        "illuminate/routing": "^11.0",
        "shelfwood/settings-yaml": "^1.0",
        "shelfwood/multi-tenant-content": "^1.0"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "Shelfwood\\InstanceConfig\\": "src/"
        },
        "files": [
            "src/helpers.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Shelfwood\\InstanceConfig\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Shelfwood\\InstanceConfig\\InstanceConfigServiceProvider"
            ],
            "aliases": {
                "Instance": "Shelfwood\\InstanceConfig\\Facades\\Instance"
            }
        }
    },
    "scripts": {
        "test": "pest",
        "test:coverage": "pest --coverage"
    },
    "config": {
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    },
    "minimum-stability": "stable"
}
```

## Integration with Worktree

After all packages are developed, the worktree integration involves:

1. Add path repositories to worktree composer.json:
```json
{
    "repositories": [
        {"type": "path", "url": "../shelfwood-markdown-frontmatter"},
        {"type": "path", "url": "../shelfwood-settings-yaml"},
        {"type": "path", "url": "../shelfwood-multi-tenant-content"},
        {"type": "path", "url": "../shelfwood-instance-config"}
    ]
}
```

2. Replace internal classes with package imports
3. Remove extracted source files
4. Update namespace references
5. Run full test suite

## Development Checklist

- [ ] Create composer.json
- [ ] Create config/instance-config.php
- [ ] Create InstanceConfigRepository contract
- [ ] Create InstanceConfig implementation
- [ ] Create ConfigRouter support class
- [ ] Create InstanceDetector support class
- [ ] Create ResolveInstanceContext middleware
- [ ] Create Instance facade
- [ ] Create helpers.php with instance() function
- [ ] Create InstanceConfigServiceProvider
- [ ] Configure Pest with Orchestra Testbench
- [ ] Write InstanceConfigTest.php
- [ ] Write ConfigRouterTest.php
- [ ] Write InstanceDetectorTest.php
- [ ] Write MiddlewareTest.php (feature)
- [ ] Write FacadeTest.php (feature)
- [ ] Write HelperFunctionTest.php (feature)
- [ ] Achieve 100% test coverage
- [ ] Add to worktree as path repository
- [ ] Refactor worktree to use package
- [ ] Verify all worktree tests pass
