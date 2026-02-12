<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig\Content\Contracts;

interface PathResolverInterface
{
    public function getTenantId(): string;

    public function getSharedDirectory(): string;

    /**
     * @return array{shared: string, tenant: string}
     */
    public function getContentPaths(string $contentType): array;

    /**
     * @return array{shared: string, tenant: string}
     */
    public function getDirectoryPaths(string $directoryPath): array;

    /**
     * @return array{tenant: string, shared: string}
     */
    public function getSingleContentPaths(string $path): array;

    public function stripPathPrefix(string $fullPath): string;
}
