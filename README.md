<div align="center">

<img src=".wordpress-org/icon-256x256.png" alt="Headless Login Guard" width="128" height="128">

# Headless Login Guard

**Forces login for backend access in headless WordPress setups while allowing GraphQL/REST API endpoints.**

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg?style=flat-square&logo=wordpress)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb4.svg?style=flat-square&logo=php)](https://php.net/)
[![CI](https://github.com/MeonValleyWeb/headless-login-guard/workflows/CI/badge.svg)](https://github.com/MeonValleyWeb/headless-login-guard/actions)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)
[![Twitter Follow](https://img.shields.io/twitter/follow/meonvalleyweb?style=social)](https://twitter.com/meonvalleyweb)

</div>

---

A lightweight plugin that **forces login for backend access** in a headless WordPress setup.
Keeps your WordPress dashboard private while allowing your front end (e.g. Astro, Next.js) to pull content via GraphQL/REST.

---

## What it does

- Requires authentication for `/wp-admin/` and other backend pages.
- Always allows the login page to avoid redirect loops.
- Leaves key endpoints open for headless use:
  - `/wp-json/` (REST API)
  - `/graphql` (WPGraphQL)
  - `/wp-admin/admin-ajax.php` (AJAX)
  - `/wp-cron.php` (cron)
  - `/robots.txt`
  - WordPress core and common SEO sitemap XML paths
  - `/wp-content/uploads/*` (media)
  - `/favicon.ico`
  - `/newrelic` (New Relic monitoring)
- Logged-in users visiting the backend root get redirected to the dashboard.
- Works with Bedrock layouts (handles root path vs `/wp/`).

---

## Why whitelist endpoints?

- **Health checks / uptime**: allow `/healthz` or `/status` so monitors (UptimeRobot, Pingdom) don't see login redirects.
- **Webhooks / callbacks**: permit URLs used by third-party services (payments, email, CRM) so they can reach your site.
- **Custom REST routes**: expose only the routes your front end needs (e.g. `/wp-json/myplugin/v1/*`).
- **Performance monitoring**: lightweight probes for APMs and cloud providers.
- **SEO essentials**: keep `robots.txt`, sitemaps, and `favicon.ico` publicly accessible.

---

## Use case

- WordPress is the content backend.
- Public site is built with Astro/Next.js/etc.
- Editors log in to WordPress. Visitors never see the backend.
- Front end builds and live pages can still query GraphQL/REST without authentication.

---

## Requirements

- WordPress 6.0+
- PHP 8.1+ (recommended: 8.2 or 8.3)
- Optional: [WPGraphQL](https://www.wpgraphql.com/)

---

## Installation

### From WordPress.org (Recommended)

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Headless Login Guard"
3. Click **Install** then **Activate**

### Manual Install

1. Download the latest release from [GitHub Releases](https://github.com/MeonValleyWeb/headless-login-guard/releases)
2. Upload to `wp-content/plugins/headless-login-guard/`
3. Activate in **Admin → Plugins**

### Composer (Bedrock)

Recent Bedrock projects include [WP Packages](https://wp-packages.org/) by default:

```bash
composer require wp-plugin/headless-login-guard
```

For other Composer-managed WordPress projects, add the WP Packages repository first:

```bash
composer config repositories.wp-packages composer https://repo.wp-packages.org
composer require wp-plugin/headless-login-guard
```

---

## How it works (overview)

- Hooks on `init`.
- Immediately returns for CLI, cron, and AJAX contexts.
- Allows specific public endpoints (see list above) for headless traffic.
- If the request is the login page, it's allowed.
- If the user is authenticated:
  - Root requests (`/` or site root path) redirect to the dashboard.
  - Other requests proceed.
- If the user is not authenticated and the path is not allowed:
  - Redirect to the login page with `redirect_to` set to the original URL.

This keeps the backend private without breaking your headless front end.

---

## Customisation

Developers can customize allowed endpoints using the `force_login_allowed_patterns` filter:

```php
add_filter('force_login_allowed_patterns', function($patterns) {
    $patterns[] = '#^/healthz$#';           // custom health check
    $patterns[] = '#^/status$#';             // uptime checks
    $patterns[] = '#^/wp-json/acf/v3/.*#';  // specific REST namespace
    return $patterns;
});
```

Keep your patterns anchored and specific to avoid exposing the backend.

---

## Testing

This plugin includes a comprehensive test suite:

```bash
# Install dependencies
composer install

# Run coding standards check
composer run phpcs

# Run unit tests
composer run phpunit

# Run full test suite
composer run test
```

---

## Troubleshooting

- **Locked out?** Visit `/wp-login.php` directly to sign in.
- **Front-end requests failing?** Verify the endpoint is on the allow list.
- **On Bedrock?** Confirm the site URL and home URL are set correctly.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for all version history.

### [1.1.0] - 2026-08-04
- Fixed WordPress core, Yoast-style, and nested sitemap access
- Fixed allowlisted endpoints and redirect destinations on subdirectory installations
- Added redirect, query-string, sitemap, and subdirectory integration tests

### [1.0.1] - 2026-05-14
- Renamed plugin to "Headless Login Guard" for WordPress.org
- Added GitHub Actions CI/CD, PHPUnit tests, coding standards
- Added WordPress.org assets (icons, banners, screenshots)
- Security: sanitize $_SERVER inputs

### [1.0.0] - 2025-08-27
- Initial release

---

## Contributing

- Fork the repo and open a Pull Request.
- Keep the plugin small and dependency-free.
- Add clear commit messages and a short description of the change.

---

## Credits

**Author:** [Andrew Wilkinson](https://github.com/MeonValleyWeb)  
**Company:** [MeonValleyWeb](https://meonvalleyweb.com)  
**Twitter:** [@meonvalleyweb](https://twitter.com/meonvalleyweb)

---

## License

MIT License. See [LICENSE](LICENSE).
