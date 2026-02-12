<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig;

use Illuminate\Support\ServiceProvider;
use Shelfwood\InstanceConfig\Content\Contracts\PathResolverInterface;
use Shelfwood\InstanceConfig\Content\TenantPathResolver;
use Shelfwood\InstanceConfig\Contracts\InstanceConfigRepository;
use Shelfwood\InstanceConfig\Http\Middleware\ResolveInstanceContext;
use Shelfwood\InstanceConfig\Support\ConfigRouter;
use Shelfwood\InstanceConfig\Support\InstanceDetector;

class InstanceConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/instance-config.php',
            'instance-config'
        );

        // Register ConfigRouter
        $this->app->singleton(ConfigRouter::class, function ($app) {
            return new ConfigRouter(
                $app['config']->get('instance-config.routing'),
                'main.md'
            );
        });

        // Register InstanceDetector
        $this->app->singleton(InstanceDetector::class, function ($app) {
            return new InstanceDetector(
                $app['config']->get('instance-config.detection.header', 'X-Instance'),
                $app['config']->get('instance-config.detection.config', 'instance.default'),
                $app['config']->get('instance-config.default', 'default')
            );
        });

        // Register InstanceConfig as singleton
        $this->app->singleton(InstanceConfig::class, function ($app) {
            return new InstanceConfig(
                $app->make(ConfigRouter::class),
                $app->make(InstanceDetector::class)
            );
        });

        // Bind contract to implementation
        $this->app->bind(InstanceConfigRepository::class, InstanceConfig::class);

        // Register PathResolverInterface for content path resolution
        $this->app->bind(PathResolverInterface::class, function ($app) {
            $instanceConfig = $app->make(InstanceConfig::class);
            $sharedDir = $app['config']->get('instance-config.shared_directory', '_shared');

            return new TenantPathResolver($instanceConfig->id(), $sharedDir);
        });

        // Tag as instance-aware for refresh_instance_services()
        $this->app->tag([InstanceConfig::class], 'instance-aware');

        // Register middleware
        $this->app->singleton(ResolveInstanceContext::class, function ($app) {
            return new ResolveInstanceContext(
                $app->make(InstanceDetector::class),
                $app->make(InstanceConfig::class)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/instance-config.php' => config_path('instance-config.php'),
            ], 'instance-config');
        }
    }
}
