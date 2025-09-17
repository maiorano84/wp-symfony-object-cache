<?php
/*
 * Plugin Name:       WordPress Symfony Cache
 * Description:       A Simple Object Cache using the Symfony Cache component.
 * Version:           0.1
 * Requires PHP:      8.2
 * Author:            Matt Maiorano
 * Author URI:        https://mattmaiorano.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       maiorano
 */

register_activation_hook( __FILE__, function() {
    $sys = loadFileSystem();
    if($sys !== false ) {
        $dropinPath = WP_CONTENT_DIR . '/object-cache.php';
        if($sys->exists($dropinPath)) {
            $sys->move($dropinPath, $dropinPath . '.bak');
        }
        $sys->copy( __DIR__ . '/dropin/object-cache.php', $dropinPath, true, FS_CHMOD_FILE );
        wp_cache_flush();
    }
} );

register_deactivation_hook( __FILE__, function() {
    $sys = loadFileSystem();
    if($sys !== false ) {
        $dropinPath = WP_CONTENT_DIR . '/object-cache.php';
        if($sys->exists($dropinPath)) {
            $sys->delete($dropinPath, false, 'f');
        }
        if($sys->exists($dropinPath . '.bak')) {
            $sys->move($dropinPath . '.bak', $dropinPath);
        }
        wp_cache_flush();
    }
} );

function loadFileSystem() {
    $url = wp_nonce_url(plugin_dir_url(__FILE__), 'wp-symfony-cache-move');
    $creds = request_filesystem_credentials( $url, '', false, WP_CONTENT_DIR, null );
    if($creds !== false && WP_Filesystem($creds) ) {
        global $wp_filesystem;
        return $wp_filesystem;
    }
    return false;
}