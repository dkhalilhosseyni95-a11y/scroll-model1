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
  (store url, currency IRT, price separators, customer registration), a COD
  gateway and an "ایران" shipping zone, then `wp moon-demo import --products`
  and `wp moon-demo catalog`.
- `wp moon-demo catalog` (also `includes/modules/class-moon-catalog.php`) turns
  the demo catalogue into real variable products: `pa_color` + `pa_size`
  variations, one image per colourway, the kids/scarves categories, multi-piece
  looks and the full size guide. It is idempotent and rebuilds the variation
  grid each run, so it is safe to repeat after changing a colourway.
- The store must not be in WooCommerce's "coming soon" mode — `setup.sh` and the
  demo importer both switch it off; otherwise every front-end page shows the
  launch screen.

## Where the shop logic lives

- `moon-core/includes/modules/class-moon-shop.php` — card data, quick view AJAX
  (`moon_quick_view`), shop filter AJAX (`moon_filter_products`), pagination,
  filter panel markup and the size-guide tables.
- `moon-core/includes/modules/class-moon-colors.php` — colourways. Variable
  products use `pa_color` (term meta `moon_hex`); simple products can use the
  `_moon_colorways` meta. It also replaces WooCommerce's attribute `<select>`
  with swatches **without removing the select**, so the variations script and
  the cart keep working.
- `moon-core/includes/modules/class-moon-otp.php` — phone login/registration
  and the SMS hook. See `docs/PHONE-LOGIN.md`.
- `assets/js/shop.js` — quick view, AJAX cart, AJAX filters, swatches, quantity
  steppers, colourway switching, the size-guide dialog and the login forms.
  `chrome.js` exposes `window.Moon` (cart fragments, side panels) for it.
- `woocommerce/archive-product.php` renders the filter column plus the grid the
  AJAX handlers replace; `assets/css/shop.css` styles everything after
  `woocommerce.css`.

## Things that are easy to get wrong

- `wp post delete` does not remove an order: HPOS keeps it. Use
  `wp wc shop_order delete <id> --force=true --user=admin`.
- WooCommerce ships block cart/checkout pages. The theme styles the *classic*
  screens, so `Moon_Demo_Importer::prepare_store()` converts them to
  `[woocommerce_cart]` / `[woocommerce_checkout]`.
- `wc-cart-fragments` is only enqueued by the cart widget, so the theme enqueues
  it explicitly — the header quick-cart drawer needs it to refresh. The theme
  also registers the drawer body and the counter as cart fragments.
- Persian digits: `moon_fa_digits()` must never rewrite numeric HTML entities
  (WooCommerce currency symbols are `&#x062A;…`); it protects them with a regex.
- WooCommerce strings come from the plugin's fa_IR language pack
  (`wp language plugin install woocommerce fa_IR`).
- Quick view templates are rendered through `wc_get_template()`, which extracts
  the arguments into scope; the template must **not** re-declare
  `global $product` or the product becomes null.
- Look pages are `moon_outfit` posts with `rewrite => look`; the pieces come from
  the `_moon_products` meta (edited in the "اقلام این استایل" meta box).

## Verifying a change

- Product cards, prices, colour swatches and the quick view appear on `/shop/`
  and the homepage carousel; the header bag opens the quick-cart drawer.
- Shop filters (category, colour, size, price) update the grid without a reload
  and write the selection into the URL.
- A look page (`/look/<style>/`) lists its pieces, swaps a piece image when a
  colourway is chosen, and "افزودن کل استایل به سبد" adds every piece.
- Product page: colour swatches swap the variation image, sizes show stock, the
  add-to-cart button works without a page reload, and the size guide opens.
- Account: `/my-account/` shows phone login, password login and registration
  tabs; see `docs/PHONE-LOGIN.md` for the SMS setup.
- Checkout: add a product, open `/checkout/`, expect "ارسال استاندارد" (45,000)
  and "پرداخت در محل", then place the order. Remove test orders afterwards.
