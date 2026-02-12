<?php

declare(strict_types=1);

use Shelfwood\InstanceConfig\InstanceConfig;
use Shelfwood\SettingsYaml\Settings;

describe('InstanceConfig', function () {
    beforeEach(function () {
        // Create additional test settings
        createInstanceConfig($this->testBasePath, 'test-instance', 'main.md', [
            'site' => ['name' => 'Test Instance Site'],
            'contact' => ['email' => 'test@example.com'],
            'pms' => ['providers' => ['mews']],
        ]);

        createInstanceConfig($this->testBasePath, 'test-instance', 'theme.md', [
            'colors' => ['primary' => '#ff0000', 'secondary' => '#00ff00'],
            'fonts' => ['heading' => 'Roboto'],
        ]);

        createInstanceConfig($this->testBasePath, 'test-instance', 'properties.md', [
            'routing' => ['base_url' => 'apartments'],
        ]);

        createInstanceConfig($this->testBasePath, 'test-instance', 'booking.md', [
            'prepayment' => 30,
            'cancellation' => ['policy' => 'flexible'],
        ]);

        Settings::clearCache();
    });

    describe('get()', function () {
        it('routes site.* keys to main.md', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            expect($config->get('site.name'))->toBe('Test Instance Site');
        });

        it('routes theme.* keys to theme.md', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            expect($config->get('theme.colors.primary'))->toBe('#ff0000');
        });

        it('routes properties.* keys to properties.md', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            expect($config->get('properties.routing.base_url'))->toBe('apartments');
        });

        it('routes booking.* keys to booking.md', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            expect($config->get('booking.prepayment'))->toBe(30);
        });

        it('routes unknown keys to main.md', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            expect($config->get('contact.email'))->toBe('test@example.com');
        });

        it('returns default for missing keys', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            expect($config->get('nonexistent.key', 'default'))->toBe('default');
        });
    });

    describe('id()', function () {
        it('returns current instance ID', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('my-instance');

            expect($config->id())->toBe('my-instance');
        });

        it('returns default when not set', function () {
            config(['instance.default' => 'fallback-instance']);

            $config = new InstanceConfig();

            expect($config->id())->toBe('fallback-instance');
        });
    });

    describe('has()', function () {
        it('checks existence in correct file', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            expect($config->has('site.name'))->toBeTrue();
            expect($config->has('theme.colors.primary'))->toBeTrue();
            expect($config->has('nonexistent.key'))->toBeFalse();
        });
    });

    describe('all()', function () {
        it('returns all main settings', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            $all = $config->all();

            expect($all)->toHaveKey('site');
            expect($all['site']['name'])->toBe('Test Instance Site');
        });
    });

    describe('refresh()', function () {
        it('clears settings cache', function () {
            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');

            // Get value to populate cache
            $original = $config->get('site.name');

            // Modify file
            createInstanceConfig($this->testBasePath, 'test-instance', 'main.md', [
                'site' => ['name' => 'Updated Site Name'],
            ]);

            // Refresh
            $config->refresh();

            expect($config->get('site.name'))->toBe('Updated Site Name');
        });
    });

    describe('section accessors', function () {
        beforeEach(function () {
            $this->config = app(InstanceConfig::class);
            $this->config->setInstance('test-instance');
        });

        it('site() returns full settings when no key', function () {
            $site = $this->config->site();

            expect($site)->toBeArray();
            expect($site['name'])->toBe('Test Instance Site');
        });

        it('site() returns specific key with dot notation', function () {
            expect($this->config->site('name'))->toBe('Test Instance Site');
        });

        it('theme() routes to theme.md', function () {
            $theme = $this->config->theme();

            expect($theme)->toHaveKey('colors');
            expect($this->config->theme('colors.primary'))->toBe('#ff0000');
        });

        it('booking() routes to booking.md', function () {
            expect($this->config->booking('prepayment'))->toBe(30);
            expect($this->config->booking('cancellation.policy'))->toBe('flexible');
        });

        it('properties() routes to properties.md', function () {
            expect($this->config->properties('routing.base_url'))->toBe('apartments');
        });

        it('pms() returns pms config from main.md', function () {
            expect($this->config->pms('providers'))->toBe(['mews']);
        });

        it('contact() returns contact config from main.md', function () {
            expect($this->config->contact('email'))->toBe('test@example.com');
        });
    });

    describe('setInstance()', function () {
        it('changes active instance', function () {
            createInstanceConfig($this->testBasePath, 'other-instance', 'main.md', [
                'site' => ['name' => 'Other Instance'],
            ]);
            Settings::clearCache();

            $config = app(InstanceConfig::class);
            $config->setInstance('test-instance');
            expect($config->get('site.name'))->toBe('Test Instance Site');

            $config->setInstance('other-instance');
            expect($config->get('site.name'))->toBe('Other Instance');
        });

        it('returns self for fluent interface', function () {
            $config = app(InstanceConfig::class);

            $result = $config->setInstance('test-instance');

            expect($result)->toBe($config);
        });
    });
});
