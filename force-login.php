<?php
/**
 * Plugin Name: Headless Login Guard
 * Plugin URI: https://github.com/MeonValleyWeb/headless-login-guard
 * Description: Forces login for backend access in headless WordPress setups while allowing GraphQL/REST API endpoints and essential paths.
 * Version: 1.1.0
 * Author: Andrew Wilkinson
 * Author URI: https://meonvalleyweb.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: headless-login-guard
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 8.1
 *
 * @package HeadlessLoginGuard
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'FORCE_LOGIN_VERSION', '1.1.0' );
define( 'FORCE_LOGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FORCE_LOGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FORCE_LOGIN_PLUGIN_FILE', __FILE__ );

/**
 * Main plugin class.
 */
class Force_Login {

	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'force_login_check' ) );
	}

	/**
	 * Main force login logic.
	 */
	public static function force_login_check() {
		// Let CLI/cron/ajax pass.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}
		if ( wp_doing_cron() ) {
			return;
		}
		if ( wp_doing_ajax() ) {
			return;
		}

		$redirect_url = self::determine_redirect_url();

		if ( false === $redirect_url ) {
			return;
		}

		nocache_headers();
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Determine whether the current request should redirect.
	 *
	 * @param string|null $request_uri Optional request URI for testing.
	 * @return string|false Redirect URL, or false when the request is allowed.
	 */
	public static function determine_redirect_url( $request_uri = null ) {
		if ( null === $request_uri ) {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		}

		$request_path = self::normalize_request_path( $request_uri );

		$is_login_request =
			( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) ||
			(bool) preg_match( '#(?:^|/)wp-login\.php$#', $request_path );

		// Always allow the login page (prevents loops).
		if ( $is_login_request ) {
			return false;
		}

		// Get allowed patterns with filter for customization.
		$allowed_patterns = apply_filters(
			'force_login_allowed_patterns',
			array(
				'#^/wp-json(?:/|$)#',                 // REST API.
				'#^/graphql(?:/|$)#',                 // WPGraphQL (if used).
				'#^/wp-admin/admin-ajax\.php$#',
				'#^/wp-cron\.php$#',
				'#^/robots\.txt$#',
				'#^/favicon\.ico$#',
				'#^/(?:[^/]+/)*(?:[^/]+-)*sitemaps?(?:[_-][^/]*)?\.xml$#',
				'#^/wp-content/uploads/.*#',          // Media.
				'#^/newrelic(?:/|$)#',                // New Relic monitoring.
			)
		);

		foreach ( $allowed_patterns as $pattern ) {
			if ( preg_match( $pattern, $request_path ) ) {
				return false;
			}
		}

		// Logged-in users hitting site root -> send to dashboard.
		if ( is_user_logged_in() ) {
			if ( '/' === $request_path ) {
				return admin_url();
			}
			return false; // Allow other URLs for logged-in users.
		}

		// Not logged in and not on an allowed path -> send to login with clean redirect_to.
		$destination = home_url( $request_path );
		$query       = wp_parse_url( $request_uri, PHP_URL_QUERY );

		if ( is_string( $query ) && '' !== $query ) {
			$destination .= '?' . $query;
		}

		return wp_login_url( $destination );
	}

	/**
	 * Normalize a request URI to a path relative to the site's home path.
	 *
	 * @param string $request_uri Request URI, optionally including a query string.
	 * @return string Normalized path beginning with a slash.
	 */
	private static function normalize_request_path( $request_uri ) {
		$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( ! is_string( $request_path ) || '' === $request_path ) {
			$request_path = '/';
		}

		$request_path = '/' . ltrim( $request_path, '/' );
		$home_path    = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

		if ( is_string( $home_path ) ) {
			$home_path = untrailingslashit( '/' . ltrim( $home_path, '/' ) );

			if ( '' !== $home_path && '/' !== $home_path ) {
				if ( $request_path === $home_path ) {
					$request_path = '/';
				} elseif ( 0 === strpos( $request_path, $home_path . '/' ) ) {
					$request_path = substr( $request_path, strlen( $home_path ) );
				}
			}
		}

		return $request_path;
	}
}

// Initialize the plugin.
Force_Login::init();

// Plugin activation hook.
register_activation_hook(
	__FILE__,
	function () {
		// Plugin activation logic if needed.
		do_action( 'force_login_activated' );
	}
);

// Plugin deactivation hook.
register_deactivation_hook(
	__FILE__,
	function () {
		// Plugin deactivation logic if needed.
		do_action( 'force_login_deactivated' );
	}
);
