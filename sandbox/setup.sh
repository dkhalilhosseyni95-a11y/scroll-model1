#!/bin/sh
# Base44 sandbox installer for the DaniSite WordPress stack.
#
# Idempotent: installing core, activating the theme/plugin, installing Elementor
# and importing the demo content are all safe to repeat. The demo import itself
# only runs once (it updates equivalent pages instead of duplicating them).
set -eu

cd /var/www/html

# WP-CLI otherwise tries to cache downloads in $HOME/.wp-cli, which is not
# writable for www-data in these images.
export WP_CLI_CACHE_DIR=/tmp/wp-cli-cache
mkdir -p "$WP_CLI_CACHE_DIR"

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

echo "== waiting for database =="
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
	echo "== installing wordpress =="
	wp core install \
		--url="${DANI_PREVIEW_ORIGIN:-http://localhost:3000}" \
		--title="DaniSite" \
		--admin_user="${DANI_ADMIN_USER:-admin}" \
		--admin_password="${DANI_ADMIN_PASSWORD:-danisite-demo}" \
		--admin_email="admin@danisite.local" \
		--skip-email
fi

echo "== locale, permalinks, timezone =="
wp core update-db >/dev/null 2>&1 || true
wp language core install fa_IR --activate >/dev/null 2>&1 || true
wp option update WPLANG fa_IR >/dev/null 2>&1 || true
wp option update timezone_string "Asia/Tehran" >/dev/null 2>&1 || true
wp rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true

echo "== elementor (free) =="
if ! wp plugin is-installed elementor >/dev/null 2>&1; then
	wp plugin install elementor --activate >/dev/null 2>&1 || echo "elementor could not be downloaded (offline?)"
fi
wp plugin activate elementor >/dev/null 2>&1 || true

echo "== theme + core plugin =="
wp theme activate danisite-child >/dev/null 2>&1 || wp theme activate danisite-parent
wp plugin activate danisite-core >/dev/null 2>&1 || true

echo "== demo import =="
if ! wp option get dani_demo_imported >/dev/null 2>&1; then
	if wp eval 'print_r( Dani_Importer::run() );' >/tmp/dani-import.log 2>&1; then
		echo "import finished"
	else
		echo "import reported errors:"
		cat /tmp/dani-import.log || true
	fi
else
	echo "demo content already imported"
fi

wp rewrite flush >/dev/null 2>&1 || true
wp cache flush >/dev/null 2>&1 || true

echo "== setup complete =="
