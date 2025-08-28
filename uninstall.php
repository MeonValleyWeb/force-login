<?php
/**
 * Uninstall Force Login Plugin
 *
 * @package ForceLogin
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up any plugin options if we had any (currently we don't store any)
// delete_option('force_login_option_name');

// Clean up any transients if we had any (currently we don't use any)
// delete_transient('force_login_transient_name');

// No database tables to drop or user meta to clean up for this plugin
// The plugin is stateless and doesn't store any data

// Fire action for other plugins to clean up if they were hooking into our plugin
do_action('force_login_uninstall');