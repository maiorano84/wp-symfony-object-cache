<?php

namespace Maiorano\ObjectCache;

interface CacheGroupAwareInterface
{
    public const string DEFAULT_KEY_SEPARATOR = '--';
    public const string DEFAULT_GROUP_SEPARATOR = '__';

    /**
     * @param array $groups
     * @return void
     */
    public function addGlobalGroups(array $groups): void;

    /**
     * @param array $groups
     * @return void
     */
    public function addNonPersistentGroups(array $groups): void;

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