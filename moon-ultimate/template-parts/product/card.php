<?php
/**
 * Product card, shared by the homepage carousel and the shop loop.
 *
 * Pass a WooCommerce product to get the full card (colour swatches, rating,
 * quick add); the array form stays for the bundled demo content.
 *
 * Every optional element is switched in the panel (فروشگاه → کارت محصول) and
 * the layout variant comes from the `moon-cards-{style}` body class.
 *
 * @param array $args Card data: product, id, name, en, price, image, hover, url, tag, colors,
 *                    sizes, rating, reviews, add_id, desc, category.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

$raw  = isset( $args ) && is_array( $args ) ? $args : array();
$card = wp_parse_args(
	$raw,
	array(
		'product'     => null,
		'id'          => 0,
		'name'        => '',
		'en'          => '',
		'price'       => '',
		'image'       => '',
		'hover'       => '',
		'url'         => '',
		'category'    => '',
		'tag'         => '',
		'on_sale'     => false,
		'in_stock'    => true,
		'colors'      => array(),
		'sizes'       => array(),
		'rating'      => 0,
		'reviews'     => 0,
		'add_id'      => 0,
		'add_url'     => '',
		'is_variable' => false,
		'desc'        => '',
	)
);

if ( $card['product'] instanceof WC_Product && class_exists( 'Moon_Shop' ) ) {
	$card            = wp_parse_args( Moon_Shop::card_data( $card['product'] ), $card );
	$card['product'] = $raw['product'];
}

$card_url = $card['url'] ? $card['url'] : moon_shop_url();
$sold_out = empty( $card['in_stock'] );

if ( ! $card['image'] ) {
	$card['image'] = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'moon-card' ) : moon_asset( 'women.jpg' );
}

$english = $card['en'] ? $card['en'] : '';

if ( $english && ( false !== strpos( $english, '%' ) || preg_match( '/\p{Arabic}/u', $english ) ) ) {
	$english = '';
}

/*
 * Card elements are switched on and off in the panel (فروشگاه → کارت محصول).
 * The defaults match the shipped design when the panel is not available.
 */
$card_cfg = array(
	'hover'     => true,
	'badges'    => true,
	'rating'    => true,
	'swatches'  => true,
	'quickview' => true,
	'wishlist'  => true,
	'compare'   => true,
	'stock'     => true,
);

if ( class_exists( 'Moon_Settings' ) ) {
	foreach ( $card_cfg as $card_key => $card_default ) {
		$card_cfg[ $card_key ] = (bool) Moon_Settings::get( 'card_' . $card_key, $card_default ? '1' : '0' );
	}
}

$card_cfg['hover'] = $card_cfg['hover'] && ! empty( $card['hover'] );

$stock_left = 0;

if ( $card_cfg['stock'] && $card['product'] instanceof WC_Product ) {
	$stock_left = $card['product']->managing_stock() ? (int) $card['product']->get_stock_quantity() : 0;
}
?>
<article
	class="moon-card<?php echo $sold_out ? ' is-soldout' : ''; ?><?php echo $card['on_sale'] ? ' is-onsale' : ''; ?>"
	data-moon-card
	data-product="<?php echo esc_attr( (string) $card['id'] ); ?>"
