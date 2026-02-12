<?php

declare(strict_types=1);

namespace Shelfwood\InstanceConfig\Facades;

use Illuminate\Support\Facades\Facade;
use Shelfwood\InstanceConfig\Contracts\InstanceConfigRepository;

/**
 * Instance configuration facade.
 *
 * @method static mixed get(string $key, mixed $default = null)
 * @method static array all()
 * @method static bool has(string $key)
 * @method static string id()
 * @method static void refresh()
 * @method static self setInstance(string $instanceId)
 * @method static mixed site(?string $key = null, mixed $default = null)
 * @method static mixed theme(?string $key = null, mixed $default = null)
 * @method static mixed properties(?string $key = null, mixed $default = null)
 * @method static mixed booking(?string $key = null, mixed $default = null)
 * @method static mixed pages(?string $key = null, mixed $default = null)
 * @method static mixed pms(?string $key = null, mixed $default = null)
 * @method static mixed services(?string $key = null, mixed $default = null)
 * @method static mixed mail(?string $key = null, mixed $default = null)
 * @method static mixed contact(?string $key = null, mixed $default = null)
 * @method static mixed pricing(?string $key = null, mixed $default = null)
 *
 * @see \Shelfwood\InstanceConfig\InstanceConfig
 */
class Instance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InstanceConfigRepository::class;
    }
}
