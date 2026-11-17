# MOON — sandbox notes for future sessions

WordPress theme (`wp-content/themes/moon-theme`) + demo importer
(`wp-content/plugins/moon-demo-importer`), run by `docker-compose.base44.yml`.

## Things that are not obvious

- **WordPress core lives in the `wp_data` volume**, not in the repo. Only the theme and
  the importer are bind-mounted, so PHP/CSS/JS edits are live immediately.
- **`setup` is not a pure one-shot.** It installs WP core deps, WooCommerce, activates the
  theme/plugin, seeds the demo content and then writes `/var/www/html/.moon-setup-ok` and
  idles (`tail -f /dev/null`). It idles on purpose: the sandbox health checker treats an
  exited container as failed, so keep the keep-alive and the `healthcheck` intact.
  The script (`docker/setup.sh`) is idempotent — running it again only finishes what is
  missing.
- `WP_CLI_CACHE_DIR=/tmp/wp-cli-cache` is set for the setup service; without it wp-cli
  warns it cannot write `/.wp-cli/cache` (the container runs as uid 33).
- Site URL is stored as `http://localhost:3000`. With `BASE44_PREVIEW_MODE=1` the mu-plugin
  in `docker/mu-plugins` rewrites hosts per request, so do **not** hardcode the sandbox host
  in the database.
- Permalinks are `/%postname%/`. Useful routes: `/shop/`, `/product/<slug>/`, `/cart/`,
  `/checkout/`, `/my-account/`, `/compare/`, `/journal/`.
- `my-account`, checkout and the OTP login are session-gated: `curl` and an anonymous
  preview browser only ever see the signed-out form. Verify the signed-in state by hand
  (or with the sandbox admin account) and report it as such.
- `wp-config.php` turns `WP_DEBUG_DISPLAY` off but `WP_DEBUG_LOG` on; logs land in
  `wp-content/debug.log` inside the container.

## Commands

```bash
docker compose -f docker-compose.base44.yml up -d --build     # first boot
docker compose -f docker-compose.base44.yml ps                # health of db / wordpress / setup
docker compose -f docker-compose.base44.yml logs -f wordpress
docker compose -f docker-compose.base44.yml exec -T setup wp plugin list
docker compose -f docker-compose.base44.yml exec -T wordpress php -l wp-content/themes/moon-theme/functions.php
```

Seed/demo data again (idempotent):

```bash
docker compose -f docker-compose.base44.yml exec -T setup wp moon-demo import --products --catalog
```

## Packaging

`bash tools/build-packages.sh` writes `dist/moon-theme-final.zip` and
`dist/moon-demo-importer-final.zip` from the source in this repo (no `zip` binary on the
host, so it runs `tools/package.php` inside the cached `wordpress:cli-php8.3` image using
PHP `ZipArchive`).

## Verifying a change

1. `docker compose -f docker-compose.base44.yml ps` — `wordpress` and `setup` healthy.
2. `curl -s -o /dev/null -w '%{http_code}' http://localhost:3000/` → `200`.
3. Load the affected route in the preview: shop, product, cart, compare, journal.
4. Check `scrollWidth - clientWidth` at desktop/tablet/mobile — the theme is expected to
   stay at `0` on every route.
