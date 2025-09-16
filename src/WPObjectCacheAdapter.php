<?php

namespace Maiorano\ObjectCache;

use Closure;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\CacheItem;
use WP_Object_Cache as WPObjectCache;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WPObjectCacheAdapter implements CacheInterface, AdapterInterface, CacheGroupAwareInterface
{
    private WPObjectCache $cache;
    private static Closure $itemFactory;
    public function __construct(
        WPObjectCache $cache,
    ) {
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

    public function getItem(mixed $key): CacheItem
    {
        $found = false;
        [$key, $group] = explode($this->getGroupSeparator(), $key) + [0 => '', 1 => 'default'];
        $value = $this->cache->get($key, $group, false, $found);
        return (self::$itemFactory)($key, $value, $found, $group);
    }

    public function getItems(array $keys = []): iterable
    {
        foreach ($keys as $key) {
            yield $this->getItem($key);
        }
    }

    public function clear(string $prefix = ''): bool
    {
        if($prefix) {
            return $this->cache->flush_group($prefix);
        }
        return $this->cache->flush();
    }

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

    public function delete(string $key): bool
    {
        return $this->deleteItem($key);
    }

    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function deleteItem(string $key): bool
    {
        if($this->hasItem($key)) {
            $item = $this->getItem($key);
            $meta = $item->getMetadata();
            $this->cache->delete($item->getKey(), $meta['group']);
        }
        return false;
    }

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

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }

    public function commit(): bool
    {
        return true;
    }

    public function addGlobalGroups(array $groups): void
    {
        $this->cache->add_global_groups($groups);
    }

    public function addNonPersistentGroups(array $groups): void
    {
        // Does nothing, WP_Object_Cache does not support non-persistent groups.
    }

    public function switchToBlog(int $blog_id): void
    {
        $this->cache->switch_to_blog($blog_id);
    }

    public function getGroupSeparator(): string
    {
        return CacheGroupAwareInterface::DEFAULT_GROUP_SEPARATOR;
    }

    public function getKeySeparator(): string
    {
        return CacheGroupAwareInterface::DEFAULT_KEY_SEPARATOR;
    }
}