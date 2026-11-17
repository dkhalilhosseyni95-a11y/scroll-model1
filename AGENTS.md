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

## Colour mode

`theme_mode` (ظاهر و طراحی → رنگ‌ها) is `dark`, `light` or `system`. `header.php`
prints both the class and `data-moon-theme-mode` on `<html>`, then a tiny inline
script applies the visitor's saved choice before the stylesheet paints, so the
switch never flashes. `assets/js/chrome.js` owns the toggle and, in `system`
mode, follows `prefers-color-scheme` live. `--on-overlay` must stay light in
both modes — overlay panels always paint on a dark scrim.

## External login plugin (SMS / OTP)

`account → login → auth_shortcode` replaces the theme's sign-in/registration
form with any shortcode (`[digits_phone]`, a SMS-panel plugin, …). Digits is
also auto-detected through `Moon_OTP::mode()`. A custom HTML homepage section
runs shortcodes too (`do_shortcode` on its content field).

## Two traps that already cost time

- **Asset versions**: `MOON_THEME_VERSION` is the `?ver=` on every CSS/JS file.
  Bump it whenever styles change, or the browser serves the old stylesheet and
  a CSS fix looks like it did nothing.
- **Empty settings beat defaults**: `Moon_Settings::defaults()` used to prefer
  the schema's empty `default` over the copy in `moon_option_defaults()`, so the
  first migration wrote empty strings for the hero, campaign and footer texts
  and the homepage rendered without a title. `VERSION = 4` repairs an install
  that already stored those empty strings (`maybe_migrate()` runs on
  `admin_init`, or run `wp eval 'Moon_Settings::maybe_migrate();'`).

## Where the dialogs live

The quick view and size guide shells are printed by
`template-parts/footer/quick-view.php`, which `footer.php` only includes when the
`moon-shop` stylesheet is enqueued — on other screens their markup used to fall
out of the flow and appear under the footer.

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
