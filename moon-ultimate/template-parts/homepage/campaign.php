<?php
/**
 * Campaign banner with parallax image.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

$moon_url = moon_link( moon_opt( 'campaign_btn_url' ), moon_shop_url() );
?>
<section id="campaign" class="moon-campaign moon-grain" data-moon-campaign>
	<?php
	moon_image(
		'campaign_image_id',
		'campaign.jpg',
		'moon-wide',
		array(
			'class'  => 'moon-campaign__media',
			'alt'    => __( 'کمپین صحرای MOON', 'moon-theme' ),
			'width'  => 1920,
			'height' => 1088,
		)
	);
	?>
	<div class="moon-campaign__shade"></div>

	<div class="moon-campaign__inner">
		<div class="moon-reveal">
			<p class="moon-eyebrow moon-campaign__eyebrow"><span dir="ltr"><?php echo esc_html( moon_opt( 'campaign_eyebrow' ) ); ?></span></p>
			<h2 class="moon-campaign__title" dir="ltr"><?php echo esc_html( moon_opt( 'campaign_title' ) ); ?></h2>
			<p class="moon-campaign__text"><?php echo esc_html( moon_opt( 'campaign_text' ) ); ?></p>
			<div class="moon-campaign__cta">
				<a class="moon-btn moon-btn--ghost" href="<?php echo esc_url( $moon_url ); ?>"><?php echo esc_html( moon_opt( 'campaign_btn_text' ) ); ?></a>
			</div>
		</div>
	</div>
</section>
