<?php

declare(strict_types=1);

use Shelfwood\InstanceConfig\Support\ConfigRouter;

describe('ConfigRouter', function () {
    describe('getFileForKey()', function () {
        it('returns main.md for site prefix', function () {
            $router = new ConfigRouter();

            expect($router->getFileForKey('site.name'))->toBe('main.md');
        });

        it('returns main.md for contact prefix', function () {
            $router = new ConfigRouter();

            expect($router->getFileForKey('contact.email'))->toBe('main.md');
        });

        it('returns theme.md for theme prefix', function () {
            $router = new ConfigRouter();

            expect($router->getFileForKey('theme.colors.primary'))->toBe('theme.md');
        });

        it('returns properties.md for properties prefix', function () {
            $router = new ConfigRouter();

            expect($router->getFileForKey('properties.routing.base_url'))->toBe('properties.md');
        });

        it('returns booking.md for booking prefix', function () {
            $router = new ConfigRouter();

            expect($router->getFileForKey('booking.prepayment'))->toBe('booking.md');
        });

        it('returns main.md for unknown prefix', function () {
            $router = new ConfigRouter();

            expect($router->getFileForKey('custom.value'))->toBe('main.md');
        });

        it('returns main.md for non-prefixed key', function () {
            $router = new ConfigRouter();

            expect($router->getFileForKey('single_key'))->toBe('main.md');
        });

        it('uses custom routing configuration', function () {
            $router = new ConfigRouter(['custom' => 'custom.md']);

            expect($router->getFileForKey('custom.value'))->toBe('custom.md');
        });
    });

    describe('stripPrefix()', function () {
        it('removes theme prefix for theme keys', function () {
            $router = new ConfigRouter();

            expect($router->stripPrefix('theme.colors.primary'))->toBe('colors.primary');
        });

        it('removes properties prefix for properties keys', function () {
            $router = new ConfigRouter();

            expect($router->stripPrefix('properties.routing.base'))->toBe('routing.base');
        });

        it('removes booking prefix for booking keys', function () {
            $router = new ConfigRouter();

            expect($router->stripPrefix('booking.prepayment'))->toBe('prepayment');
        });

        it('keeps site prefix (main.md file)', function () {
            $router = new ConfigRouter();

            expect($router->stripPrefix('site.name'))->toBe('site.name');
        });

        it('keeps contact prefix (main.md file)', function () {
            $router = new ConfigRouter();

            expect($router->stripPrefix('contact.email'))->toBe('contact.email');
        });

        it('returns key unchanged for unknown prefix', function () {
            $router = new ConfigRouter();

            expect($router->stripPrefix('unknown.key'))->toBe('unknown.key');
        });

        it('handles keys without dots', function () {
            $router = new ConfigRouter();

            expect($router->stripPrefix('simple'))->toBe('simple');
        });
    });

    describe('shouldStripPrefix()', function () {
        it('returns true for theme keys', function () {
            $router = new ConfigRouter();

            expect($router->shouldStripPrefix('theme.colors'))->toBeTrue();
        });

        it('returns false for site keys', function () {
            $router = new ConfigRouter();

            expect($router->shouldStripPrefix('site.name'))->toBeFalse();
        });
    });
});
