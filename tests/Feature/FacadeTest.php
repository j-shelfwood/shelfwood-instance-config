<?php

declare(strict_types=1);

use Shelfwood\InstanceConfig\Facades\Instance;
use Shelfwood\SettingsYaml\Settings;

describe('Instance Facade', function () {
    beforeEach(function () {
        createInstanceConfig($this->testBasePath, 'facade-test', 'main.md', [
            'site' => ['name' => 'Facade Test Site'],
            'contact' => ['phone' => '123-456'],
        ]);

        createInstanceConfig($this->testBasePath, 'facade-test', 'theme.md', [
            'colors' => ['accent' => '#0066cc'],
        ]);

        Settings::clearCache();
        Instance::setInstance('facade-test');
    });

    it('provides access to instance ID', function () {
        expect(Instance::id())->toBe('facade-test');
    });

    it('provides access to configuration', function () {
        expect(Instance::get('site.name'))->toBe('Facade Test Site');
    });

    it('section methods return correct data', function () {
        expect(Instance::site('name'))->toBe('Facade Test Site');
        expect(Instance::contact('phone'))->toBe('123-456');
        expect(Instance::theme('colors.accent'))->toBe('#0066cc');
    });

    it('refresh clears cache', function () {
        $original = Instance::get('site.name');

        createInstanceConfig($this->testBasePath, 'facade-test', 'main.md', [
            'site' => ['name' => 'Refreshed Name'],
        ]);

        Instance::refresh();

        expect(Instance::get('site.name'))->toBe('Refreshed Name');
    });

    it('has() checks key existence', function () {
        expect(Instance::has('site.name'))->toBeTrue();
        expect(Instance::has('nonexistent'))->toBeFalse();
    });

    it('all() returns main settings', function () {
        $all = Instance::all();

        expect($all)->toBeArray();
        expect($all)->toHaveKey('site');
    });

    it('setInstance() changes context', function () {
        createInstanceConfig($this->testBasePath, 'other-facade-test', 'main.md', [
            'site' => ['name' => 'Other Facade Site'],
        ]);
        Settings::clearCache();

        Instance::setInstance('other-facade-test');

        expect(Instance::id())->toBe('other-facade-test');
        expect(Instance::get('site.name'))->toBe('Other Facade Site');
    });
});
