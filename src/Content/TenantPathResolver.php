<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig\Content;

use Shelfwood\InstanceConfig\Content\Contracts\PathResolverInterface;

/**
 * Handles resolution of tenant-specific and shared content paths.
 * Centralizes path logic and tenant configuration access.
 */
final readonly class TenantPathResolver implements PathResolverInterface
{
    public function __construct(
        private string $tenantId,
        private string $sharedDirectory = '_shared'
    ) {}

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getSharedDirectory(): string
    {
        return $this->sharedDirectory;
    }

    /**
     * Get paths for a content type directory.
     *
     * @return array{shared: string, tenant: string}
     */
    public function getContentPaths(string $contentType): array
    {
        return [
            'shared' => "{$this->sharedDirectory}/content/{$contentType}",
            'tenant' => "{$this->tenantId}/content/{$contentType}",
        ];
    }

    /**
     * Get paths for a general directory.
     *
     * @return array{shared: string, tenant: string}
     */
    public function getDirectoryPaths(string $directoryPath): array
    {
        return [
            'shared' => "{$this->sharedDirectory}/{$directoryPath}",
            'tenant' => "{$this->tenantId}/{$directoryPath}",
        ];
    }

    /**
     * Get paths for a single content file with tenant priority.
     *
     * @return array{tenant: string, shared: string}
     */
    public function getSingleContentPaths(string $path): array
    {
        return [
            'tenant' => "{$this->tenantId}/{$path}",
            'shared' => "{$this->sharedDirectory}/{$path}",
        ];
    }

    /**
     * Strip tenant or shared prefix from a full path.
     */
    public function stripPathPrefix(string $fullPath): string
    {
        // Match either the shared directory or any tenant ID at the start
        $escapedShared = preg_quote($this->sharedDirectory, '/');

        return preg_replace("/^({$escapedShared}|[^\/]+)\//", '', $fullPath);
    }
}
