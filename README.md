# Force Login for Headless WordPress

A lightweight plugin that **forces login for backend access** in a headless WordPress setup.
Keeps your WordPress dashboard private while allowing your front end (e.g. Astro) to pull content via GraphQL/REST.

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
  - `/sitemap*.xml` (sitemaps and indexes)
  - `/wp-content/uploads/*` (media)
  - `/favicon.ico`
- Logged-in users visiting the backend root get redirected to the dashboard.
- Works with Bedrock layouts (handles root path vs `/wp/`).

---

## Why whitelist endpoints?

- **Health checks / uptime**: allow `/healthz` or `/status` so monitors (UptimeRobot, Pingdom) don’t see login redirects.
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

- WordPress 6.x+
- PHP 7.4+ (tested with PHP 8.x)
- Optional: [WPGraphQL](https://www.wpgraphql.com/)

---

## Installation

### Classic (wp-content) install

1. Copy this plugin folder to `wp-content/plugins/force-login`  
2. Activate **Force Login** in **Admin → Plugins**

### Bedrock (Composer) install

If the repo is not on Packagist, declare it as a VCS repository and require it:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/MeonValleyWeb/force-login"
    }
  ],
  "require": {
    "meonvalleyweb/force-login": "dev-main"
  }
}
```

Then run:

```bash
composer update meonvalleyweb/force-login
```

Activate the plugin in **wp-admin → Plugins**.

---

## How it works (overview)

- Hooks on `init`.
- Immediately returns for CLI, cron, and AJAX contexts.
- Allows specific public endpoints (see list above) for headless traffic.
- If the request is the login page, it’s allowed.
- If the user is authenticated:
  - Root requests (`/` or site root path) redirect to the dashboard.
  - Other requests proceed.
- If the user is not authenticated and the path is not allowed:
  - Redirect to the login page with `redirect_to` set to the original URL.

This keeps the backend private without breaking your headless front end.

---

## Customisation

Need extra open endpoints (e.g. a health check URL)?  
Edit the `$allowed_patterns` array in `force-login.php` to add your regex path(s).

Examples you might add:

```php
'#^/healthz$#',            // custom health check
'#^/status$#',             // uptime checks
'#^/wp-json/acf/v3/.*#',   // specific REST namespace
```

Keep your patterns anchored and specific to avoid exposing the backend.

---

## Troubleshooting

- Locked out? Visit `/wp-login.php` directly to sign in.
- Front-end requests failing? Verify the endpoint is on the allow list.
- On Bedrock, confirm the site URL and home URL are set correctly.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

---

## Contributing

- Fork the repo and open a Pull Request.
- Keep the plugin small and dependency-free.
- Add clear commit messages and a short description of the change.

---

## Credits

**Author:** Andrew Wilkinson  
**Company:** [MeonValleyWeb](https://meonvalleyweb.com)

---

## License

MIT License. See `LICENSE`.
