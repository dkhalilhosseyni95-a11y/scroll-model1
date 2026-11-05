# DaniSite — repository notes

WordPress theme (parent + child) and the `danisite-core` plugin, developed from the
Lovable design export. There is no build step: PHP, CSS and JS in `themes/` and
`plugins/` are served directly.

## Run the sandbox stack

```sh
docker compose -f docker-compose.base44.yml up -d     # db, perms, wordpress, setup
curl -I http://localhost:3000/                        # expect 200
```

- `db` — MariaDB, local credentials live inline in the compose file (not secrets).
- `perms` — one-shot, runs as root and gives the `wp_uploads` volume to uid 33.
  Without it WordPress cannot create `wp-content/uploads/<year>/<month>` and media
  uploads fail. Keep `setup` depending on it (`service_completed_successfully`).
- `wordpress` — serves on host port 3000. Core files live in the `wp_core` volume,
  so changing the image tag does **not** upgrade an existing volume; copy the new
  core in (`cp -a /usr/src/wordpress/. /var/www/html/`) or `wp core update`.
- `setup` — idempotent installer (`sandbox/setup.sh`): core install, Persian locale,
  Elementor, theme/plugin activation, demo import. Runs via `sh` because the mounted
  script has no executable bit.

## Gotchas

- The WordPress image generates `wp-config.php` with `getenv_docker()`, so the database
  constants are resolved **at runtime**. Every WP-CLI service (`setup`, `cli`) must get
  the same `WORDPRESS_DB_*` environment as the `wordpress` service, otherwise WP-CLI
  falls back to the host `mysql` and every command fails.
- Elementor requires WordPress >= 6.8, which is why the image is pinned to
  `wordpress:7.1.4-php8.2-apache` (the original 6.7 image silently refused to install it).
- `sandbox/mu-plugins/base44-preview.php` only acts when `BASE44_PREVIEW_MODE` is exactly
  `1`. It forces `home`/`siteurl` to the public preview origin **and rewrites
  `$_SERVER['HTTP_HOST']`/`SERVER_NAME` to that origin**; without the host rewrite the
  proxy's internal Host header makes core's canonical redirect loop
  (`ERR_TOO_MANY_REDIRECTS`). Unset or any other value = normal production behaviour.
- Uploads/media need a writable `wp-content/uploads`; the `perms` job owns that. If the
  demo import ran before it (media failed silently), re-run
  `wp eval 'print_r( Dani_Importer::run() );'` — the importer is idempotent and will
  attach the missing images.
- Elementor's `URL` control expects an **array** default (`array( 'url' => … )`). A
  string default makes control registration throw `array_merge(): Argument #2 must be
  of type array`, which fatals the whole page in `wp_head`. `Dani_Widget_Base::text()`
  handles this; keep any new URL control array-defaulted.

## WP-CLI

```sh
docker compose -f docker-compose.base44.yml run --rm cli plugin list
docker compose -f docker-compose.base44.yml run --rm --entrypoint sh setup -c 'wp option get blogname'
```

WP-CLI needs a writable cache dir in these images; `setup.sh` exports
`WP_CLI_CACHE_DIR=/tmp/wp-cli-cache`. Add the same when running CLI by hand that
installs packages.

## Packaging

`dist/*.zip` are the installable parent theme, child theme and plugin. Rebuild them
after any change to `themes/` or `plugins/`:

```sh
docker compose -f docker-compose.base44.yml run --rm -T --user 0:0 --entrypoint php \
  -v "$PWD:/opt/repo" setup /opt/repo/tools/build-packages.php
```

`--user 0:0` is required: the checkout is root-owned, and the `setup` service runs as
uid 33, so the bind mount is otherwise unreadable ("Permission denied" on /opt/repo).
The script needs PHP's `zip` extension, which the WordPress image provides.

`docs/installation.md`, `docs/content-guide.md` and `docs/test-report.md` are the
delivered (Persian) documentation; keep them in sync when behaviour changes
(importer steps, settings tabs, shortcodes, widgets).

## Checks

There is no automated test suite. Verify by loading the routes (`/`, `/about/`,
`/services/`, `/portfolio/`, `/blog/`, `/contact/`, `/faq/`, `/terms/`,
`/cookie-policy/`) and looking at the rendered page; all of them answer 200, an
unknown slug answers 404.
