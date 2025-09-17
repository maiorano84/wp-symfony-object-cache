<?php
/*
 * Plugin Name:       WordPress Symfony Object Cache Drop-In
 * Description:       An Object Cache using the Symfony Cache component.
 * Version:           0.1
 * Requires PHP:      8.2
 * Author:            Matt Maiorano
 * Author URI:        https://mattmaiorano.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       maiorano
 */

use Maiorano\ObjectCache\CacheGroupAwareInterface;
use Maiorano\ObjectCache\WPCacheInterface;
use Maiorano\ObjectCache\WPSymfonyCacheFactory;

defined('ABSPATH') || exit;

/**
 * Object Cache API
 *
 * @link https://developer.wordpress.org/reference/classes/wp_object_cache/
 *
 * @package Maiorano\ObjectCache
 */
require_once WP_CONTENT_DIR . '/plugins/wp-symfony-cache/vendor/autoload.php';
WPSymfonyCacheFactory::autoload();

/**
 * Sets up Object Cache Global and assigns it.
 *
 * @since 2.0.0
 *
 * @global WPCacheInterface $wp_object_cache
 */
function wp_cache_init()
{
    $GLOBALS['wp_object_cache'] = WPSymfonyCacheFactory::create();
}

/**
 * Adds data to the cache, if the cache key doesn't already exist.
 *
 * @param int|string $key The cache key to use for retrieval later.
 * @param mixed $data The data to add to the cache.
 * @param string $group Optional. The group to add the cache to. Enables the same key
 *                           to be used across groups. Default empty.
 * @param int $expire Optional. When the cache data should expire, in seconds.
 *                           Default 0 (no expiration).
 * @return bool True on success, false if cache key and group already exist.
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 2.0.0
 *
 * @see WPCacheInterface::add()
 */
function wp_cache_add($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->add($key, $data, $group, (int)$expire);
}

/**
 * Adds multiple values to the cache in one call.
 *
 * @param array $data Array of keys and values to be set.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @param int $expire Optional. When to expire the cache contents, in seconds.
 *                       Default 0 (no expiration).
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false if cache key and group already exist.
 * @see WPCacheInterface::add_multiple()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 6.0.0
 *
 */
function wp_cache_add_multiple(array $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return iterator_to_array($wp_object_cache->addMultiple($data, $group, $expire));
}

/**
 * Replaces the contents of the cache with new data.
 *
 * @param int|string $key The key for the cache data that should be replaced.
 * @param mixed $data The new data to store in the cache.
 * @param string $group Optional. The group for the cache data that should be replaced.
 *                           Default empty.
 * @param int $expire Optional. When to expire the cache contents, in seconds.
 *                           Default 0 (no expiration).
 * @return bool True if contents were replaced, false if original value does not exist.
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 2.0.0
 *
 * @see WPCacheInterface::replace()
 */
function wp_cache_replace($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->replace($key, $data, $group, (int)$expire);
}

/**
 * Saves the data to the cache.
 *
 * Differs from wp_cache_add() and wp_cache_replace() in that it will always write data.
 *
 * @param int|string $key The cache key to use for retrieval later.
 * @param mixed $data The contents to store in the cache.
 * @param string $group Optional. Where to group the cache contents. Enables the same key
 *                           to be used across groups. Default empty.
 * @param int $expire Optional. When to expire the cache contents, in seconds.
 *                           Default 0 (no expiration).
 * @return bool True on success, false on failure.
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 2.0.0
 *
 * @see WPCacheInterface::set()
 */
function wp_cache_set($key, $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->set($key, $data, $group, (int)$expire);
}

/**
 * Sets multiple values to the cache in one call.
 *
 * @param array $data Array of keys and values to be set.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @param int $expire Optional. When to expire the cache contents, in seconds.
 *                       Default 0 (no expiration).
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false on failure.
 * @see WPCacheInterface::set_multiple()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 6.0.0
 *
 */
