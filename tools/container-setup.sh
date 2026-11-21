#!/bin/sh
# First-boot installer for the MOON sandbox (runs inside the `setup` service).
# Idempotent: safe to run on every `docker compose up`.
set -e

WP="wp --path=/var/www/html --allow-root"

echo "Waiting for WordPress files…"
i=0
while [ ! -f /var/www/html/wp-config.php ] && [ "$i" -lt 90 ]; do
	sleep 2
	i=$((i + 1))
done

if ! $WP core is-installed >/dev/null 2>&1; then
	echo "Installing WordPress…"
	$WP core install \
		--url=http://localhost:3000 \
		--title="MOON" \
		--admin_user=admin \
		--admin_email=admin@example.com \
		--admin_password="${WP_ADMIN_PASSWORD:-moon_local_admin}" \
		--skip-email
fi

if ! $WP plugin is-installed woocommerce >/dev/null 2>&1; then
	echo "Installing WooCommerce…"
	$WP plugin install woocommerce --activate
else
	$WP plugin activate woocommerce >/dev/null 2>&1 || true
fi

echo "Activating the theme and plugins…"
$WP theme activate moon-ultimate
$WP plugin activate moon-companion >/dev/null 2>&1 || true
$WP plugin activate moon-demo-importer >/dev/null 2>&1 || true

# Behind the preview proxy the public origin is the only reachable host, and
# WordPress builds WP_CONTENT_URL/WP_PLUGIN_URL from these options at load
# time. Keep them on the preview origin (sandbox only; unset = untouched).
if [ "${BASE44_PREVIEW_MODE:-}" = "1" ] && [ -n "${BASE44_PUBLIC_HOST_SUFFIX:-}" ]; then
	echo "Pointing the site at the preview origin…"
	$WP option update home "https://3000-${BASE44_PUBLIC_HOST_SUFFIX}" >/dev/null
	$WP option update siteurl "https://3000-${BASE44_PUBLIC_HOST_SUFFIX}" >/dev/null
fi

echo "Persian language…"
$WP language core install fa_IR >/dev/null 2>&1 || true
$WP site switch-language fa_IR >/dev/null 2>&1 || true
$WP rewrite structure '/%postname%/' >/dev/null 2>&1 || true
$WP option update blogdescription 'فروشگاه پوشاک و اکسسوری MOON' >/dev/null 2>&1 || true

echo "Demo content…"
$WP moon-demo import --products --catalog || echo "demo import reported errors (continuing)"

echo "Setup finished."
