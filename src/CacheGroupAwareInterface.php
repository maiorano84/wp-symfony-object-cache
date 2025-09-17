<?php

namespace Maiorano\ObjectCache;

interface CacheGroupAwareInterface
{
    public const DEFAULT_KEY_SEPARATOR = '--';
    public const DEFAULT_GROUP_SEPARATOR = '__';

    /**
     * @param string|string[] $groups
     * @return void
     */
    public function addGlobalGroups(string|array $groups): void;

    /**
     * @param string|string[] $groups
     * @return void
     */
    public function addNonPersistentGroups(string|array $groups): void;

    /**
     * @param int $blog_id
     * @return void
     */
    public function switchToBlog(int $blog_id): void;

    /**
     * @return string
     */
    public function getGroupSeparator(): string;

    /**
     * @return string
     */
    public function getKeySeparator(): string;
}