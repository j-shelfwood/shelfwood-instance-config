<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Shelfwood\InstanceConfig\Support\InstanceDetector;

describe('InstanceDetector', function () {
    describe('detectFromRequest()', function () {
        it('detects from X-Instance header', function () {
            $detector = new InstanceDetector();
            $request = Request::create('/');
            $request->headers->set('X-Instance', 'header-instance');

            expect($detector->detectFromRequest($request))->toBe('header-instance');
        });

        it('falls back to config value', function () {
            config(['instance.default' => 'config-instance']);

            $detector = new InstanceDetector();
            $request = Request::create('/');

            expect($detector->detectFromRequest($request))->toBe('config-instance');
        });

        it('falls back to default instance', function () {
            config(['instance.default' => null]);

            $detector = new InstanceDetector(
                headerName: 'X-Instance',
                configKey: 'instance.default',
                defaultInstance: 'fallback-instance'
            );
            $request = Request::create('/');

            expect($detector->detectFromRequest($request))->toBe('fallback-instance');
        });

        it('respects priority order', function () {
            config(['instance.default' => 'config-instance']);

            $detector = new InstanceDetector();
            $request = Request::create('/');
            $request->headers->set('X-Instance', 'header-instance');

            // Header has higher priority than config
            expect($detector->detectFromRequest($request))->toBe('header-instance');
        });

        it('uses custom header name', function () {
            $detector = new InstanceDetector(headerName: 'X-Custom-Instance');
            $request = Request::create('/');
            $request->headers->set('X-Custom-Instance', 'custom-header-instance');

            expect($detector->detectFromRequest($request))->toBe('custom-header-instance');
        });
    });

    describe('detect()', function () {
        it('returns config value when set', function () {
            config(['instance.default' => 'configured-instance']);

            $detector = new InstanceDetector();

            expect($detector->detect())->toBe('configured-instance');
        });

        it('returns default when no config', function () {
            config(['instance.default' => null]);

            $detector = new InstanceDetector(defaultInstance: 'my-default');

            expect($detector->detect())->toBe('my-default');
        });
    });

    describe('getters', function () {
        it('returns configured header name', function () {
            $detector = new InstanceDetector(headerName: 'X-Custom');

            expect($detector->getHeaderName())->toBe('X-Custom');
        });

        it('returns configured config key', function () {
            $detector = new InstanceDetector(configKey: 'app.instance');

            expect($detector->getConfigKey())->toBe('app.instance');
        });

        it('returns configured default instance', function () {
            $detector = new InstanceDetector(defaultInstance: 'custom-default');

            expect($detector->getDefaultInstance())->toBe('custom-default');
        });
    });
});