>
	<div class="moon-card__media">
		<a class="moon-card__media-link" href="<?php echo esc_url( $card_url ); ?>" tabindex="-1" aria-hidden="true">
			<img
				class="moon-card__img"
				data-moon-card-img
				src="<?php echo esc_url( $card['image'] ); ?>"
				<?php if ( $card['hover'] ) : ?>data-default="<?php echo esc_url( $card['image'] ); ?>"<?php endif; ?>
				alt="<?php echo esc_attr( $card['name'] ); ?>"
				width="900"
				height="1200"
				loading="lazy"
				decoding="async"
			>
			<?php if ( $card_cfg['hover'] ) : ?>
				<img
					class="moon-card__hover"
					src="<?php echo esc_url( $card['hover'] ); ?>"
					alt=""
					width="900"
					height="1200"
					loading="lazy"
					decoding="async"
				>
			<?php endif; ?>
		</a>

		<?php if ( $card_cfg['badges'] ) : ?>
			<div class="moon-card__badges">
				<?php if ( $sold_out ) : ?>
					<span class="moon-card__badge moon-card__badge--muted"><?php esc_html_e( 'ناموجود', 'moon-theme' ); ?></span>
				<?php elseif ( $card['tag'] ) : ?>
					<span class="moon-card__badge<?php echo $card['on_sale'] ? ' moon-card__badge--sale' : ''; ?>"><?php echo esc_html( $card['tag'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="moon-card__tools">
			<?php if ( $card['id'] && $card_cfg['wishlist'] ) : ?>
				<button
					type="button"
					class="moon-card__like"
					data-moon-like
					data-id="<?php echo esc_attr( (string) $card['id'] ); ?>"
					data-name="<?php echo esc_attr( $card['name'] ); ?>"
					data-url="<?php echo esc_url( $card_url ); ?>"
					data-image="<?php echo esc_url( $card['image'] ); ?>"
					aria-pressed="false"
					aria-label="<?php echo esc_attr( sprintf( __( 'افزودن %s به علاقه‌مندی‌ها', 'moon-theme' ), $card['name'] ) ); ?>"
				>
					<?php moon_the_icon( 'heart', 15 ); ?>
				</button>
			<?php endif; ?>

			<?php if ( $card['id'] && $card_cfg['compare'] ) : ?>
				<button
					type="button"
					class="moon-card__like moon-card__compare"
					data-moon-compare-add="<?php echo esc_attr( (string) $card['id'] ); ?>"
					data-name="<?php echo esc_attr( $card['name'] ); ?>"
					aria-pressed="false"
					aria-label="<?php echo esc_attr( sprintf( __( 'افزودن %s به مقایسه', 'moon-theme' ), $card['name'] ) ); ?>"
				>
					<?php moon_the_icon( 'compare', 15 ); ?>
				</button>
			<?php endif; ?>
		</div>

		<?php if ( $card['id'] ) : ?>
			<div class="moon-card__actions">
				<?php if ( $card['add_id'] ) : ?>
					<button
						type="button"
						class="moon-card__act moon-card__act--add"
						data-moon-add="<?php echo esc_attr( (string) $card['add_id'] ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'افزودن %s به سبد خرید', 'moon-theme' ), $card['name'] ) ); ?>"
					>
						<?php moon_the_icon( 'shopping-bag', 16 ); ?>
						<span><?php esc_html_e( 'افزودن به سبد', 'moon-theme' ); ?></span>
					</button>
				<?php elseif ( $sold_out ) : ?>
					<span class="moon-card__act moon-card__act--muted"><?php esc_html_e( 'تمام شد', 'moon-theme' ); ?></span>
				<?php else : ?>
					<a class="moon-card__act" href="<?php echo esc_url( $card_url ); ?>">
						<?php moon_the_icon( 'shopping-bag', 16 ); ?>
						<span><?php esc_html_e( 'انتخاب گزینه‌ها', 'moon-theme' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( $card_cfg['quickview'] ) : ?>
					<button
						type="button"
						class="moon-card__act moon-card__act--icon"
						data-moon-quick="<?php echo esc_attr( (string) $card['id'] ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'مشاهده سریع %s', 'moon-theme' ), $card['name'] ) ); ?>"
					>
						<?php moon_the_icon( 'eye', 16 ); ?>
					</button>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="moon-card__body">
		<?php if ( $card['category'] ) : ?>
			<p class="moon-card__cat"><?php echo esc_html( $card['category'] ); ?></p>
		<?php endif; ?>

		<h3 class="moon-card__name"><a href="<?php echo esc_url( $card_url ); ?>"><?php echo esc_html( $card['name'] ); ?></a></h3>

		<?php if ( $english ) : ?>
			<p class="moon-card__en" dir="ltr"><?php echo esc_html( $english ); ?></p>
		<?php endif; ?>

		<?php if ( $card_cfg['swatches'] && ! empty( $card['colors'] ) ) : ?>
			<div class="moon-card__colors" aria-label="<?php esc_attr_e( 'رنگ‌بندی', 'moon-theme' ); ?>">
				<?php foreach ( $card['colors'] as $index => $color ) : ?>
					<button
						type="button"
						class="moon-card__color<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-moon-card-color
						data-image="<?php echo esc_url( isset( $color['image'] ) ? $color['image'] : '' ); ?>"
						data-name="<?php echo esc_attr( isset( $color['name'] ) ? $color['name'] : '' ); ?>"
						title="<?php echo esc_attr( isset( $color['name'] ) ? $color['name'] : '' ); ?>"
						aria-label="<?php echo esc_attr( isset( $color['name'] ) ? $color['name'] : '' ); ?>"
					>
						<span class="moon-card__dot" style="--swatch:<?php echo esc_attr( isset( $color['hex'] ) ? $color['hex'] : '#c9c4bb' ); ?>"></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="moon-card__foot">
			<div class="moon-card__price"><?php echo wp_kses_post( $card['price'] ); ?></div>

			<?php if ( $card_cfg['rating'] && ! empty( $card['reviews'] ) ) : ?>
				<span class="moon-card__rating">
					<span class="moon-stars" style="--rating:<?php echo esc_attr( (string) ( (float) $card['rating'] / 5 * 100 ) ); ?>%" aria-hidden="true"></span>
					<span class="moon-card__rating-count"><?php echo esc_html( moon_fa_digits( number_format_i18n( (int) $card['reviews'] ) ) ); ?></span>
				</span>
			<?php endif; ?>
		</div>

		<?php if ( $stock_left > 0 && $stock_left <= 10 ) : ?>
			<p class="moon-card__stock">
				<?php
				printf(
					/* translators: %s: quantity left. */
					esc_html__( 'تنها %s عدد باقی مانده', 'moon-theme' ),
					esc_html( moon_fa_digits( (string) $stock_left ) )
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</article>
