<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig\Support;

use Illuminate\Http\Request;

/**
 * Detects instance ID from various sources.
 */
class InstanceDetector
{
    public function __construct(
        private string $headerName = 'X-Instance',
        private string $configKey = 'instance.default',
        private string $defaultInstance = 'default'
    ) {}

    /**
     * Detect instance from request.
     */
    public function detectFromRequest(Request $request): string
    {
        // 1. HTTP header (API/testing)
        if ($header = $request->header($this->headerName)) {
            return $header;
        }

        // 2. Existing config value
        if ($existing = config($this->configKey)) {
            return $existing;
        }

        // 3. Default fallback
        return $this->defaultInstance;
    }

    /**
     * Detect instance without request context.
     */
    public function detect(): string
    {
        // 1. Laravel Context (queue propagation)
        if ($contextInstance = \Illuminate\Support\Facades\Context::get('instance')) {
            return $contextInstance;
        }

        // 2. Config value
        if ($configInstance = config($this->configKey)) {
            return $configInstance;
        }

        // 3. Default
        return $this->defaultInstance;
    }

    /**
     * Get the configured header name.
     */
    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    /**
     * Get the configured config key.
     */
    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    /**
     * Get the default instance.
     */
    public function getDefaultInstance(): string
    {
        return $this->defaultInstance;
    }
}
