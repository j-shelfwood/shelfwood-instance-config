<?php

declare(strict_types=1);

use Shelfwood\InstanceConfig\Facades\Instance;
use Shelfwood\SettingsYaml\Settings;

if (! function_exists('instance')) {
    /**
     * Get instance configuration value using dot notation.
     *
     * Routes to appropriate settings file based on key prefix:
     * - site/contact/social/mail → main.md
     * - theme.* → theme.md
     * - properties.* → properties.md
     * - booking.* → booking.md
     * - Other keys → main.md (default)
     *
     * @param  string|null  $key  Configuration key in dot notation (null returns main settings object)
     * @param  mixed  $default  Default value if key is not found
     * @return mixed Configuration value or Settings object
     */
    function instance(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Settings::main(Instance::id());
        }

        if ($key === 'id') {
            return Instance::id();
        }

        return Instance::get($key, $default);
    }
}

if (! function_exists('current_instance')) {
    /**
     * Get current instance ID from Context (request-scoped).
     *
     * Uses Laravel Context which automatically propagates through:
     * - Queue jobs
     * - Logs
     * - Nested service calls
     */
    function current_instance(): string
    {
        return \Illuminate\Support\Facades\Context::get('instance')
            ?? Instance::id();
    }
}
