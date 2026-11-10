# MOON Core — repository notes

WordPress storefront delivered as the `moon-core` plugin (Hello Elementor theme,
Elementor, WooCommerce). No build step: PHP/CSS/JS in `plugins/moon-core/` are
served directly from the bind mount.

## Run the sandbox stack

```sh
docker compose -f docker-compose.base44.yml up -d     # db, perms, wordpress, setup
curl -I http://localhost:3000/                        # expect 200
```

- `db` — MariaDB; credentials are local infra values inline in compose (not secrets).
- `perms` — one-shot root job handing `wp-content` to uid 33 so media import works.
  Keep `setup` depending on it (`service_completed_successfully`).
- `wordpress` — serves on host port 3000. Core lives in the `wp_core` volume, so a
  new image tag does not upgrade an existing volume.
- `setup` — idempotent installer (`sandbox/setup.sh`): core install, fa_IR,
  Hello Elementor, Elementor + WooCommerce, Moon Core activation, demo import,
  `wp media regenerate --only-missing`.
- `cli` — `docker compose -f docker-compose.base44.yml run --rm cli <args>`.

## Gotchas found while building this

- **WooCommerce load order.** `moon-core` sorts before `woocommerce`, so
  `class_exists('WooCommerce')` is false while the plugin file loads. All shop
  hooks are registered on `plugins_loaded` priority 20 — keep it that way or the
  filters, badges and wishlist silently disappear.
- **The widget base class must not load at plugin boot.** It extends
  `Elementor\Widget_Base`; it is required inside `elementor/widgets/register`.
- **RTL stylesheets are additive.** `wp_style_add_data($handle, 'rtl', 'replace')`
  swaps the base file for the RTL one and strips the design system. The RTL files
  only contain overrides and are enqueued after the base on RTL locales.
- **Elementor element cache.** Re-importing documents without deleting
  `_elementor_element_cache` (and `_elementor_css`) keeps the old markup on screen.
- **WooCommerce "coming soon".** Fresh installs set `woocommerce_coming_soon=yes`,
  which serves a launch screen to visitors; `setup.sh` and the importer set it to
  `no`.
- **Thumbnails.** `woocommerce_thumbnail_cropping = 3:4` plus width 600 yields the
  600×800 portrait cards the CSS expects. Changing it later needs
  `wp media regenerate --yes`.
- **Preview origin.** `sandbox/mu-plugins/base44-preview.php` acts only when
  `BASE44_PREVIEW_MODE` is exactly `1`; it rewrites `home`/`siteurl` and
  `HTTP_HOST` to `MOON_PREVIEW_ORIGIN`. Without the host rewrite WordPress
  canonical-redirects forever behind the proxy. Never hardcode the origin.
- **Healthcheck before install.** The DB has no `wp_options` until the installer
  runs, so the check treats "reachable but empty" as healthy (it gates `setup`).

## Packaging

`dist/moon-core.zip` and `dist/moon-elementor-templates.zip` are rebuilt with:

```sh
docker run --rm -v "$PWD:/repo" -w /repo wordpress:7.1.4-php8.2-apache php tools/build-plugin.php
```

Elementor JSON templates come from the same builders used by the importer:

```sh
docker compose -f docker-compose.base44.yml run --rm -T \
  -v "$PWD/tools:/opt/tools:ro" -v "$PWD/templates/elementor:/opt/export" \
  cli eval-file /opt/tools/export-templates.php
```

Demo imagery is generated, never downloaded:

```sh
docker run --rm -v "$PWD:/repo" -w /repo wordpress:7.1.4-php8.2-apache php tools/make-images.php
```

After regenerating images, delete demo attachments (`_moon_demo_file` meta) and run
`Moon_Core_Demo::run()` so products and Elementor documents pick up the new ids.

## Checks

No automated test suite. Verify by requesting `/`, `/shop/`,
`/product-category/women/`, `/cart/`, `/about/`, `/faq/`, `/track-order/`,
`/my-account/` (all 200) and by posting to `admin-ajax.php` with
`action=moon_filter_products` (needs the `moonCore.nonce` value from a page).
`docs/test-report.md` lists exactly what was verified and what was not.
