<?php

namespace Zeitpulse;

use Predis\Client;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Cache\CacheFactory;
use SilverStripe\Core\Injector\Injector;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

class RedisCacheFactory implements CacheFactory
{
    protected $redis_client;

    public function __construct(Client $redis_client)
    {
        $this->redis_client = $redis_client;
    }

    /**
     * @param  string   $service 
     * @param  array    $params  
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function create($service, array $params = []): CacheInterface
    {
        $namespace = isset($params['namespace'])
            ? $params['namespace'] . '_' . md5(BASE_PATH)
            : md5(BASE_PATH);

        $defaultLifetime = isset($params['defaultLifetime'])
            ? $params['defaultLifetime']
            : 0;

        $psr6 = Injector::inst()
            ->createWithArgs(
                RedisAdapter::class,
                [
                    $this->redis_client,
                    $namespace,
                    $defaultLifetime,
                ]
            );

        return Injector::inst()->createWithArgs(Psr16Cache::class, [$psr6]);
    }
}
