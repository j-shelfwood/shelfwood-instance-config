<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Shelfwood\InstanceConfig\InstanceConfig;
use Shelfwood\InstanceConfig\Support\InstanceDetector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve instance context and propagate through request lifecycle.
 *
 * Sets instance ID in Laravel Context for:
 * - Automatic queue job propagation
 * - Log correlation
 * - Request-scoped access without globals
 */
class ResolveInstanceContext
{
    public function __construct(
        private InstanceDetector $detector,
        private InstanceConfig $config
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $instance = $this->detector->detectFromRequest($request);

        // Set in Laravel Context (auto-propagates to queues)
        Context::add('instance', $instance);

        // Update InstanceConfig with resolved instance
        $this->config->setInstance($instance);

        // Maintain existing config for backward compatibility
        config(['instance.default' => $instance]);

        return $next($request);
    }
}
