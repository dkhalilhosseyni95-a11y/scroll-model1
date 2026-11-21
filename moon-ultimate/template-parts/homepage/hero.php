<?php
/**
 * Hero section: full-height parallax image with staggered entrance.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

$moon_btn1 = moon_link( moon_opt( 'hero_btn1_url' ), moon_product_cat_url( 'women' ) );
$moon_btn2 = moon_link( moon_opt( 'hero_btn2_url' ), moon_product_cat_url( 'men' ) );
?>
<section id="top" class="moon-hero moon-grain" data-moon-hero>
	<?php
	moon_image(
		'hero_image_id',
		'hero.jpg',
		'moon-wide',
		array(
			'class'         => 'moon-hero__media',
			'alt'           => __( 'مدل با پالتو مشکی کالکشن جدید MOON', 'moon-theme' ),
			'width'         => 1920,
			'height'        => 1088,
			'loading'       => 'eager',
			'fetchpriority' => 'high',
		)
	);
	?>
	<div class="moon-hero__shade"></div>
	<div class="moon-hero__fade"></div>

	<div class="moon-hero__inner">
		<div class="moon-hero__copy">
			<p class="moon-eyebrow moon-hero__eyebrow"><span dir="ltr"><?php echo esc_html( moon_opt( 'hero_eyebrow' ) ); ?></span></p>

			<h1 class="moon-hero__title">
				<span class="moon-hero__line"><span><?php echo esc_html( moon_opt( 'hero_title_1' ) ); ?></span></span>
				<span class="moon-hero__line"><span><?php echo esc_html( moon_opt( 'hero_title_2' ) ); ?></span></span>
			</h1>

			<p class="moon-hero__desc"><?php echo esc_html( moon_opt( 'hero_desc' ) ); ?></p>

			<div class="moon-hero__actions">
				<a class="moon-btn moon-btn--solid" href="<?php echo esc_url( $moon_btn1 ); ?>"><?php echo esc_html( moon_opt( 'hero_btn1_text' ) ); ?></a>
				<a class="moon-btn moon-btn--ghost" href="<?php echo esc_url( $moon_btn2 ); ?>"><?php echo esc_html( moon_opt( 'hero_btn2_text' ) ); ?></a>
			</div>
		</div>
	</div>

	<div class="moon-hero__word" aria-hidden="true" dir="ltr">MOON</div>

	<div class="moon-hero__scroll" aria-hidden="true">
		<span class="moon-eyebrow moon-hero__scroll-label" dir="ltr">Scroll</span>
		<span class="moon-hero__scroll-line"><span></span></span>
	</div>
</section>
