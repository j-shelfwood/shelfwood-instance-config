<?php

declare(strict_types=1);

use Shelfwood\InstanceConfig\Facades\Instance;
use Shelfwood\SettingsYaml\Settings;

describe('instance() helper', function () {
    beforeEach(function () {
        createInstanceConfig($this->testBasePath, 'helper-test', 'main.md', [
            'site' => ['name' => 'Helper Test Site'],
            'custom' => ['value' => 'custom-data'],
        ]);

        createInstanceConfig($this->testBasePath, 'helper-test', 'theme.md', [
            'colors' => ['primary' => '#112233'],
        ]);

        Settings::clearCache();
        Instance::setInstance('helper-test');
    });

    it('returns settings object when no key', function () {
        $settings = instance();

        expect($settings)->toBeInstanceOf(Settings::class);
    });

    it('returns instance ID for "id" key', function () {
        expect(instance('id'))->toBe('helper-test');
    });

    it('routes to correct settings file', function () {
        expect(instance('site.name'))->toBe('Helper Test Site');
        expect(instance('theme.colors.primary'))->toBe('#112233');
    });

    it('returns default for missing keys', function () {
        expect(instance('missing.key', 'fallback'))->toBe('fallback');
    });

    it('returns null for missing keys without default', function () {
        expect(instance('missing.key'))->toBeNull();
    });
});

describe('current_instance() helper', function () {
    it('returns instance ID from context or config', function () {
        Instance::setInstance('current-test');

        expect(current_instance())->toBe('current-test');
    });
});
