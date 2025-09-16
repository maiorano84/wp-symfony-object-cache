<?php

namespace Maiorano\ObjectCache;

use Closure;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use WP_Object_Cache as WPObjectCache;

/**
 * A class that wraps a WP_Object_Cache instance to work with PSR-6 interfaces.
 * @package Maiorano\ObjectCache
 */
class WPObjectCacheAdapter implements CacheInterface, AdapterInterface, CacheGroupAwareInterface
{
    /**
     * @var Closure
     */
    private static Closure $itemFactory;

    /**
     * @var WPObjectCache
     */
    private WPObjectCache $cache;

    /**
     * @param WPObjectCache $cache
     */
    public function __construct(
        WPObjectCache $cache,
    )
    {
        $this->cache = $cache;
        self::$itemFactory = Closure::bind(static function ($key, $value, $isHit, $group) {
            $item = new CacheItem();
            $item->key = $key;
            $item->value = $value;
            $item->isHit = $isHit;
            $item->metadata['group'] = $group;

            return $item;
        }, null, CacheItem::class);
    }

    /**
     * @param array $keys
     * @return iterable
     */
    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $this->getItem($key);
        }
    }

    /**
     * @param mixed $key
     * @return CacheItem
     */
    public function getItem(mixed $key): CacheItem
    {
        $found = false;
        [$key, $group] = explode($this->getGroupSeparator(), $key) + [0 => '', 1 => 'default'];
        $value = $this->cache->get($key, $group, false, $found);
        return (self::$itemFactory)($key, $value, $found, $group);
    }

    /**
     * @return string
     */
    public function getGroupSeparator(): string
    {
        return CacheGroupAwareInterface::DEFAULT_GROUP_SEPARATOR;
    }

    /**
     * @param string $key
     * @param callable $callback
     * @param float|null $beta
     * @param array|null $metadata
     * @return mixed
     */
    public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null): mixed
    {
        $item = $this->getItem($key);
        if (INF === $beta || !$item->isHit()) {
            $save = true;
            $item->set($callback($item, $save));
            if ($save) {
                $this->save($item);
            }
        }

        return $item->get();
    }

    /**
     * @param CacheItemInterface $item
     * @return bool
     */
    public function save(CacheItemInterface $item): bool
    {
        $meta = $item->getMetadata();
        return $this->cache->set(
            $item->getKey(),
            $item->get(),
            $meta['group'],
            $meta[ItemInterface::METADATA_EXPIRY] ?? 0,
        );
    }

    /**
     * @param string $prefix
     * @return bool
     */
    public function clear(string $prefix = ''): bool
    {
        if ($prefix) {
            return $this->cache->flush_group($prefix);
        }
        return $this->cache->flush();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->deleteItem($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function deleteItem(string $key): bool
    {
        if ($this->hasItem($key)) {
            $item = $this->getItem($key);
            $meta = $item->getMetadata();
            $this->cache->delete($item->getKey(), $meta['group']);
        }
        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteItems(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * @param CacheItemInterface $item
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return true;
    }

    /**
     * @param array $groups
     * @return void
     */
    public function addGlobalGroups(array $groups): void
    {
        $this->cache->add_global_groups($groups);
    }

    /**
     * @param array $groups
     * @return void
     */
    public function addNonPersistentGroups(array $groups): void
    {
        // Does nothing, WP_Object_Cache does not support non-persistent groups.
    }

    /**
     * @param int $blog_id
     * @return void
     */
    public function switchToBlog(int $blog_id): void
    {
        $this->cache->switch_to_blog($blog_id);
    }

    /**
     * @return string
     */
    public function getKeySeparator(): string
    {
        return CacheGroupAwareInterface::DEFAULT_KEY_SEPARATOR;
    }
}