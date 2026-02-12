<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Shelfwood\InstanceConfig\Http\Middleware\ResolveInstanceContext;
use Shelfwood\InstanceConfig\InstanceConfig;

describe('ResolveInstanceContext Middleware', function () {
    it('sets instance from X-Instance header', function () {
        $middleware = app(ResolveInstanceContext::class);
        $request = Request::create('/');
        $request->headers->set('X-Instance', 'header-instance');

        $middleware->handle($request, function () {
            expect(Context::get('instance'))->toBe('header-instance');
            expect(config('instance.default'))->toBe('header-instance');

            return response('OK');
        });
    });

    it('falls back to config when no header', function () {
        config(['instance.default' => 'config-instance']);

        $middleware = app(ResolveInstanceContext::class);
        $request = Request::create('/');

        $middleware->handle($request, function () {
            expect(Context::get('instance'))->toBe('config-instance');

            return response('OK');
        });
    });

    it('adds instance to Laravel Context', function () {
        $middleware = app(ResolveInstanceContext::class);
        $request = Request::create('/');
        $request->headers->set('X-Instance', 'context-test');

        $middleware->handle($request, function () {
            expect(Context::has('instance'))->toBeTrue();
            expect(Context::get('instance'))->toBe('context-test');

            return response('OK');
        });
    });

    it('updates config for backward compatibility', function () {
        config(['instance.default' => 'old-value']);

        $middleware = app(ResolveInstanceContext::class);
        $request = Request::create('/');
        $request->headers->set('X-Instance', 'new-value');

        $middleware->handle($request, function () {
            expect(config('instance.default'))->toBe('new-value');

            return response('OK');
        });
    });

    it('updates InstanceConfig with resolved instance', function () {
        $middleware = app(ResolveInstanceContext::class);
        $request = Request::create('/');
        $request->headers->set('X-Instance', 'resolved-instance');

        $middleware->handle($request, function () {
            $config = app(InstanceConfig::class);
            expect($config->id())->toBe('resolved-instance');

            return response('OK');
        });
    });
});
