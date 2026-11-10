#!/bin/sh
# Base44 sandbox installer for the MOON storefront.
#
# Idempotent: core install, locale, theme, plugins and the demo import can all be
# re-run. The demo importer updates existing documents instead of duplicating them.
set -eu

cd /var/www/html

# WP-CLI needs a writable cache directory for www-data.
export WP_CLI_CACHE_DIR=/tmp/wp-cli-cache
mkdir -p "$WP_CLI_CACHE_DIR"

echo "== waiting for wp-config.php =="
n=0
while [ ! -f wp-config.php ] && [ "$n" -lt 90 ]; do
	n=$((n + 1))
	sleep 2
done

if [ ! -f wp-config.php ]; then
	echo "wp-config.php not found — the wordpress service did not finish starting"
	exit 1
fi

echo "== waiting for database =="
n=0
until wp db check >/dev/null 2>&1; do
	n=$((n + 1))
	if [ "$n" -gt 90 ]; then
		echo "database not reachable"
		exit 1
	fi
	sleep 2
done

ORIGIN="${MOON_PREVIEW_ORIGIN:-http://localhost:3000}"

if ! wp core is-installed >/dev/null 2>&1; then
	echo "== installing WordPress =="
	wp core install \
		--url="$ORIGIN" \
		--title="${MOON_SITE_TITLE:-MOON}" \
		--admin_user="${MOON_ADMIN_USER:-admin}" \
		--admin_password="${MOON_ADMIN_PASSWORD:-moon-demo-2024}" \
		--admin_email="${MOON_ADMIN_EMAIL:-admin@moon.local}" \
		--skip-email
fi

echo "== locale, permalinks, timezone =="
wp core update-db >/dev/null 2>&1 || true
wp language core install fa_IR --activate >/dev/null 2>&1 || true
wp option update WPLANG fa_IR >/dev/null 2>&1 || true
wp option update timezone_string "Asia/Tehran" >/dev/null 2>&1 || true
wp rewrite structure '/%postname%/' >/dev/null 2>&1 || true

echo "== theme: Hello Elementor =="
if ! wp theme is-installed hello-elementor >/dev/null 2>&1; then
	wp theme install hello-elementor >/dev/null 2>&1 || echo "hello-elementor could not be downloaded"
fi
wp theme activate hello-elementor >/dev/null 2>&1 || true

echo "== plugins: Elementor + WooCommerce =="
for plugin in elementor woocommerce; do
	if ! wp plugin is-installed "$plugin" >/dev/null 2>&1; then
		wp plugin install "$plugin" >/dev/null 2>&1 || echo "$plugin could not be downloaded (offline?)"
	fi
	wp plugin activate "$plugin" >/dev/null 2>&1 || true
done

wp plugin activate moon-core >/dev/null 2>&1 || true

echo "== WooCommerce pages and onboarding =="
wp wc tool run install_pages --user=1 >/dev/null 2>&1 || true
wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json >/dev/null 2>&1 || true
# WooCommerce 9.6+ shows a "coming soon" screen until the store is launched.
wp option update woocommerce_coming_soon "no" >/dev/null 2>&1 || true
wp option update woocommerce_task_list_hidden "yes" >/dev/null 2>&1 || true
wp option update woocommerce_allow_tracking "no" >/dev/null 2>&1 || true

echo "== Moon Core demo content =="
if ! wp eval 'Moon_Core_Demo::run();' >/tmp/moon-import.log 2>&1; then
	echo "demo import reported an error:"
	cat /tmp/moon-import.log || true
fi

echo "== translations =="
if [ -d wp-content/plugins/moon-core/languages ]; then
	wp i18n make-mo wp-content/plugins/moon-core/languages >/dev/null 2>&1 || true
fi

wp rewrite flush --hard >/dev/null 2>&1 || true
wp cache flush >/dev/null 2>&1 || true

echo "== ready =="
wp option get home
