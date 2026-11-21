#!/usr/bin/env bash
#
# First-run setup for the MOON ULTIMATE sandbox: installs WordPress and
# WooCommerce, activates the theme and both plugins, then imports the demo.
#
# Usage: tools/setup-wordpress.sh [--with-demo] [--with-products]
#
set -euo pipefail

cd "$(dirname "$0")/.."

COMPOSE="docker compose -f docker-compose.base44.yml"
WP="$COMPOSE exec -T cli wp --path=/var/www/html --allow-root"

echo "Waiting for WordPress to answer…"
for i in $(seq 1 60); do
  if curl -fsS -o /dev/null http://localhost:3000/wp-login.php; then
    break
  fi
  sleep 2
done

if ! curl -fsS -o /dev/null http://localhost:3000/wp-login.php; then
  echo "WordPress did not answer on port 3000." >&2
  exit 1
fi

if ! $WP core is-installed >/dev/null 2>&1; then
  echo "Installing WordPress…"
  # The admin password lives in the platform secret store, which is only
  # available inside the cli container (env_file), never on the host.
  $COMPOSE exec -T cli sh -ec 'wp --path=/var/www/html --allow-root core install \
    --url=http://localhost:3000 \
    --title="MOON ULTIMATE" \
    --admin_user=admin \
    --admin_email=admin@example.com \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --skip-email'
fi

echo "Installing WooCommerce…"
if ! $WP plugin is-installed woocommerce; then
  $WP plugin install woocommerce --activate
else
  $WP plugin activate woocommerce || true
fi

echo "Activating the theme and plugins…"
$WP theme activate moon-ultimate
$WP plugin activate moon-companion
$WP plugin activate moon-demo-importer

echo "Installing the Persian language pack…"
$WP language core install fa_IR >/dev/null 2>&1 || true
$WP site switch-language fa_IR >/dev/null 2>&1 || true

$WP rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true
$WP option update blogdescription 'MOON ULTIMATE — فروشگاه پوشاک لوکس' >/dev/null

if [[ " $* " == *" --with-demo "* ]]; then
  echo "Importing the demo content…"
  if [[ " $* " == *" --with-products "* ]]; then
    $WP moon-demo import --products --catalog
  else
    $WP moon-demo import --catalog
  fi
fi

echo "Setup finished."
