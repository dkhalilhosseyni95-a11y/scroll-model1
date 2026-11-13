#!/bin/sh
# One-shot WordPress setup for the MOON sandbox.
# Idempotent: installing, activating and importing are all no-ops on later boots.
set -eu

cd /var/www/html

echo "→ waiting for WordPress core files"
i=0
while [ ! -f wp-includes/version.php ] || [ ! -f wp-config.php ]; do
  i=$((i + 1))
  if [ "$i" -gt 90 ]; then
    echo "✗ WordPress core files did not appear" >&2
    exit 1
  fi
  sleep 2
done

echo "→ waiting for the database"
i=0
until wp db check --quiet >/dev/null 2>&1; do
  i=$((i + 1))
  if [ "$i" -gt 60 ]; then
    echo "✗ database is not reachable" >&2
    exit 1
  fi
  sleep 2
done

if ! wp core is-installed >/dev/null 2>&1; then
  echo "→ installing WordPress"
  wp core install \
    --url="http://localhost:3000" \
    --title="MOON" \
    --admin_user="${MOON_ADMIN_USER:-admin}" \
    --admin_password="${MOON_ADMIN_PASSWORD:-moon-dev-admin}" \
    --admin_email="${MOON_ADMIN_EMAIL:-admin@example.com}" \
    --skip-email
fi

# The sandbox preview host changes; keep stored URLs on localhost and let the
# mu-plugin rewrite them per request while BASE44_PREVIEW_MODE=1.
wp option update home "http://localhost:3000" >/dev/null
wp option update siteurl "http://localhost:3000" >/dev/null
wp option update blogname "MOON" >/dev/null
wp option update blogdescription "پوشاک لوکس زنانه و مردانه؛ ساخته‌شده برای زمان." >/dev/null
wp rewrite structure '/%postname%/' >/dev/null 2>&1 || true

echo "→ theme and plugins"
wp theme activate moon-theme
wp plugin activate moon-core

if ! wp plugin is-installed woocommerce >/dev/null 2>&1; then
  echo "→ installing WooCommerce"
  wp plugin install woocommerce --activate
else
  wp plugin activate woocommerce
fi

# Persian locale (network dependent; the site works in English if unavailable).
wp language core install fa_IR --activate >/dev/null 2>&1 || true
wp language plugin install woocommerce fa_IR >/dev/null 2>&1 || true

echo "→ store settings"
wp eval 'if ( class_exists( "WC_Install" ) ) { WC_Install::create_pages(); }' >/dev/null 2>&1 || true
wp option update woocommerce_store_address "Tehran" >/dev/null 2>&1 || true
wp option update woocommerce_default_country "IR:THR" >/dev/null 2>&1 || true
wp option update woocommerce_currency "IRT" >/dev/null 2>&1 || true
wp option update woocommerce_price_num_decimals "0" >/dev/null 2>&1 || true
wp option update woocommerce_price_thousand_sep "٬" >/dev/null 2>&1 || true
wp option update woocommerce_price_decimal_sep "." >/dev/null 2>&1 || true
wp option update woocommerce_currency_pos "right_space" >/dev/null 2>&1 || true
wp option update woocommerce_enable_ajax_add_to_cart "yes" >/dev/null 2>&1 || true
wp option update woocommerce_cart_redirect_after_add "no" >/dev/null 2>&1 || true
wp option update woocommerce_permalinks '{"product_base":"/product","category_base":"product-category"}' --format=json >/dev/null 2>&1 || true

# A working checkout needs at least one gateway and one shipping method. The
# sandbox has no real payment provider credentials, so cash on delivery is
# enabled; replace it with the store's real gateway in WooCommerce > Settings.
echo "→ payments and shipping"
wp eval '
if ( ! class_exists( "WooCommerce" ) ) { return; }

update_option( "woocommerce_cod_settings", array(
	"enabled"           => "yes",
	"title"             => "پرداخت در محل",
	"description"       => "هزینه سفارش را هنگام تحویل پرداخت کنید.",
	"instructions"      => "",
	"enable_for_methods" => array(),
	"enable_for_virtual" => "yes",
) );

$zones  = WC_Shipping_Zones::get_zones();
$exists = false;
foreach ( $zones as $zone ) {
	if ( "ایران" === $zone["zone_name"] ) {
		$exists = true;
	}
}

if ( ! $exists ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( "ایران" );
	$zone->add_location( "IR", "country" );
	$zone->save();
	$instance = $zone->add_shipping_method( "flat_rate" );
	update_option( "woocommerce_flat_rate_" . $instance . "_settings", array(
		"title"      => "ارسال استاندارد",
		"tax_status" => "taxable",
		"cost"       => "45000",
	) );
}
' >/dev/null 2>&1 || true

echo "→ demo content"
wp moon-demo import --products

echo "→ rewrite rules"
wp rewrite flush --hard >/dev/null 2>&1 || wp rewrite flush >/dev/null 2>&1 || true

echo "✓ setup complete"