function wp_cache_set_multiple(array $data, $group = '', $expire = 0)
{
    global $wp_object_cache;

    return iterator_to_array($wp_object_cache->setMultiple($data, $group, $expire));
}

/**
 * Retrieves the cache contents from the cache by key and group.
 *
 * @param int|string $key The key under which the cache contents are stored.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @param bool $force Optional. Whether to force an update of the local cache
 *                          from the persistent cache. Default false.
 * @param bool $found Optional. Whether the key was found in the cache (passed by reference).
 *                          Disambiguates a return of false, a storable value. Default null.
 * @return mixed|false The cache contents on success, false on failure to retrieve contents.
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 2.0.0
 *
 * @see WPCacheInterface::get()
 */
function wp_cache_get($key, $group = '', $force = false, &$found = null)
{
    global $wp_object_cache;

    return $wp_object_cache->get($key, $group, $force, $found);
}

/**
 * Retrieves multiple values from the cache in one call.
 *
 * @param array $keys Array of keys under which the cache contents are stored.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @param bool $force Optional. Whether to force an update of the local cache
 *                      from the persistent cache. Default false.
 * @return array Array of return values, grouped by key. Each value is either
 *               the cache contents on success, or false on failure.
 * @see WPCacheInterface::get_multiple()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 5.5.0
 *
 */
function wp_cache_get_multiple($keys, $group = '', $force = false)
{
    global $wp_object_cache;

    return iterator_to_array($wp_object_cache->getMultiple($keys, $group, $force));
}

/**
 * Removes the cache contents matching key and group.
 *
 * @param int|string $key What the contents in the cache are called.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @return bool True on successful removal, false on failure.
 * @since 2.0.0
 *
 * @see WPCacheInterface::delete()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 */
function wp_cache_delete($key, $group = '')
{
    global $wp_object_cache;

    return $wp_object_cache->delete($key, $group);
}

/**
 * Deletes multiple values from the cache in one call.
 *
 * @param array $keys Array of keys under which the cache to deleted.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false if the contents were not deleted.
 * @since 6.0.0
 *
 * @see WPCacheInterface::delete_multiple()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 */
function wp_cache_delete_multiple(array $keys, $group = '')
{
    global $wp_object_cache;

    return iterator_to_array($wp_object_cache->deleteMultiple($keys, $group));
}

/**
 * Increments numeric cache item's value.
 *
 * @param int|string $key The key for the cache contents that should be incremented.
 * @param int $offset Optional. The amount by which to increment the item's value.
 *                           Default 1.
 * @param string $group Optional. The group the key is in. Default empty.
 * @return int|false The item's new value on success, false on failure.
 * @see WPCacheInterface::incr()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 3.3.0
 *
 */
function wp_cache_incr($key, $offset = 1, $group = '')
{
    global $wp_object_cache;

    return $wp_object_cache->incr($key, $offset, $group);
}

/**
 * Decrements numeric cache item's value.
 *
 * @param int|string $key The cache key to decrement.
 * @param int $offset Optional. The amount by which to decrement the item's value.
 *                           Default 1.
 * @param string $group Optional. The group the key is in. Default empty.
 * @return int|false The item's new value on success, false on failure.
 * @see WPCacheInterface::decr()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 3.3.0
 *
 */
function wp_cache_decr($key, $offset = 1, $group = '')
{
    global $wp_object_cache;

    return $wp_object_cache->decr($key, $offset, $group);
}

/**
 * Removes all cache items.
 *
 * @return bool True on success, false on failure.
 * @see WPCacheInterface::flush()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 2.0.0
 *
 */
function wp_cache_flush()
{
    global $wp_object_cache;

    return $wp_object_cache->flush();
}

/**
 * Removes all cache items from the in-memory runtime cache.
 *
 * @return bool True on success, false on failure.
 * @see WPCacheInterface::flush()
 *
 * @since 6.0.0
 *
 */
