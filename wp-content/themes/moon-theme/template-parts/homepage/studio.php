<?php
/**
 * Style studio: swap outfits on a fixed model frame.
 *
 * @package Moon_Theme
 */

$moon_outfits = moon_outfits_view();

if ( empty( $moon_outfits ) ) {
	return;
}

$moon_total = count( $moon_outfits );
$moon_split = min( 3, max( 1, $moon_total - 1 ) );
?>
<section
	id="studio"
	class="moon-studio"
	data-moon-studio
	data-outfits="<?php echo esc_attr( wp_json_encode( $moon_outfits ) ); ?>"
>
	<div class="moon-studio__inner">
		<?php moon_section_title( moon_opt( 'studio_eyebrow' ), moon_opt( 'studio_title' ), 'moon-reveal' ); ?>

		<div class="moon-studio__grid">
			<div class="moon-studio__side" data-moon-studio-side="start">
				<?php for ( $i = 0; $i < $moon_split; $i++ ) : ?>
					<button type="button" class="moon-studio__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-moon-studio-thumb="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( $moon_outfits[ $i ]['name'] ); ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>">
						<img src="<?php echo esc_url( $moon_outfits[ $i ]['image'] ); ?>" alt="" width="192" height="256" loading="lazy" decoding="async">
					</button>
				<?php endfor; ?>
			</div>

			<div class="moon-studio__main">
				<div class="moon-studio__frame">
					<?php foreach ( $moon_outfits as $index => $outfit ) : ?>
						<img
							class="moon-studio__img<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-moon-studio-img="<?php echo esc_attr( (string) $index ); ?>"
							src="<?php echo esc_url( $outfit['image'] ); ?>"
							alt="<?php echo esc_attr( sprintf( __( 'مدل با %s', 'moon-theme' ), $outfit['name'] ) ); ?>"
							width="768"
							height="1152"
							loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
							decoding="async"
						>
					<?php endforeach; ?>

					<div class="moon-studio__caption" data-moon-studio-caption>
						<p class="moon-eyebrow moon-studio__look"><span dir="ltr" data-moon-studio-look><?php echo esc_html( 'Look 01 — ' . $moon_outfits[0]['en'] ); ?></span></p>
						<p class="moon-studio__look-name" data-moon-studio-name><?php echo esc_html( $moon_outfits[0]['name'] ); ?></p>
					</div>
				</div>

				<button type="button" class="moon-studio__nav moon-studio__nav--prev" data-moon-studio-prev aria-label="<?php esc_attr_e( 'استایل قبلی', 'moon-theme' ); ?>">
					<?php moon_the_icon( 'chevron-right', 18 ); ?>
				</button>
				<button type="button" class="moon-studio__nav moon-studio__nav--next" data-moon-studio-next aria-label="<?php esc_attr_e( 'استایل بعدی', 'moon-theme' ); ?>">
					<?php moon_the_icon( 'chevron-left', 18 ); ?>
				</button>
			</div>

			<div class="moon-studio__side" data-moon-studio-side="end">
				<?php for ( $i = $moon_split; $i < $moon_total; $i++ ) : ?>
					<button type="button" class="moon-studio__thumb" data-moon-studio-thumb="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( $moon_outfits[ $i ]['name'] ); ?>" aria-pressed="false">
						<img src="<?php echo esc_url( $moon_outfits[ $i ]['image'] ); ?>" alt="" width="192" height="256" loading="lazy" decoding="async">
					</button>
				<?php endfor; ?>
			</div>
		</div>

		<div class="moon-studio__mobile moon-noscroll">
			<?php foreach ( $moon_outfits as $index => $outfit ) : ?>
				<button type="button" class="moon-studio__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-studio-thumb="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( $outfit['name'] ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>">
					<img src="<?php echo esc_url( $outfit['image'] ); ?>" alt="" width="192" height="256" loading="lazy" decoding="async">
				</button>
			<?php endforeach; ?>
		</div>

		<div class="moon-studio__foot">
			<div class="moon-studio__palette">
				<span><?php esc_html_e( 'پالت رنگ', 'moon-theme' ); ?></span>
				<span data-moon-studio-palette style="display:flex;gap:.5rem;">
					<?php foreach ( $moon_outfits[0]['colors'] as $color ) : ?>
						<span class="moon-studio__dot" style="background:<?php echo esc_attr( $color ); ?>"></span>
					<?php endforeach; ?>
				</span>
				<span class="moon-eyebrow moon-studio__counter" dir="ltr" data-moon-studio-counter><?php echo esc_html( '1 / ' . $moon_total ); ?></span>
			</div>

			<a class="moon-btn moon-btn--dark moon-magnetic" data-moon-studio-cta href="<?php echo esc_url( $moon_outfits[0]['link'] ); ?>">
				<?php echo esc_html( moon_opt( 'studio_cta' ) ); ?> <?php moon_the_icon( 'arrow-left', 16 ); ?>
			</a>
		</div>
	</div>
</section>
