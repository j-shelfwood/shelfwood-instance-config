<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig;

use Shelfwood\InstanceConfig\Contracts\InstanceConfigRepository;
use Shelfwood\InstanceConfig\Support\ConfigRouter;
use Shelfwood\InstanceConfig\Support\InstanceDetector;
use Shelfwood\SettingsYaml\Settings;

/**
 * Main implementation of instance configuration management.
 */
class InstanceConfig implements InstanceConfigRepository
{
    private ConfigRouter $router;

    private InstanceDetector $detector;

    private ?string $instanceId = null;

    /**
     * Cached settings objects by filename.
     *
     * @var array<string, Settings>
     */
    private array $settingsCache = [];

    public function __construct(
        ?ConfigRouter $router = null,
        ?InstanceDetector $detector = null
    ) {
        $this->router = $router ?? new ConfigRouter(
            config('instance-config.routing'),
            'main.md'
        );

        $this->detector = $detector ?? new InstanceDetector(
            config('instance-config.detection.header', 'X-Instance'),
            config('instance-config.detection.config', 'instance.default'),
            config('instance-config.default', 'default')
        );
    }

    /**
     * Set the current instance ID.
     */
    public function setInstance(string $instanceId): self
    {
        $this->instanceId = $instanceId;
        $this->settingsCache = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->router->getFileForKey($key);
        $lookupKey = $this->router->stripPrefix($key);

        return $this->loadSettings($file)->get($lookupKey, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->loadSettings('main.md')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $file = $this->router->getFileForKey($key);
        $lookupKey = $this->router->stripPrefix($key);

        return $this->loadSettings($file)->has($lookupKey);
    }

    /**
     * {@inheritdoc}
     */
    public function id(): string
    {
        return $this->instanceId ?? $this->detector->detect();
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(): void
    {
        $this->settingsCache = [];
        $this->instanceId = null;
        Settings::clearCache();
    }

    /**
     * Refresh instance context when instance changes.
     *
     * Called by refresh_instance_services() when switching instances.
     */
    public function refreshInstanceContext(): void
    {
        $this->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function site(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('main.md', 'site', $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function theme(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('theme.md', null, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function properties(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('properties.md', null, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function booking(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('booking.md', null, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function pages(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('main.md', 'pages', $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function pms(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('main.md', 'pms', $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function services(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('main.md', 'services', $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function mail(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('main.md', 'mail', $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function contact(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('main.md', 'contact', $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function pricing(?string $key = null, mixed $default = null): mixed
    {
        return $this->getSectionValue('main.md', 'pricing', $key, $default);
    }

    /**
     * Get a section value from a settings file.
     */
    private function getSectionValue(string $file, ?string $section, ?string $key, mixed $default): mixed
    {
        $settings = $this->loadSettings($file);

        if ($key === null) {
            // Return entire section or entire settings
            return $section ? $settings->get($section, $default) : $settings->all();
        }

        // Build full key with section prefix if applicable
        $fullKey = $section ? "{$section}.{$key}" : $key;

        return $settings->get($fullKey, $default);
    }

    /**
     * Load settings for a file, using cache if available.
     */
    private function loadSettings(string $filename): Settings
    {
        if (! isset($this->settingsCache[$filename])) {
            $this->settingsCache[$filename] = Settings::load(
                $filename,
                $this->id()
            );
        }

        return $this->settingsCache[$filename];
    }
}
