# MEMORY - force-login

**Project:** Force Login for Headless WordPress  
**Type:** WordPress Plugin  
**Version:** 1.0.1  
**License:** MIT

## Purpose
Secure headless WordPress backend. Forces login for `/wp-admin/` while keeping API endpoints open for front-end (Astro/Next.js/etc).

## Use Case
- WordPress = content backend
- Public site = Astro/Next.js
- Editors log in, visitors never see backend
- Front-end queries GraphQL/REST without auth

## Key Files
- `force-login.php` — Main plugin
- `composer.json` — Bedrock/Composer install
- `readme.txt` — WordPress.org format
- `CHANGELOG.md` — Version history

## Allowed Patterns
Whitelist regex patterns for public access:
- REST API (`/wp-json/`)
- GraphQL (`/graphql`)
- AJAX, cron, uploads
- Sitemaps, robots.txt, favicon
- New Relic monitoring

## Customization
Add patterns to `$allowed_patterns` in `force-login.php`:
```php
'#^/healthz$#',           // health checks
'#^/wp-json/myplugin/.*#', // custom REST
```

## Author
Andrew Wilkinson / MeonValleyWeb