function wp_cache_flush_runtime()
{
    return wp_cache_flush();
}

/**
 * Removes all cache items in a group, if the object cache implementation supports it.
 *
 * Before calling this function, always check for group flushing support using the
 * `wp_cache_supports( 'flush_group' )` function.
 *
 * @param string $group Name of group to remove from cache.
 * @return bool True if group was flushed, false otherwise.
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 6.1.0
 *
 * @see WPCacheInterface::flush_group()
 */
function wp_cache_flush_group($group)
{
    global $wp_object_cache;

    return $wp_object_cache->flushGroup($group);
}

/**
 * Determines whether the object cache implementation supports a particular feature.
 *
 * @param string $feature Name of the feature to check for. Possible values include:
 *                        'add_multiple', 'set_multiple', 'get_multiple', 'delete_multiple',
 *                        'flush_runtime', 'flush_group'.
 * @return bool True if the feature is supported, false otherwise.
 * @since 6.1.0
 *
 */
function wp_cache_supports($feature)
{
    switch ($feature) {
        case 'add_multiple':
        case 'set_multiple':
        case 'get_multiple':
        case 'delete_multiple':
        case 'flush_runtime':
        case 'flush_group':
            return true;

        default:
            return false;
    }
}

/**
 * Closes the cache.
 *
 * This function has ceased to do anything since WordPress 2.5. The
 * functionality was removed along with the rest of the persistent cache.
 *
 * This does not mean that plugins can't implement this function when they need
 * to make sure that the cache is cleaned up after WordPress no longer needs it.
 *
 * @return true Always returns true.
 * @since 2.0.0
 *
 */
function wp_cache_close()
{
    global $wp_object_cache;
    return $wp_object_cache->close();
}

/**
 * Adds a group or set of groups to the list of global groups.
 *
 * @param string|string[] $groups A group or an array of groups to add.
 * @see CacheGroupAwareInterface::add_global_groups()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 2.6.0
 *
 */
function wp_cache_add_global_groups($groups)
{
    global $wp_object_cache;

    $wp_object_cache->addGlobalGroups($groups);
}

/**
 * Adds a group or set of groups to the list of non-persistent groups.
 *
 * @param string|string[] $groups A group or an array of groups to add.
 * @see CacheGroupAwareInterface::add_non_persistent_groups()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 2.6.0
 *
 */
function wp_cache_add_non_persistent_groups($groups)
{
    global $wp_object_cache;

    $wp_object_cache->addNonPersistentGroups($groups);
}

/**
 * Switches the internal blog ID.
 *
 * This changes the blog id used to create keys in blog specific groups.
 *
 * @param int $blog_id Site ID.
 * @see CacheGroupAwareInterface::switch_to_blog()
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 *
 * @since 3.5.0
 *
 */
function wp_cache_switch_to_blog($blog_id)
{
    global $wp_object_cache;

    $wp_object_cache->switchToBlog($blog_id);
}

/**
 * Resets internal cache keys and structures.
 *
 * If the cache back end uses global blog or site IDs as part of its cache keys,
 * this function instructs the back end to reset those keys and perform any cleanup
 * since blog or site IDs have changed since cache init.
 *
 * This function is deprecated. Use wp_cache_switch_to_blog() instead of this
 * function when preparing the cache for a blog switch. For clearing the cache
 * during unit tests, consider using wp_cache_init(). wp_cache_init() is not
 * recommended outside of unit tests as the performance penalty for using it is high.
 *
 * @since 3.0.0
 * @deprecated 3.5.0 Use wp_cache_switch_to_blog()
 * @see WPCacheInterface::flush()
 *
 * @global WPCacheInterface $wp_object_cache Object cache global instance.
 */
function wp_cache_reset()
{
    _deprecated_function(__FUNCTION__, '3.5.0', 'wp_cache_switch_to_blog()');

    wp_cache_flush();
}