<?php
/**
 * Quick view content, loaded by AJAX from the product cards.
 *
 * Kept close to the single product summary so the same variation form, swatches
 * and AJAX add-to-cart work inside the dialog.
 *
 * @var WC_Product $product Product.
 * @var array      $data    Card data.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! $product instanceof WC_Product ) {
	return;
}

$gallery  = class_exists( 'Moon_Colors' ) ? Moon_Colors::gallery( $product ) : array();
$fallback = wc_placeholder_img_src( 'large' );
$images   = $gallery ? $gallery : array( $fallback );
$rating   = (float) $product->get_average_rating();
$reviews  = (int) $product->get_review_count();
?>
<div class="moon-qv__panel" role="dialog" aria-modal="true" aria-labelledby="moon-qv-title">
	<button type="button" class="moon-qv__close" data-moon-close aria-label="<?php esc_attr_e( 'بستن', 'moon-theme' ); ?>">
		<?php echo function_exists( 'moon_icon' ) ? moon_icon( 'x', 18 ) : '×'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>

	<div class="moon-qv__media moon-noscroll" data-moon-qv-gallery>
		<div class="moon-qv__stage">
			<?php foreach ( $images as $index => $url ) : ?>
				<img
					class="moon-qv__img<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-moon-qv-img="<?php echo esc_attr( (string) $index ); ?>"
					src="<?php echo esc_url( $url ); ?>"
					alt="<?php echo esc_attr( $product->get_name() ); ?>"
					loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
					decoding="async"
				>
			<?php endforeach; ?>

			<?php if ( $data['tag'] ) : ?>
				<span class="moon-qv__badge"><?php echo esc_html( $data['tag'] ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( count( $images ) > 1 ) : ?>
			<div class="moon-qv__thumbs">
				<?php foreach ( $images as $index => $url ) : ?>
					<button type="button" class="moon-qv__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-qv-thumb="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: image number. */ __( 'تصویر %s', 'moon-theme' ), Moon_Shop::digits( (string) ( $index + 1 ) ) ) ); ?>">
						<img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy" decoding="async">
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="moon-qv__body">
		<?php if ( $data['category'] ) : ?>
			<p class="moon-eyebrow moon-qv__eyebrow"><?php echo esc_html( $data['category'] ); ?></p>
		<?php endif; ?>

		<h2 class="moon-qv__title" id="moon-qv-title"><?php echo esc_html( $product->get_name() ); ?></h2>
		<p class="moon-qv__en" dir="ltr"><?php echo esc_html( $data['en'] ); ?></p>

		<div class="moon-qv__rating">
			<?php if ( $reviews ) : ?>
				<span class="moon-stars" style="--rating:<?php echo esc_attr( (string) ( $rating / 5 * 100 ) ); ?>%" aria-hidden="true"></span>
				<span class="moon-qv__rating-text">
					<?php
					echo esc_html( sprintf(
						/* translators: 1: rating, 2: review count. */
						__( '%1$s از ۵ — %2$s نظر', 'moon-theme' ),
						Moon_Shop::digits( number_format_i18n( $rating, 1 ) ),
						Moon_Shop::digits( number_format_i18n( $reviews ) )
					) );
					?>
				</span>
			<?php else : ?>
				<span class="moon-qv__rating-text"><?php esc_html_e( 'اولین نظر را شما ثبت کنید', 'moon-theme' ); ?></span>
			<?php endif; ?>
		</div>

		<div class="moon-qv__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

		<?php if ( $product->get_short_description() ) : ?>
			<p class="moon-qv__desc"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></p>
		<?php endif; ?>

		<div class="moon-qv__form" data-moon-ajax-cart>
			<?php woocommerce_template_single_add_to_cart(); ?>
		</div>

		<ul class="moon-qv__meta">
			<li>
				<?php echo function_exists( 'moon_icon' ) ? moon_icon( 'truck', 16 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'ارسال ۲۴ تا ۷۲ ساعته', 'moon-theme' ); ?></span>
			</li>
			<li>
				<?php echo function_exists( 'moon_icon' ) ? moon_icon( 'rotate-ccw', 16 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( '۷ روز مهلت بازگشت', 'moon-theme' ); ?></span>
			</li>
			<li>
				<button type="button" class="moon-linkbtn" data-moon-sizeguide-open><?php esc_html_e( 'راهنمای سایز', 'moon-theme' ); ?></button>
			</li>
		</ul>

		<a class="moon-qv__more" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
			<?php esc_html_e( 'مشاهده صفحه کامل محصول', 'moon-theme' ); ?>
			<?php echo function_exists( 'moon_icon' ) ? moon_icon( 'arrow-left', 16 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</div>
