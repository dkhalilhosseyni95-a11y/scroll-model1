<?php
/**
 * A single style / fitting-room look.
 *
 * Shows the model shot and every piece the look is built from, with each
 * product's colourways and a one-click "add the whole look".
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$look_id    = get_the_ID();
	$look_en    = (string) get_post_meta( $look_id, '_moon_en', true );
	$look_desc  = (string) get_post_meta( $look_id, '_moon_look_desc', true );
	$product_ids = (array) get_post_meta( $look_id, '_moon_products', true );
	$product_ids = array_values( array_filter( array_map( 'absint', $product_ids ) ) );

	if ( empty( $product_ids ) ) {
		$single = (int) get_post_meta( $look_id, '_moon_product', true );

		if ( $single ) {
			$product_ids = array( $single );
		}
	}

	if ( ! $look_desc ) {
		$look_desc = get_the_excerpt();
	}

	$hero = get_the_post_thumbnail_url( $look_id, 'full' );

	if ( ! $hero && $product_ids && function_exists( 'wc_get_product' ) ) {
		$first = wc_get_product( $product_ids[0] );
		$hero  = $first ? wp_get_attachment_image_url( $first->get_image_id(), 'full' ) : '';
	}

	$bundle = array();
	?>
	<article class="moon-look">
		<div class="moon-look__hero">
			<div class="moon-look__media">
				<?php if ( $hero ) : ?>
					<img src="<?php echo esc_url( $hero ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" width="900" height="1200" decoding="async">
				<?php endif; ?>
			</div>

			<div class="moon-look__intro">
				<p class="moon-eyebrow"><span dir="ltr"><?php esc_html_e( 'The fitting room', 'moon-theme' ); ?></span></p>
				<h1 class="moon-look__title"><?php the_title(); ?></h1>

				<?php if ( $look_en ) : ?>
					<p class="moon-look__en" dir="ltr"><?php echo esc_html( $look_en ); ?></p>
				<?php endif; ?>

				<?php if ( $look_desc ) : ?>
					<p class="moon-look__desc"><?php echo esc_html( $look_desc ); ?></p>
				<?php endif; ?>

				<div class="moon-look__actions">
					<button type="button" class="moon-btn moon-btn--dark" data-moon-look-add="" data-look-bundle>
						<?php esc_html_e( 'افزودن کل استایل به سبد', 'moon-theme' ); ?>
					</button>
					<a class="moon-btn" href="<?php echo esc_url( moon_shop_url() ); ?>">
						<?php esc_html_e( 'بازگشت به فروشگاه', 'moon-theme' ); ?>
					</a>
				</div>
			</div>
		</div>

		<?php if ( $product_ids ) : ?>
			<h2 class="moon-look__title" style="font-size:clamp(1.4rem,3vw,1.9rem);margin-bottom:1.5rem;"><?php esc_html_e( 'اقلام این استایل', 'moon-theme' ); ?></h2>

			<ul class="moon-look__pieces">
				<?php foreach ( $product_ids as $product_id ) : ?>
					<?php
					$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;

					if ( ! $product || ! $product->is_visible() ) {
						continue;
					}

					$colors     = class_exists( 'Moon_Colors' ) ? Moon_Colors::product_colors( $product ) : array();
					$image      = wp_get_attachment_image_url( $product->get_image_id(), 'moon-card' );
					$variations = array();
					$default    = 0;
					$default_color = '';

					if ( $product->is_type( 'variable' ) ) {
						foreach ( $product->get_available_variations() as $variation ) {
							$color = isset( $variation['attributes']['attribute_pa_color'] ) ? $variation['attributes']['attribute_pa_color'] : '';

							if ( $color && ! isset( $variations[ $color ] ) ) {
								$variations[ $color ] = (int) $variation['variation_id'];

								if ( ! $default && $variation['is_in_stock'] ) {
									$default       = (int) $variation['variation_id'];
									$default_color = $color;
								}
							} elseif ( $color && $variation['is_in_stock'] && isset( $variations[ $color ] ) && ! $default ) {
								$default       = (int) $variation['variation_id'];
								$default_color = $color;
							}
						}
					} else {
						$default = $product->get_id();
					}

					$bundle[] = array(
						'id'        => $product->get_id(),
						'variation' => (int) $default,
					);
					?>
					<li class="moon-look__piece" data-moon-look-piece>
						<div class="moon-look__piece-media">
							<img src="<?php echo esc_url( $image ? $image : wc_placeholder_img_src( 'moon-card' ) ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" data-moon-look-img width="600" height="800" loading="lazy" decoding="async">
						</div>

						<div class="moon-look__piece-body">
							<p class="moon-look__piece-cat"><?php echo esc_html( class_exists( 'Moon_Shop' ) ? Moon_Shop::primary_category( $product ) : '' ); ?></p>
							<h3 class="moon-look__piece-name"><a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
							<div class="moon-look__piece-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

							<?php if ( $colors ) : ?>
								<div class="moon-look__colors">
									<?php foreach ( $colors as $color ) : ?>
										<button
											type="button"
											class="moon-look__color<?php echo $color['slug'] === $default_color || ( ! $default_color && $color === reset( $colors ) ) ? ' is-active' : ''; ?>"
											data-moon-look-color
											data-image="<?php echo esc_url( $color['image'] ); ?>"
											data-variation="<?php echo esc_attr( (string) ( isset( $variations[ $color['slug'] ] ) ? $variations[ $color['slug'] ] : 0 ) ); ?>"
											title="<?php echo esc_attr( $color['name'] ); ?>"
											aria-label="<?php echo esc_attr( $color['name'] ); ?>"
										>
											<span style="--swatch:<?php echo esc_attr( $color['hex'] ); ?>"></span>
										</button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<div class="moon-look__piece-foot">
								<button type="button" class="moon-look__piece-add" data-moon-look-piece-add data-product="<?php echo esc_attr( (string) $product->get_id() ); ?>" data-variation="<?php echo esc_attr( (string) ( $default_color && isset( $variations[ $default_color ] ) ? $variations[ $default_color ] : 0 ) ); ?>">
									<?php echo esc_html( $product->is_in_stock() ? __( 'افزودن به سبد', 'moon-theme' ) : __( 'ناموجود', 'moon-theme' ) ); ?>
								</button>
								<a class="moon-look__piece-link" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" aria-label="<?php esc_attr_e( 'مشاهده محصول', 'moon-theme' ); ?>">
									<?php moon_the_icon( 'arrow-left', 15 ); ?>
								</a>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<script type="application/json" data-look-bundle-data><?php echo wp_json_encode( $bundle, JSON_UNESCAPED_UNICODE ); ?></script>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
