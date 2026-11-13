<?php
/**
 * Product card, shared by the homepage carousel and the shop loop.
 *
 * @param array $args Card data: id, name, en, price, image, url, tag, contain, sizes, add_url, desc.
 *
 * @package Moon_Theme
 */

$card = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'id'      => 0,
		'name'    => '',
		'en'      => '',
		'price'   => '',
		'image'   => '',
		'url'     => '',
		'tag'     => '',
		'contain' => false,
		'sizes'   => array(),
		'add_url' => '',
		'add_id'  => 0,
		'desc'    => __( 'دوخته‌شده با دقت در جزئیات، از پارچه‌های منتخب. سایزبندی استاندارد اروپایی.', 'moon-theme' ),
	)
);

$card_url  = $card['url'] ? $card['url'] : moon_shop_url();
$quickview = array(
	'name'   => $card['name'],
	'en'     => $card['en'],
	'price'  => $card['price'],
	'image'  => $card['image'],
	'url'    => $card_url,
	'addUrl' => $card['add_url'],
	'addId'  => (int) $card['add_id'],
	'desc'   => $card['desc'],
	'sizes'  => array_values( array_map( 'strval', (array) $card['sizes'] ) ),
);
?>
<article class="moon-card" data-moon-card>
	<div class="moon-card__media<?php echo $card['contain'] ? ' moon-card__media--contain' : ''; ?>">
		<a href="<?php echo esc_url( $card_url ); ?>" tabindex="-1" aria-hidden="true">
			<img
				src="<?php echo esc_url( $card['image'] ); ?>"
				alt=""
				width="900"
				height="1200"
				loading="lazy"
				decoding="async"
			>
		</a>

		<?php if ( $card['tag'] ) : ?>
			<span class="moon-card__tag"><?php echo esc_html( $card['tag'] ); ?></span>
		<?php endif; ?>

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

		<button
			type="button"
			class="moon-card__quick"
			data-moon-quick
			aria-label="<?php echo esc_attr( sprintf( __( 'مشاهده سریع %s', 'moon-theme' ), $card['name'] ) ); ?>"
		>
			<?php moon_the_icon( 'eye', 15 ); ?> <?php esc_html_e( 'مشاهده سریع', 'moon-theme' ); ?>
		</button>

		<script type="application/json" data-moon-qv-data><?php echo wp_json_encode( $quickview, JSON_UNESCAPED_UNICODE ); ?></script>
	</div>

	<div class="moon-card__meta">
		<div>
			<h3 class="moon-card__name"><a href="<?php echo esc_url( $card_url ); ?>"><?php echo esc_html( $card['name'] ); ?></a></h3>
			<?php if ( $card['en'] ) : ?>
				<p class="moon-card__en" dir="ltr"><?php echo esc_html( $card['en'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $card['price'] ) : ?>
			<div class="moon-card__price"><?php echo wp_kses_post( $card['price'] ); ?></div>
		<?php endif; ?>
	</div>
</article>
