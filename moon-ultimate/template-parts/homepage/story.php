<?php
/**
 * Brand story with service benefits.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

$moon_benefits = array();

for ( $i = 1; $i <= 4; $i++ ) {
	$moon_benefits[] = array(
		'icon'  => moon_opt( 'benefit' . $i . '_icon' ),
		'title' => moon_opt( 'benefit' . $i . '_title' ),
		'desc'  => moon_opt( 'benefit' . $i . '_desc' ),
	);
}
?>
<section id="story" class="moon-story">
	<div class="moon-story__grid">
		<div class="moon-story__figure moon-reveal">
			<?php
			moon_image(
				'story_image_id',
				'atelier.jpg',
				'moon-card',
				array(
					'class'  => 'moon-story__img',
					'alt'    => __( 'خیاط در آتلیه MOON', 'moon-theme' ),
					'width'  => 896,
					'height' => 1152,
				)
			);
			?>
			<span class="moon-story__badge">
				<span class="moon-story__year" dir="ltr"><?php echo esc_html( moon_opt( 'story_year' ) ); ?></span>
				<span class="moon-story__year-note"><?php echo esc_html( moon_opt( 'story_year_note' ) ); ?></span>
			</span>
		</div>

		<div class="moon-reveal" style="transition-delay:.15s">
			<p class="moon-eyebrow moon-section-title__eyebrow"><span dir="ltr"><?php echo esc_html( moon_opt( 'story_eyebrow' ) ); ?></span></p>
			<h2 class="moon-story__title">
				<?php echo esc_html( moon_opt( 'story_title' ) ); ?><br>
				<?php echo esc_html( moon_opt( 'story_title_2' ) ); ?>
			</h2>
			<p class="moon-story__text"><?php echo esc_html( moon_opt( 'story_text' ) ); ?></p>
		</div>
	</div>

	<div class="moon-benefits">
		<?php foreach ( $moon_benefits as $index => $benefit ) : ?>
			<div class="moon-benefits__item moon-reveal" style="transition-delay:<?php echo esc_attr( $index * 0.08 ); ?>s">
				<span class="moon-benefits__icon"><?php moon_the_icon( $benefit['icon'], 26 ); ?></span>
				<h3 class="moon-benefits__title"><?php echo esc_html( $benefit['title'] ); ?></h3>
				<p class="moon-benefits__desc"><?php echo esc_html( $benefit['desc'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>
