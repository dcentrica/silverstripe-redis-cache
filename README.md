# Redis Cache for SilverStripe

Enables usage of redis cache for Silverstripe.

![status](https://github.com/pstaender/silverstripe-redis-cache/actions/workflows/ci.yml/badge.svg)

## Requirements

  * SilverStripe v4, v5, or v6
  * Redis/Valkey/DragonFly
  * Tested on PHP 7.3+

## Pre-install

Ensure you have Redis or a Redis-like system installed in your environment and configured within your php ini files to ensure PHP knows where to find it.

To install Redis, a quick Google with your OS name and version, your PHP version, and server and the version that your're working with (E.G. Apache or Nginx) should yield a number of installation instructions such as [this installation instruction example.](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-redis-on-ubuntu-18-04)

Add the following to your php.ini or conf.d/{your-custom-php-config-file}.ini to let PHP know where to communicate with Redis to store session data:
**Note:** Missing this step will lead to login issues when working with providers such as AWS where you have your site running on multiple pods but need sessions to be unified across them.
```
session.save_handler  = redis
session.save_path     = {your_redis_url}
```

In containerised environments, the [Redis](https://pecl.php.net/package/redis) PHP extensions which is available from Pecl.

## Installation and usage

Use composer to pull this module into your project:

```
  $ composer require dcentrica/silverstripe-redis-cache
```

To enable Redis cache in your Silverstripe project, add one or both of the following yaml configs to your project under `/app/_config/` in either their own yaml file, or in an existing file such as `app.yml`.

**Note:** The `REDIS_URL` must be the url of the used redis instance, e.g. `tcp://127.0.0.1:6379`.

## Usage in your project

```yml
---
Name: silverstripe-redis-cache
After:
  - '#corecache'
  - '#assetscache'
Only:
  envvarset: REDIS_URL
---
SilverStripe\Core\Injector\Injector:
  # Redis PHP client
  RedisClient:
    class: Predis\Client
    constructor:
      0: '`MP_REDIS_HOST`'
  # Silverstripe Redis backend
  RedisCacheFactory:
    class: Zeitpulse\RedisCacheFactory
    constructor:
      client: '%$RedisClient'
  SilverStripe\Core\Cache\CacheFactory: '%$RedisCacheFactory'

  # Service-specific cache segements
  Psr\SimpleCache\CacheInterface.DMService_APICache:
    factory: RedisCacheFactory

  # vendor/silverstripe/assets/_config/assetscache.yml
  Psr\SimpleCache\CacheInterface.InterventionBackend_Manipulations:
    factory: RedisCacheFactory
    constructor:
      namespace: 'InterventionBackend_Manipulations'
      defaultLifetime: 600 # 10 mins

  Psr\SimpleCache\CacheInterface.FileShortcodeProvider:
    factory: RedisCacheFactory
    constructor:
      namespace: 'FileShortcodeProvider'
      defaultLifetime: 600 # 10 mins
```

## Usage with flysystem asset storage

```yaml
---
Name: silverstripes3-flysystem-redis
Only:
  envvarset:
    - REDIS_URL
After:
  - '#silverstripes3-flysystem'
---
SilverStripe\Core\Injector\Injector:
  League\Flysystem\Cached\Storage\Memory.public:
    class: League\Flysystem\Cached\Storage\Predis
  League\Flysystem\Cached\Storage\Adapter.public:
    class: League\Flysystem\Cached\Storage\Predis
  League\Flysystem\Cached\Storage\Adapter.protected:
    class: League\Flysystem\Cached\Storage\Predis
```

## Licence

BSD-3

## Credits

Authors: 

* [Philipp Staender](https://github.com/pstaender)
* [Michael Houghton](https://github.com/Oireachtas)
