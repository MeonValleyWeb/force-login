<?php
/**
 * Force login for backend subdomain without redirect loops.
 * Allows: login page, REST/GraphQL, admin-ajax, cron, robots, sitemaps, uploads.
 */

add_action('init', function () {
    // Let CLI/cron/ajax pass
    if (defined('WP_CLI') && WP_CLI) return;
    if (wp_doing_cron()) return;
    if (wp_doing_ajax()) return;

    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $is_login_request =
        (isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') ||
        (bool) preg_match('#/wp-login\.php#', $uri);

    // Always allow the login page (prevents loops)
    if ($is_login_request) return;

    // Allowlist for headless use
    $allowed_patterns = [
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
    ];
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
    wp_safe_redirect( wp_login_url($dest) );
    exit;
});