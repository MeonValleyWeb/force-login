# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-08-27
### Added
- Initial release of Force Login plugin for headless WordPress setups
- Restricts backend (`/wp-admin/`) to authenticated users
- Allows GraphQL and REST API endpoints for headless front-ends
- Basic whitelist of essential endpoints (cron, ajax, robots.txt, sitemaps, uploads)
