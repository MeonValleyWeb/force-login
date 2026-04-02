# CHANGELOG_PROGRESS - force-login

**Cloned:** 2026-04-02  
**Status:** v1.0.1 — Production ready WordPress plugin

## Project Overview
WordPress plugin that forces login for backend access in headless WordPress setups. Keeps `/wp-admin/` private while allowing front-end API access.

## Purpose
- Requires auth for `/wp-admin/`
- Allows public: REST API, GraphQL, AJAX, cron, uploads
- Prevents redirect loops on login page
- Bedrock-compatible

## Tech
- **Type:** WordPress Plugin
- **PHP:** 7.4+ (tested with 8.x)
- **Requirements:** WordPress 6.x+
- **Optional:** WPGraphQL

## Allowed Endpoints (Public)
- `/wp-json/` (REST API)
- `/graphql` (WPGraphQL)
- `/wp-admin/admin-ajax.php`
- `/wp-cron.php`
- `/robots.txt`, `/sitemap*.xml`
- `/wp-content/uploads/*`
- `/favicon.ico`
- `/newrelic` (APM monitoring)

## Installation
**Classic:** Copy to `wp-content/plugins/force-login`
**Bedrock/Composer:** VCS repository → `composer require meonvalleyweb/force-login`

## Recent Activity
- v1.0.1: Added New Relic monitoring endpoint
- v1.0.0: Initial release

## Pending Tasks
- [ ] Monitor for additional endpoint requests
- [ ] Check WordPress.org plugin directory status
- [ ] Review for multisite compatibility if needed

## Notes
- Customization: Edit `$allowed_patterns` array in `force-login.php`
- Locked out? Visit `/wp-login.php` directly
- Packaged for wordpress.org (see readme.txt)

---
*This file is managed by Jarvis. Last updated: 2026-04-02*
