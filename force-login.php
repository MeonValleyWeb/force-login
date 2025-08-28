<?php
/**
 * Plugin Name: Force Login
 * Plugin URI: https://github.com/MeonValleyWeb/force-login
 * Description: Forces login for backend access in headless WordPress setups while allowing GraphQL/REST API endpoints and essential paths.
 * Version: 1.0.1
 * Author: Andrew Wilkinson
 * Author URI: https://meonvalleyweb.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: force-login
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Network: false
 *
 * @package ForceLogin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FORCE_LOGIN_VERSION', '1.0.1');
define('FORCE_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FORCE_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FORCE_LOGIN_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class Force_Login {
    
    /**
     * Initialize the plugin
     */
    public static function init() {
        add_action('init', [__CLASS__, 'force_login_check']);
        add_action('plugins_loaded', [__CLASS__, 'load_textdomain']);
    }
    
    /**
     * Load plugin text domain for translations
     */
    public static function load_textdomain() {
        load_plugin_textdomain('force-login', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Main force login logic
     */
    public static function force_login_check() {
        // Let CLI/cron/ajax pass
        if (defined('WP_CLI') && WP_CLI) return;
        if (wp_doing_cron()) return;
        if (wp_doing_ajax()) return;

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $is_login_request =
            (isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') ||
            (bool) preg_match('#/wp-login\.php#', $uri);

        // Always allow the login page (prevents loops)
        if ($is_login_request) return;

        // Get allowed patterns with filter for customization
        $allowed_patterns = apply_filters('force_login_allowed_patterns', [
            '#^/wp-json(?:/|$)#',                 // REST API
            '#^/graphql(?:/|$)#',                 // WPGraphQL (if used)
            '#^/wp-admin/admin-ajax\.php$#',
            '#^/wp-cron\.php$#',
            '#^/robots\.txt$#',
            '#^/favicon\.ico$#',
            '#^/sitemap\.xml$#',
            '#^/sitemaps?-.*\.xml$#',
            '#^/wp-content/uploads/.*#',          // media
            '#^/newrelic(?:/|$)#',                // New Relic monitoring
        ]);
        
        foreach ($allowed_patterns as $pattern) {
            if (preg_match($pattern, $uri)) return;
        }

        // Logged-in users hitting site root -> send to dashboard
        if (is_user_logged_in()) {
            // Treat "/" as root even in Bedrock (/wp lives separately)
            $path = wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/';
            if ($uri === '/' || $uri === $path) {
                wp_safe_redirect(admin_url());
                exit;
            }
            return; // allow other URLs for logged-in users
        }

        // Not logged in and not on an allowed path -> send to login with clean redirect_to
        $dest = home_url(add_query_arg([], $uri));
        wp_safe_redirect(wp_login_url($dest));
        exit;
    }
}

// Initialize the plugin
Force_Login::init();

// Plugin activation hook
register_activation_hook(__FILE__, function() {
    // Plugin activation logic if needed
    do_action('force_login_activated');
});

// Plugin deactivation hook  
register_deactivation_hook(__FILE__, function() {
    // Plugin deactivation logic if needed
    do_action('force_login_deactivated');
});