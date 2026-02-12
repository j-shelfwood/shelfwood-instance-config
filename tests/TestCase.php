<?php

namespace Shelfwood\InstanceConfig\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Shelfwood\InstanceConfig\InstanceConfigServiceProvider;
use Shelfwood\SettingsYaml\SettingsServiceProvider;
use Shelfwood\MultiTenantContent\MultiTenantContentServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected string $testBasePath;

    protected function getPackageProviders($app): array
    {
        return [
            SettingsServiceProvider::class,
            MultiTenantContentServiceProvider::class,
            InstanceConfigServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $this->testBasePath = sys_get_temp_dir() . '/instance-config-tests-' . getmypid();

        $app['config']->set('instance-config.default', 'test-instance');
        $app['config']->set('instance-config.base_path', $this->testBasePath);
        $app['config']->set('instance-config.shared_directory', '_shared');

        // Also configure dependent packages
        $app['config']->set('settings-yaml.base_path', $this->testBasePath);
        $app['config']->set('settings-yaml.cache.enabled', false);

        $app['config']->set('filesystems.disks.test-instances', [
            'driver' => 'local',
            'root' => $this->testBasePath,
        ]);
        $app['config']->set('multi-tenant-content.disk', 'test-instances');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create test directories with defaults
        @mkdir("{$this->testBasePath}/_shared", 0755, true);
        @mkdir("{$this->testBasePath}/test-instance", 0755, true);

        // Create minimal shared config
        createInstanceConfig($this->testBasePath, '_shared', 'main.md', [
            'site' => ['name' => 'Default Site'],
            'pricing' => ['vat' => 21],
        ]);

        createInstanceConfig($this->testBasePath, '_shared', 'theme.md', [
            'colors' => ['primary' => '#333333'],
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles($this->testBasePath);

        parent::tearDown();
    }

    private function cleanupTestFiles(string $basePath): void
    {
        if (is_dir($basePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }

            rmdir($basePath);
        }
    }
}
