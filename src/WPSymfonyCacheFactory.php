<?php

namespace Maiorano\ObjectCache;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use WP_Object_Cache as WPObjectCache;

final class WPSymfonyCacheFactory
{
    private const WP_SYMFONY_CACHE_DEFAULT_NAMESPACE = 'wp_cache';

    /**
     * @return void
     */
    public static function autoload(): void
    {
        if (self::getConfigOption('WP_SYMFONY_CACHE_ADAPTER', 'wordpress') === 'wordpress') {
            require_once ABSPATH . WPINC . '/class-wp-object-cache.php';
        }
    }

    /**
     * @param string $opt
     * @param string|null $default
     * @return string|null
     */
    private static function getConfigOption(string $opt, ?string $default = null): ?string
    {
        return match (true) {
            defined($opt) => constant($opt) ?: $default,
            getenv($opt) !== false => getenv($opt) ?: $default,
            default => $default,
        };
    }

    /**
     * @return WPCacheInterface
     */
    public static function create(): WPCacheInterface
    {
        $ns = self::getConfigOption('WP_SYMFONY_CACHE_NAMESPACE', self::WP_SYMFONY_CACHE_DEFAULT_NAMESPACE);
        $adapterType = self::getConfigOption('WP_SYMFONY_CACHE_ADAPTER', 'wordpress');
        $adapter = apply_filters('maiorano:object-cache:adapter', self::createAdapterFromConfig($adapterType, $ns));
        return apply_filters('maiorano:object-cache:global-cache', new SymfonyCacheDecorator($adapter, $ns));
    }

    /**
     * @param string $adapterType
     * @param string $ns
     * @return AdapterInterface
     */
    public static function createAdapterFromConfig(string $adapterType, string $ns): AdapterInterface
    {
        return match ($adapterType) {
            'redis' => new RedisTagAwareAdapter(RedisAdapter::createConnection(
                self::getConfigOption('WP_SYMFONY_CACHE_REDIS_DSN', 'redis://localhost:6379')
            ), $ns),
            default => new WPObjectCacheAdapter(new WPObjectCache),
        };
    }
}