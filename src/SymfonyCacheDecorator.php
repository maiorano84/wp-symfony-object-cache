<?php

namespace Maiorano\ObjectCache;

use Generator;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\ItemInterface;

class SymfonyCacheDecorator implements WPCacheInterface, CacheGroupAwareInterface
{
    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $cache)
    {
        $this->adapter = $cache;
    }

    public function add(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
    {
        $k = $this->normalizeKeyGroup($key, $group);
        if (!$this->adapter->hasItem($k)) {
            return $this->set($key, $data, $group, $expire);
        }
        return false;
    }

    public function addMultiple(array $data, string $group = '', int $expire = 0): Generator
    {
        foreach ($data as $key => $value) {
            yield $key => $this->add($key, $value, $group, $expire);
        }
    }

    public function replace(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
    {
        $k = $this->normalizeKeyGroup($key, $group);
        if ($this->adapter->hasItem($k)) {
            return $this->set($key, $data, $group, $expire);
        }
        return false;
    }

    public function set(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
    {
        $k = $this->normalizeKeyGroup($key, $group);
        $item = $this->adapter->getItem($k)->set($data);

        if ($expire > 0) {
            $item->expiresAfter($expire);
        }
        return $this->adapter->save($item);
    }

    public function setMultiple(array $data, string $group = '', int $expire = 0): Generator
    {
        foreach ($data as $key => $value) {
            yield $key => $this->set($key, $value, $group, $expire);
        }
    }

    public function get(int|string $key, string $group = '', bool $force = false, bool &$found = null): mixed
    {
        $k = $this->normalizeKeyGroup($key, $group);
        if ($found = $this->adapter->hasItem($k)) {
            return $this->adapter->getItem($k)->get();
        }
        return false;
    }

    public function getMultiple(array $keys, string $group = '', bool $force = false): Generator
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $group, $force);
        }
    }

    public function delete(int|string $key, string $group = ''): bool
    {
        return $this->adapter->deleteItem($this->normalizeKeyGroup($key, $group));
    }

    public function deleteMultiple(array $keys, string $group = ''): Generator
    {
        foreach ($keys as $key) {
            yield $key => $this->delete($key, $group);
        }
    }

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

    public function flush(): bool
    {
        return $this->adapter->clear();
    }

    public function flushGroup(string $group): bool
    {
        return $this->adapter->clear($group);
    }

    public function close(): bool
    {
        return true;
    }

    public function addGlobalGroups(array $groups): void
    {
        if($this->adapter instanceof CacheGroupAwareInterface) {
            $this->adapter->addGlobalGroups($groups);
        }
    }

    public function addNonPersistentGroups(array $groups): void
    {
        if ($this->adapter instanceof CacheGroupAwareInterface) {
            $this->adapter->addNonPersistentGroups($groups);
        }
    }

    public function switchToBlog(int $blog_id): void
    {
        if($this->adapter instanceof CacheGroupAwareInterface) {
            $this->adapter->switchToBlog($blog_id);
        }
    }

    public function getGroupSeparator(): string
    {
        if($this->adapter instanceof CacheGroupAwareInterface) {
            return $this->adapter->getGroupSeparator();
        }
        return CacheGroupAwareInterface::DEFAULT_GROUP_SEPARATOR;
    }

    public function getKeySeparator(): string
    {
        if($this->adapter instanceof CacheGroupAwareInterface) {
            return $this->adapter->getKeySeparator();
        }
        return CacheGroupAwareInterface::DEFAULT_KEY_SEPARATOR;
    }

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
}