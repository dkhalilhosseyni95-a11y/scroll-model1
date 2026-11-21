<?php
/**
 * Interactive clothing rack.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

$moon_items = moon_rack_items_view();

if ( empty( $moon_items ) ) {
	return;
}

$moon_first = $moon_items[0];
?>
<section
	id="rack"
	class="moon-rack"
	data-moon-rack
	data-items="<?php echo esc_attr( wp_json_encode( $moon_items ) ); ?>"
>
	<div class="moon-rack__inner">
		<div class="moon-rack__head">
			<?php moon_section_title( moon_opt( 'rack_eyebrow' ), moon_opt( 'rack_title' ) ); ?>
			<p class="moon-rack__desc moon-reveal"><?php echo esc_html( moon_opt( 'rack_desc' ) ); ?></p>
		</div>

		<div class="moon-rack__stage" data-moon-rack-stage>
			<div class="moon-rack__rail">
				<div class="moon-rack__bar" aria-hidden="true"></div>
				<div class="moon-rack__post moon-rack__post--start" aria-hidden="true"></div>
				<div class="moon-rack__post moon-rack__post--end" aria-hidden="true"></div>

				<div class="moon-rack__items moon-noscroll" data-moon-rack-items>
					<?php foreach ( $moon_items as $index => $item ) : ?>
						<div class="moon-rack__slot">
							<button
								type="button"
								class="moon-rack__hanger"
								data-moon-hanger
								data-index="<?php echo esc_attr( (string) $index ); ?>"
								aria-label="<?php echo esc_attr( sprintf( __( 'برداشتن %s از رگال', 'moon-theme' ), $item['name'] ) ); ?>"
							>
								<img
									src="<?php echo esc_url( $item['image'] ); ?>"
									alt="<?php echo esc_attr( $item['name'] ); ?>"
									width="640"
									height="1024"
									loading="lazy"
									decoding="async"
								>
								<span class="moon-eyebrow moon-rack__en" dir="ltr"><?php echo esc_html( $item['en'] ); ?></span>
							</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="moon-rack__panel" data-moon-rack-panel aria-live="polite">
				<div class="moon-rack__figure">
					<span class="moon-rack__shadow" aria-hidden="true"></span>
					<img class="moon-rack__photo" data-moon-rack-photo src="<?php echo esc_url( $moon_first['image'] ); ?>" alt="" width="640" height="1024" loading="lazy" decoding="async">
				</div>

				<div class="moon-rack__card moon-glass">
					<p class="moon-eyebrow moon-rack__eyebrow"><span dir="ltr" data-moon-rack-en><?php echo esc_html( $moon_first['en'] ); ?></span></p>
					<h3 class="moon-rack__name" data-moon-rack-name><?php echo esc_html( $moon_first['name'] ); ?></h3>
					<div class="moon-rack__price" data-moon-rack-price><?php echo wp_kses_post( $moon_first['price'] ); ?></div>
					<p class="moon-rack__note" data-moon-rack-note <?php echo '' === $moon_first['note'] ? 'hidden' : ''; ?>><?php echo esc_html( $moon_first['note'] ); ?></p>

					<div class="moon-rack__colors">
						<p class="moon-rack__colors-label"><?php esc_html_e( 'رنگ‌های موجود', 'moon-theme' ); ?></p>
						<div class="moon-rack__swatches" data-moon-rack-swatches>
							<?php foreach ( $moon_first['colors'] as $color_index => $color ) : ?>
								<button
									type="button"
									class="moon-rack__swatch<?php echo 0 === $color_index ? ' is-active' : ''; ?>"
									style="background:<?php echo esc_attr( $color ); ?>"
									aria-label="<?php echo esc_attr( sprintf( __( 'رنگ %s', 'moon-theme' ), number_format_i18n( $color_index + 1 ) ) ); ?>"
								></button>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="moon-rack__actions">
						<a
							class="moon-btn moon-btn--dark"
							data-moon-rack-add
							href="<?php echo esc_url( $moon_first['addUrl'] ? $moon_first['addUrl'] : ( $moon_first['link'] ? $moon_first['link'] : moon_shop_url() ) ); ?>"
						><span data-moon-rack-add-label><?php esc_html_e( 'افزودن به سبد', 'moon-theme' ); ?></span> <?php moon_the_icon( 'arrow-left', 16 ); ?></a>

						<button type="button" class="moon-btn moon-btn--outline" data-moon-rack-return>
							<?php moon_the_icon( 'rotate-ccw', 15 ); ?> <?php esc_html_e( 'بازگرداندن به رگال', 'moon-theme' ); ?>
						</button>
					</div>

					<div class="moon-rack__thumbs" data-moon-rack-thumbs>
						<?php foreach ( $moon_items as $thumb_index => $thumb ) : ?>
							<?php if ( 0 === $thumb_index ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<button type="button" class="moon-rack__thumb" data-moon-rack-thumb="<?php echo esc_attr( (string) $thumb_index ); ?>" aria-label="<?php echo esc_attr( $thumb['name'] ); ?>">
								<img src="<?php echo esc_url( $thumb['image'] ); ?>" alt="" width="640" height="1024" loading="lazy" decoding="async">
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
