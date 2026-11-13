# MOON — development notes

WordPress theme (`wp-content/themes/moon-theme`) plus its companion plugin
(`wp-content/plugins/moon-core`). There is no build step: PHP, CSS and JS are
served as written.

## Running the sandbox

```sh
docker compose -f docker-compose.base44.yml up -d --build   # db + wordpress on :3000
docker compose -f docker-compose.base44.yml run --rm setup  # idempotent install/import
```

- WordPress core lives in the `wp_data` volume; only the theme and plugin are
  bind mounted, so file edits are live.
- There is no PHP or wp-cli on the sandbox host. Lint with
  `docker compose -f docker-compose.base44.yml exec -T wordpress php -l <file>`
  and run wp-cli through the one-shot service:
  `docker compose -f docker-compose.base44.yml run --rm --entrypoint wp setup <args>`.
- `docker/setup.sh` is the runbook: core install, fa_IR locale, WooCommerce
  (store url, currency IRT, price separators), a COD gateway and an "ایران"
  shipping zone, then `wp moon-demo import --products`.
- The store must not be in WooCommerce's "coming soon" mode — `setup.sh` and the
  demo importer both switch it off; otherwise every front-end page shows the
  launch screen.

## Things that are easy to get wrong

- `wp post delete` does not remove an order: HPOS keeps it. Use
  `wp wc shop_order delete <id> --force=true --user=admin`.
- WooCommerce ships block cart/checkout pages. The theme styles the *classic*
  screens, so `Moon_Demo_Importer::prepare_store()` converts them to
  `[woocommerce_cart]` / `[woocommerce_checkout]`.
- `wc-cart-fragments` is only enqueued by the cart widget, so the theme enqueues
  it explicitly — the header quick-cart drawer needs it to refresh.
- Persian digits: `moon_fa_digits()` must never rewrite numeric HTML entities
  (WooCommerce currency symbols are `&#x062A;…`); it protects them with a regex.
- WooCommerce strings come from the plugin's fa_IR language pack
  (`wp language plugin install woocommerce fa_IR`).

## Verifying a change

- Product cards, prices and add-to-cart appear on `/shop/` and the homepage
  carousel; the header bag opens the quick-cart drawer.
- Checkout: add a product, open `/checkout/`, expect "ارسال استاندارد" (45,000)
  and "پرداخت در محل", then place the order. Remove test orders afterwards.
