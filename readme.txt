=== Force Login ===
Contributors: andrewwilkinson
Tags: login, security, headless, rest-api, graphql
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.1
License: MIT
License URI: https://opensource.org/licenses/MIT

Forces login for backend access in headless WordPress setups while allowing GraphQL/REST API endpoints and essential paths.

== Description ==

A lightweight plugin that **forces login for backend access** in a headless WordPress setup. Keeps your WordPress dashboard private while allowing your front end (e.g. Astro, Next.js) to pull content via GraphQL/REST.

= What it does =

* Requires authentication for `/wp-admin/` and other backend pages
* Always allows the login page to avoid redirect loops
* Leaves key endpoints open for headless use:
  * `/wp-json/` (REST API)
  * `/graphql` (WPGraphQL)
  * `/wp-admin/admin-ajax.php` (AJAX)
  * `/wp-cron.php` (cron)
  * `/robots.txt`
  * `/sitemap*.xml` (sitemaps and indexes)
  * `/wp-content/uploads/*` (media)
  * `/favicon.ico`
  * `/newrelic` (New Relic monitoring)
* Logged-in users visiting the backend root get redirected to the dashboard
* Works with Bedrock layouts (handles root path vs `/wp/`)

= Use case =

* WordPress is the content backend
* Public site is built with Astro/Next.js/etc
* Editors log in to WordPress. Visitors never see the backend
* Front end builds and live pages can still query GraphQL/REST without authentication

= Customization =

Developers can customize allowed endpoints using the `force_login_allowed_patterns` filter:

`
add_filter('force_login_allowed_patterns', function($patterns) {
    $patterns[] = '#^/healthz$#';           // custom health check
    $patterns[] = '#^/status$#';            // uptime checks
    $patterns[] = '#^/wp-json/acf/v3/.*#';  // specific REST namespace
    return $patterns;
});
`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/force-login` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The plugin will automatically start protecting your backend - no configuration needed!

== Frequently Asked Questions ==

= I'm locked out! How do I access my site? =

Visit `/wp-login.php` directly to sign in. The plugin always allows access to the login page.

= My front-end requests are failing. What should I do? =

Verify the endpoint is on the allow list. Check the plugin description for the default allowed patterns, or use the `force_login_allowed_patterns` filter to add custom endpoints.

= Does this work with Bedrock? =

Yes! The plugin correctly handles both standard WordPress installs and Bedrock layouts where the site URL and home URL may differ.

= Can I add custom endpoints? =

Yes, use the `force_login_allowed_patterns` filter to add your own regex patterns for additional endpoints that should remain public.

== Changelog ==

= 1.0.1 =
* Added: New Relic monitoring endpoint allowlist pattern (`/newrelic`) to support APM monitoring
* Added: WordPress.org plugin directory compatibility
* Added: Proper plugin structure with activation/deactivation hooks
* Added: Filter hook for customizing allowed patterns
* Improved: Code organization and documentation

= 1.0.0 =
* Initial release
* Restricts backend (`/wp-admin/`) to authenticated users
* Allows GraphQL and REST API endpoints for headless front-ends
* Basic whitelist of essential endpoints (cron, ajax, robots.txt, sitemaps, uploads)

== Upgrade Notice ==

= 1.0.1 =
This version adds WordPress.org compatibility and developer customization options. Safe to upgrade.