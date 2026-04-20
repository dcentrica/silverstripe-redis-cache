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
    /**
     * Redis client.
     *
     * @var \Predis\Client $redis_client 
     */
    protected $redis_client;

    /**
     * @param  \Predis\Client $redis_client 
     */
    public function __construct(Client $redis_client)
    {
        $this->redis_client = $redis_client;
    }

    /**
     * @param  string   $service 
     * @param  string[] $params  
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function create($service, array $params = []): CacheInterface
    {
        $namespace = !empty($params['namespace'])
            ? sprintf('%s_%s', $params['namespace'], hash('sha256', BASE_PATH))
            : hash('sha256', BASE_PATH);

        $defaultLifetime = !empty($params['defaultLifetime'])
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
