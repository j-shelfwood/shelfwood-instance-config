<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig\Support;

/**
 * Routes configuration keys to appropriate settings files.
 */
class ConfigRouter
{
    /**
     * Key prefix to file mappings.
     *
     * @var array<string, string>
     */
    private array $routing;

    /**
     * Default file for unmapped keys.
     */
    private string $defaultFile;

    public function __construct(?array $routing = null, string $defaultFile = 'main.md')
    {
        $this->routing = $routing ?? [
            'site' => 'main.md',
            'contact' => 'main.md',
            'social' => 'main.md',
            'mail' => 'main.md',
            'pms' => 'main.md',
            'pricing' => 'main.md',
            'pages' => 'main.md',
            'services' => 'main.md',
            'theme' => 'theme.md',
            'properties' => 'properties.md',
            'booking' => 'booking.md',
        ];
        $this->defaultFile = $defaultFile;
    }

    /**
     * Get the settings file for a configuration key.
     */
    public function getFileForKey(string $key): string
    {
        $prefix = $this->extractPrefix($key);

        return $this->routing[$prefix] ?? $this->defaultFile;
    }

    /**
     * Strip the routing prefix from a key if it maps to a different file.
     */
    public function stripPrefix(string $key): string
    {
        $prefix = $this->extractPrefix($key);
        $file = $this->routing[$prefix] ?? $this->defaultFile;

        // Only strip prefix for non-main files where the prefix IS the file context
        if ($file !== 'main.md' && str_starts_with($key, $prefix.'.')) {
            return substr($key, strlen($prefix) + 1);
        }

        return $key;
    }

    /**
     * Check if a key should have its prefix stripped.
     */
    public function shouldStripPrefix(string $key): bool
    {
        $prefix = $this->extractPrefix($key);
        $file = $this->routing[$prefix] ?? $this->defaultFile;

        return $file !== 'main.md' && str_starts_with($key, $prefix.'.');
    }

    /**
     * Extract the first segment of a dot-notation key.
     */
    private function extractPrefix(string $key): string
    {
        $dotPos = strpos($key, '.');

        return $dotPos !== false ? substr($key, 0, $dotPos) : $key;
    }
}
