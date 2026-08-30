# AGENTS.md — ldm-ingest

## Quick commands

```bash
# Install deps
cd ldm-ingest && composer install

# Run tests
php vendor/bin/phpunit

# Run single test
php vendor/bin/phpunit tests/UtilsTest.php

# Run specific test method
php vendor/bin/phpunit tests/UtilsTest.php --filter testExpandRanges

# Run the ingestor (reads from STDIN)
echo "product text" | php src/ingestor.php

```

## Setup

1. `composer install`
2. Copy/create `conf/chswx.conf.php` with RethinkDB connection config (gitignored)
3. Create `.env` with `LOG_OUTPUT`, `LOG_LEVEL` (gitignored)
4. Run `php src/setup.php` to initialize database and import geospatial data

## Architecture

- **Language**: PHP (CLI), no web framework
- **Entry point**: `src/ingestor.php` — reads NWS products from STDIN, pipes to parser factory, stores in RethinkDB
- **Parser factory**: `src/Ingestor/NWSProductFactory.php` — dispatches to product-type parsers
- **Product parsers**: `src/Parser/ProductTypes/` — `VTEC.php`, `HLS.php`, `WatchProbs.php`, `GenericProduct.php`
- **VTEC library**: `src/Parser/Library/VTEC/` — `VTECString.php`, `VTECUtils.php`
- **Other libraries**: `SBW.php`, `IBW.php`, `SMVString.php` (storm-based warnings, impact-based warnings, severe module)
- **Supporting parsers**: `AbbreviatedHeading.php` (WMO), `NWSProduct.php`, `NWSProductSegment.php`
- **Geo types**: `src/Parser/Library/Geo/Point.php`, `Polygon.php`
- **Storage**: `src/Storage/ProductStorage.php` — RethinkDB write via `danielmewes/php-rql`
- **Utils**: `src/Utils.php` — static helpers (sanitize, zone parsing, PDS detection, logging)
- **Setup**: `src/setup.php` — one-time DB init, table creation, geospatial/zone data import
- **Docker**: `Dockerfile` — based on `unidata/ldm-docker:6.13.13`, PHP 7.4 CLI, copies src/ to /home/chswx/services/ldm-ingest

## GitHub

Hosted at [github.com/chswx/ldm-ingest](https://github.com/chswx/ldm-ingest) (git submodule).

## Gotchas

- `conf/` and `lib/` dirs are gitignored (local config + legacy library dep)
- Uses `danielmewes/php-rql` (dev-master from private chswx/php-rql repo) — not on Packagist
- `setup.php` hardcodes DATABASE_NAME="chswx" and DATABASE_SERVER="chswx-rethink-dev.orb.local"
- Tests use PHPUnit 9.5 with `phpunit.xml` pointing to `tests/` directory
- Ingestor expects `conf/chswx.conf.php` to exist — will fail without it
- No linting or static analysis configured
- No CI/CD configured
- **Channel generation has been decommissioned** — parsers no longer emit a `channels` field. Channel/dissemination targeting now lives in `pyeventsmanager` (`EventFactory.make_*_channels`). Do not re-add `generateChannels`/`appendChannels` here.
