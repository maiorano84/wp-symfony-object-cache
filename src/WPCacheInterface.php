<?php

namespace Maiorano\ObjectCache;

interface WPCacheInterface extends CacheGroupAwareInterface
{
    /**
     * @param int|string $key
     * @param mixed $data
     * @param string $group
     * @param int $expire
     * @return bool
     */
    public function add(int|string $key, mixed $data, string $group = '', int $expire = 0): bool;

    /**
     * @param array $data
     * @param string $group
     * @param int $expire
     * @return iterable
     */
    public function addMultiple(array $data, string $group = '', int $expire = 0): iterable;

    /**
     * @param int|string $key
     * @param mixed $data
     * @param string $group
     * @param int $expire
     * @return bool
     */
    public function replace(int|string $key, mixed $data, string $group = '', int $expire = 0): bool;

    /**
     * @param int|string $key
     * @param mixed $data
     * @param string $group
     * @param int $expire
     * @return bool
     */
    public function set(int|string $key, mixed $data, string $group = '', int $expire = 0): bool;

    /**
     * @param array $data
     * @param string $group
     * @param int $expire
     * @return iterable
     */
    public function setMultiple(array $data, string $group = '', int $expire = 0): iterable;

    /**
     * @param int|string $key
     * @param string $group
     * @param bool $force
     * @param bool|null $found
     * @return mixed
     */
    public function get(int|string $key, string $group = '', bool $force = false, bool &$found = null): mixed;

    /**
     * @param array $keys
     * @param string $group
     * @param bool $force
     * @return iterable
     */
    public function getMultiple(array $keys, string $group = '', bool $force = false): iterable;

    /**
     * @param int|string $key
     * @param string $group
     * @return bool
     */
    public function delete(int|string $key, string $group = ''): bool;

    /**
     * @param array $keys
     * @param string $group
     * @return iterable
     */
    public function deleteMultiple(array $keys, string $group = ''): iterable;

    /**
     * @param int|string $key
     * @param int $offset
     * @param string $group
     * @return int|false
     */
    public function incr(int|string $key, int $offset = 1, string $group = ''): int|false;

    /**
     * @param int|string $key
     * @param int $offset
     * @param string $group
     * @return int|false
     */
    public function decr(int|string $key, int $offset = 1, string $group = ''): int|false;

    /**
     * @return bool
     */
    public function flush(): bool;

    /**
     * @param string $group
     * @return bool
     */
    public function flushGroup(string $group): bool;

    /**
     * @return bool
     */
    public function close(): bool;
}