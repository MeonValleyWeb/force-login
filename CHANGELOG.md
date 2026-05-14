# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-05-14

### Changed
- **Plugin renamed** from "Force Login" to "Headless Login Guard" to avoid WordPress.org naming conflict
- Updated text domain from `force-login` to `headless-login-guard`
- Updated `Tested up to:` to WordPress 6.9

### Added
- GitHub Actions CI/CD workflow for automated testing (PHP 7.4-8.3, WP 6.2-6.9)
- PHPUnit test suite with 11 unit tests
- WordPress Coding Standards (WPCS) integration via Composer
- WordPress.org assets: icons (128x128, 256x256), banners (772x250, 1544x500), screenshots
- Translation template (.pot file)
- Automated test environment setup scripts
- Security hardening: sanitize $_SERVER['REQUEST_URI'] input

### Removed
- `load_plugin_textdomain()` call (WordPress 4.6+ auto-loads translations)
- `.claude` AI instruction directories
- `Network: false` header (not needed)

## [1.0.0] - 2025-08-27

### Added
- Initial release of Force Login plugin for headless WordPress setups
- Restricts backend (`/wp-admin/`) to authenticated users
- Allows GraphQL and REST API endpoints for headless front-ends
- Basic whitelist of essential endpoints (cron, ajax, robots.txt, sitemaps, uploads)
- New Relic monitoring endpoint allowlist pattern (`/newrelic`) to support APM monitoring
