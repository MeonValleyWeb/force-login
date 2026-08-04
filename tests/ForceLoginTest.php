<?php
/**
 * Tests for Headless Login Guard.
 *
 * @package HeadlessLoginGuard
 */

/**
 * Test the plugin's request and redirect decisions.
 */
class Force_Login_Test extends WP_UnitTestCase {

	/**
	 * Reset request-sensitive globals before each test.
	 */
	public function set_up() {
		parent::set_up();

		wp_set_current_user( 0 );
		unset( $GLOBALS['pagenow'] );
		update_option( 'home', 'http://example.org' );
		update_option( 'siteurl', 'http://example.org' );
	}

	/**
	 * Confirm the plugin loaded with the expected version.
	 */
	public function test_plugin_loads() {
		$this->assertTrue( class_exists( 'Force_Login' ) );
		$this->assertSame( '1.1.0', FORCE_LOGIN_VERSION );
	}

	/**
	 * Public endpoints should not redirect, including query-string variants.
	 *
	 * @dataProvider data_public_endpoints
	 *
	 * @param string $request_uri Request URI to test.
	 */
	public function test_public_endpoints_are_allowed( $request_uri ) {
		$this->assertFalse( Force_Login::determine_redirect_url( $request_uri ) );
	}

	/**
	 * Public endpoint cases.
	 *
	 * @return array<string, array<string>> Test cases.
	 */
	public function data_public_endpoints() {
		return array(
			'rest-api'           => array( '/wp-json/wp/v2/posts?per_page=10' ),
			'graphql-post'       => array( '/graphql' ),
			'graphql-get'        => array( '/graphql?query=%7Bposts%7D' ),
			'core-sitemap-index' => array( '/wp-sitemap.xml' ),
			'core-sitemap-child' => array( '/wp-sitemap-posts-post-1.xml' ),
			'yoast-index'        => array( '/sitemap_index.xml' ),
			'yoast-child'        => array( '/post-sitemap.xml' ),
			'nested-sitemap'     => array( '/en/post-sitemap.xml?cache=1' ),
			'favicon-query'      => array( '/favicon.ico?v=2' ),
		);
	}

	/**
	 * A protected request should redirect to login and retain its destination.
	 */
	public function test_protected_request_redirects_to_login() {
		$redirect = Force_Login::determine_redirect_url( '/private-preview?post=42' );
		$query    = wp_parse_url( $redirect, PHP_URL_QUERY );
		$args     = array();

		wp_parse_str( $query, $args );

		$this->assertStringStartsWith( 'http://example.org/wp-login.php', $redirect );
		$this->assertSame( 'http://example.org/private-preview?post=42', $args['redirect_to'] );
	}

	/**
	 * Subdirectory installs should match public endpoints relative to home.
	 */
	public function test_subdirectory_public_endpoint_is_allowed() {
		update_option( 'home', 'http://example.org/cms' );
		update_option( 'siteurl', 'http://example.org/cms' );

		$this->assertFalse( Force_Login::determine_redirect_url( '/cms/wp-json/wp/v2/posts?page=2' ) );
		$this->assertFalse( Force_Login::determine_redirect_url( '/cms/wp-sitemap.xml' ) );
	}

	/**
	 * Subdirectory redirect destinations must not duplicate the home path.
	 */
	public function test_subdirectory_redirect_preserves_original_destination() {
		update_option( 'home', 'http://example.org/cms' );
		update_option( 'siteurl', 'http://example.org/cms' );

		$redirect = Force_Login::determine_redirect_url( '/cms/private-preview?post=42' );
		$query    = wp_parse_url( $redirect, PHP_URL_QUERY );
		$args     = array();

		wp_parse_str( $query, $args );

		$this->assertSame( 'http://example.org/cms/private-preview?post=42', $args['redirect_to'] );
		$this->assertStringNotContainsString( '/cms/cms/', $args['redirect_to'] );
	}

	/**
	 * Logged-in users should go from the site root to the dashboard.
	 */
	public function test_logged_in_root_redirects_to_dashboard() {
		$user_id = self::factory()->user->create();
		wp_set_current_user( $user_id );

		$this->assertSame( admin_url(), Force_Login::determine_redirect_url( '/' ) );
	}

	/**
	 * Logged-in users may access non-root requests.
	 */
	public function test_logged_in_non_root_request_is_allowed() {
		$user_id = self::factory()->user->create();
		wp_set_current_user( $user_id );

		$this->assertFalse( Force_Login::determine_redirect_url( '/wp-admin/edit.php' ) );
	}

	/**
	 * The login endpoint must always remain available.
	 */
	public function test_login_request_is_allowed() {
		$this->assertFalse( Force_Login::determine_redirect_url( '/wp-login.php?action=lostpassword' ) );
	}

	/**
	 * Custom allowlist filters should receive and match normalized paths.
	 */
	public function test_custom_allowed_pattern_uses_normalized_path() {
		update_option( 'home', 'http://example.org/cms' );
		update_option( 'siteurl', 'http://example.org/cms' );

		$allow_health_check = static function ( $patterns ) {
			$patterns[] = '#^/healthz$#';
			return $patterns;
		};

		add_filter( 'force_login_allowed_patterns', $allow_health_check );

		$this->assertFalse( Force_Login::determine_redirect_url( '/cms/healthz?full=1' ) );

		remove_filter( 'force_login_allowed_patterns', $allow_health_check );
	}
}
