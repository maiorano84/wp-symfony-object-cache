<?php

namespace Maiorano\ObjectCache;

use Generator;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * A class that wraps a Symfony Cache Adapter to work with WordPress caching functions.
 * @package Maiorano\ObjectCache
 */
final class SymfonyCacheDecorator implements WPCacheInterface
{
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $adapter;

    /**
     * @param AdapterInterface $cache
     */
    public function __construct(AdapterInterface $cache)
    {
        $this->adapter = $cache;
    }

    /**
     * @param array $data
     * @param string $group
     * @param int $expire
     * @return Generator
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function addMultiple(array $data, string $group = '', int $expire = 0): Generator
    {
        foreach ($data as $key => $value) {
            yield $key => $this->add($key, $value, $group, $expire);
        }
    }

    /**
     * @param int|string $key
     * @param mixed $data
     * @param string $group
     * @param int $expire
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function add(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
    {
        $k = $this->normalizeKeyGroup($key, $group);
        if (!$this->adapter->hasItem($k)) {
            return $this->set($key, $data, $group, $expire);
        }
        return false;
    }

    /**
     * @param int|string $key
     * @param string $group
     * @return string
     */
    private function normalizeKeyGroup(int|string $key, string $group = ''): string
    {
        $k = $group ? implode($this->getGroupSeparator(), [$key, $group]) : (string)$key;
        try {
            CacheItem::validateKey($k);
            return $k;
        } catch (InvalidArgumentException $e) {
            return preg_replace('/[' . preg_quote(ItemInterface::RESERVED_CHARACTERS, '/') . ']/', $this->getKeySeparator(), $k);
        }
    }

    /**
     * @return string
     */
    public function getGroupSeparator(): string
    {
        if ($this->adapter instanceof CacheGroupAwareInterface) {
            return $this->adapter->getGroupSeparator();
        }
        return CacheGroupAwareInterface::DEFAULT_GROUP_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getKeySeparator(): string
    {
        if ($this->adapter instanceof CacheGroupAwareInterface) {
            return $this->adapter->getKeySeparator();
        }
        return CacheGroupAwareInterface::DEFAULT_KEY_SEPARATOR;
    }

    /**
     * @param int|string $key
     * @param mixed $data
     * @param string $group
     * @param int $expire
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function set(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
    {
        $k = $this->normalizeKeyGroup($key, $group);
        $item = $this->adapter->getItem($k)->set($data);

        if ($expire > 0) {
            $item->expiresAfter($expire);
        }
        return $this->adapter->save($item);
    }

    /**
     * @param int|string $key
     * @param mixed $data
     * @param string $group
     * @param int $expire
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function replace(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
    {
        $k = $this->normalizeKeyGroup($key, $group);
        if ($this->adapter->hasItem($k)) {
            return $this->set($key, $data, $group, $expire);
        }
        return false;
    }

    /**
     * @param array $data
     * @param string $group
     * @param int $expire
     * @return Generator
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function setMultiple(array $data, string $group = '', int $expire = 0): Generator
    {
        foreach ($data as $key => $value) {
            yield $key => $this->set($key, $value, $group, $expire);
        }
    }

    /**
     * @param array $keys
     * @param string $group
     * @param bool $force
     * @return Generator
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getMultiple(array $keys, string $group = '', bool $force = false): Generator
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $group, $force);
        }
    }

    /**
     * @param int|string $key
     * @param string $group
     * @param bool $force
     * @param bool|null $found
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get(int|string $key, string $group = '', bool $force = false, bool &$found = null): mixed
    {
        $k = $this->normalizeKeyGroup($key, $group);
        if ($found = $this->adapter->hasItem($k)) {
            return $this->adapter->getItem($k)->get();
        }
        return false;
    }

    /**
     * @param array $keys
     * @param string $group
     * @return Generator
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteMultiple(array $keys, string $group = ''): Generator
    {
        foreach ($keys as $key) {
            yield $key => $this->delete($key, $group);
        }
    }

    /**
     * @param int|string $key
     * @param string $group
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(int|string $key, string $group = ''): bool
    {
        return $this->adapter->deleteItem($this->normalizeKeyGroup($key, $group));
    }

    /**
     * @param int|string $key
     * @param int $offset
     * @param string $group
     * @return int|false
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function incr(int|string $key, int $offset = 1, string $group = ''): int|false
    {
        $exists = false;
        $value = $this->get($key, $offset, $group, $exists);
        if ($exists && is_numeric($value)) {
            $value += $offset;
            if ($this->set($key, $value, $group)) {
                return (int)$value;
            }
        }
        return false;
    }

    /**
     * @param int|string $key
     * @param int $offset
     * @param string $group
     * @return int|false
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function decr(int|string $key, int $offset = 1, string $group = ''): int|false
    {
        $exists = false;
        $value = $this->get($key, $offset, $group, $exists);
        if ($exists && is_numeric($value)) {
            $value -= $offset;
            if ($this->set($key, $value, $group)) {
                return (int)$value;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function flush(): bool
    {
        return $this->adapter->clear();
    }

    /**
     * @param string $group
     * @return bool
     */
    public function flushGroup(string $group): bool
    {
        return $this->adapter->clear($group);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @param string|string[] $groups
     * @return void
     */
    public function addGlobalGroups(string|array $groups): void
    {
        if ($this->adapter instanceof CacheGroupAwareInterface) {
            $this->adapter->addGlobalGroups($groups);
        }
    }

    /**
     * @param string|string[] $groups
     * @return void
     */
    public function addNonPersistentGroups(string|array $groups): void
    {
        if ($this->adapter instanceof CacheGroupAwareInterface) {
            $this->adapter->addNonPersistentGroups($groups);
        }
    }

    /**
     * @param int $blog_id
     * @return void
     */
    public function switchToBlog(int $blog_id): void
    {
        if ($this->adapter instanceof CacheGroupAwareInterface) {
            $this->adapter->switchToBlog($blog_id);
        }
    }
}