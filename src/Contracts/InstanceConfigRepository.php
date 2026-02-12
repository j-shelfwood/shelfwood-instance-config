<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig\Contracts;

interface InstanceConfigRepository
{
    /**
     * Get instance configuration value using dot notation.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Get all instance configuration.
     */
    public function all(): array;

    /**
     * Check if configuration key exists.
     */
    public function has(string $key): bool;

    /**
     * Get current instance ID.
     */
    public function id(): string;

    /**
     * Refresh cached configuration.
     */
    public function refresh(): void;

    /**
     * Get site configuration.
     */
    public function site(?string $key = null, mixed $default = null): mixed;

    /**
     * Get theme configuration.
     */
    public function theme(?string $key = null, mixed $default = null): mixed;

    /**
     * Get properties configuration.
     */
    public function properties(?string $key = null, mixed $default = null): mixed;

    /**
     * Get booking configuration.
     */
    public function booking(?string $key = null, mixed $default = null): mixed;

    /**
     * Get pages configuration.
     */
    public function pages(?string $key = null, mixed $default = null): mixed;

    /**
     * Get PMS configuration.
     */
    public function pms(?string $key = null, mixed $default = null): mixed;

    /**
     * Get services configuration.
     */
    public function services(?string $key = null, mixed $default = null): mixed;

    /**
     * Get mail configuration.
     */
    public function mail(?string $key = null, mixed $default = null): mixed;

    /**
     * Get contact configuration.
     */
    public function contact(?string $key = null, mixed $default = null): mixed;

    /**
     * Get pricing configuration.
     */
    public function pricing(?string $key = null, mixed $default = null): mixed;
}
