#!/bin/sh
# Base44 sandbox installer for the MOON WordPress stack.
#
# Idempotent: WordPress core, the theme, the plugins and the starter content are
# all safe to install repeatedly. The starter import updates the same page, menu
# and demo products instead of duplicating them.
set -eu

cd /var/www/html

# WP-CLI caches downloads in $HOME/.wp-cli, which is not writable for www-data.
export WP_CLI_CACHE_DIR=/tmp/wp-cli-cache
mkdir -p "$WP_CLI_CACHE_DIR"

SITE_URL="${MOON_PREVIEW_ORIGIN:-http://localhost:3000}"

echo "== waiting for wp-config.php =="
n=0
while [ ! -f wp-config.php ] && [ "$n" -lt 60 ]; do
	n=$((n + 1))
	sleep 2
done

if [ ! -f wp-config.php ]; then
	echo "wp-config.php not found — the wordpress service did not finish starting"
	exit 1
fi

echo "== waiting for the database =="
n=0
until wp db check >/dev/null 2>&1; do
	n=$((n + 1))
	if [ "$n" -gt 60 ]; then
		echo "database not reachable"
		exit 1
	fi
	sleep 2
done

if ! wp core is-installed >/dev/null 2>&1; then
	echo "== installing WordPress ($SITE_URL) =="
	wp core install \
		--url="$SITE_URL" \
		--title="MOON" \
		--admin_user="${MOON_ADMIN_USER:-admin}" \
		--admin_password="${MOON_ADMIN_PASSWORD:-moon-demo}" \
		--admin_email="${MOON_ADMIN_EMAIL:-admin@moon.local}" \
		--skip-email
fi

wp option update blogdescription "Modern wardrobe, editorial spirit" >/dev/null 2>&1 || true
wp rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true
wp option update timezone_string "Europe/Lisbon" >/dev/null 2>&1 || true

echo "== parent theme (Hello Elementor) =="
if ! wp theme is-installed hello-elementor >/dev/null 2>&1; then
	wp theme install hello-elementor >/dev/null 2>&1 || echo "hello-elementor could not be downloaded"
fi
wp theme activate hello-elementor >/dev/null 2>&1 || true

echo "== Elementor =="
if ! wp plugin is-installed elementor >/dev/null 2>&1; then
	wp plugin install elementor >/dev/null 2>&1 || echo "elementor could not be downloaded"
fi
wp plugin activate elementor >/dev/null 2>&1 || true

echo "== WooCommerce =="
if ! wp plugin is-installed woocommerce >/dev/null 2>&1; then
	wp plugin install woocommerce >/dev/null 2>&1 || echo "woocommerce could not be downloaded"
fi
wp plugin activate woocommerce >/dev/null 2>&1 || true

echo "== MOON child theme + Moon Core =="
wp theme activate moon-child >/dev/null 2>&1 || echo "moon-child activation failed"
wp plugin activate moon-core >/dev/null 2>&1 || echo "moon-core activation failed"

echo "== journal entry =="
if ! wp post list --post_type=post --name=journal --format=ids 2>/dev/null | grep -q '[0-9]'; then
	wp post create --post_type=post --post_status=publish --post_title="Field notes from the atelier" --post_name=journal --post_content="<p>Notes on cutting slowly, the mills we work with and why a coat should outlive its season.</p>" >/dev/null 2>&1 || true
fi

echo "== starter content (homepage, menus, demo catalogue) =="
wp eval 'print_r( \MoonCore\Starter_Content::import( array( "products" => true, "pages" => true ) ) );' || echo "starter import failed"

echo "== store currency for the demo catalogue =="
wp option update woocommerce_currency EUR >/dev/null 2>&1 || true
# The sandbox is a live preview, not a pre-launch store.
wp option update woocommerce_coming_soon no >/dev/null 2>&1 || true
wp eval '\MoonCore\Starter_Content::use_classic_store_pages();' >/dev/null 2>&1 || true

echo "== rewrite rules =="
wp rewrite flush --hard >/dev/null 2>&1 || true

echo "== versions =="
wp core version
wp plugin list --status=active --field=name,version 2>/dev/null || true
wp theme list --status=active --field=name,version 2>/dev/null || true

echo "MOON setup complete."
