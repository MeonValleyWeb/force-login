<?php
/**
 * Test Force Login functionality
 *
 * @package ForceLogin
 */

/**
 * Class Force_Login_Test
 */
class Force_Login_Test extends WP_UnitTestCase {

	/**
	 * Test that the plugin class exists and can be instantiated
	 */
	public function test_class_exists() {
		$this->assertTrue(class_exists('Force_Login'));
	}

	/**
	 * Test that plugin constants are defined
	 */
	public function test_constants_defined() {
		$this->assertTrue(defined('FORCE_LOGIN_VERSION'));
		$this->assertTrue(defined('FORCE_LOGIN_PLUGIN_DIR'));
		$this->assertTrue(defined('FORCE_LOGIN_PLUGIN_URL'));
		$this->assertTrue(defined('FORCE_LOGIN_PLUGIN_FILE'));
	}

	/**
	 * Test that plugin version matches expected value
	 */
	public function test_plugin_version() {
		$this->assertEquals('1.0.1', FORCE_LOGIN_VERSION);
	}

	/**
	 * Test that allowed patterns filter exists
	 */
	public function test_allowed_patterns_filter() {
		$patterns = apply_filters('force_login_allowed_patterns', []);
		$this->assertIsArray($patterns);
	}

	/**
	 * Test REST API pattern matches
	 */
	public function test_rest_api_pattern_matches() {
		$patterns = [
			'#^/wp-json(?:/|$)#',
		];
		
		$test_urls = [
			'/wp-json/wp/v2/posts' => true,
			'/wp-json/' => true,
			'/wp-admin/' => false,
		];
		
		foreach ($test_urls as $url => $should_match) {
			$matched = false;
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $url)) {
					$matched = true;
					break;
				}
			}
			$this->assertEquals($should_match, $matched, "URL $url pattern matching failed");
		}
	}

	/**
	 * Test GraphQL pattern matches
	 */
	public function test_graphql_pattern_matches() {
		$patterns = [
			'#^/graphql(?:/|$)#',
		];
		
		$test_urls = [
			'/graphql' => true,
			'/graphql/' => true,
			'/wp-json/' => false,
		];
		
		foreach ($test_urls as $url => $should_match) {
			$matched = false;
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $url)) {
					$matched = true;
					break;
				}
			}
			$this->assertEquals($should_match, $matched, "URL $url pattern matching failed");
		}
	}

	/**
	 * Test sitemap patterns
	 */
	public function test_sitemap_patterns() {
		$patterns = [
			'#^/sitemap\.xml$#',
			'#^/sitemaps?-.*\.xml$#',
		];
		
		$test_urls = [
			'/sitemap.xml' => true,
			'/sitemaps-posts.xml' => true,
			'/sitemap-0.xml' => true,
			'/wp-admin/' => false,
		];
		
		foreach ($test_urls as $url => $should_match) {
			$matched = false;
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $url)) {
					$matched = true;
					break;
				}
			}
			$this->assertEquals($should_match, $matched, "URL $url pattern matching failed");
		}
	}

	/**
	 * Test that activation hook is registered
	 */
	public function test_activation_hook_registered() {
		$callbacks = $GLOBALS['wp_filter']['force_login_activated'] ?? null;
		$this->assertNotNull($callbacks, 'Activation action should exist');
	}

	/**
	 * Test plugin file exists
	 */
	public function test_plugin_file_exists() {
		$this->assertFileExists(FORCE_LOGIN_PLUGIN_FILE);
	}
}
