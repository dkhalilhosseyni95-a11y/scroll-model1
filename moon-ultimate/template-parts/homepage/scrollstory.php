<?php
/**
 * Scroll-linked brand story: three scenes cross-fading inside a sticky frame.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

$moon_scenes = array(
	array(
		'eyebrow' => moon_opt( 'scene1_eyebrow' ),
		'text'    => moon_opt( 'scene1_text' ),
		'option'  => 'scene1_image_id',
		'file'    => 'atelier.jpg',
	),
	array(
		'eyebrow' => moon_opt( 'scene2_eyebrow' ),
		'text'    => moon_opt( 'scene2_text' ),
		'option'  => 'scene2_image_id',
		'file'    => 'men.jpg',
	),
	array(
		'eyebrow' => moon_opt( 'scene3_eyebrow' ),
		'text'    => moon_opt( 'scene3_text' ),
		'option'  => 'scene3_image_id',
		'file'    => 'hero.jpg',
	),
);
?>
<section id="chapters" class="moon-scrollstory" data-moon-scrollstory>
	<div class="moon-scrollstory__sticky">
		<?php foreach ( $moon_scenes as $index => $scene ) : ?>
			<div class="moon-scrollstory__scene" data-moon-scene="<?php echo esc_attr( (string) $index ); ?>">
				<?php
				moon_image(
					$scene['option'],
					$scene['file'],
					'moon-wide',
					array(
						'class' => 'moon-scrollstory__img',
						'alt'   => '',
					)
				);
				?>
				<div class="moon-scrollstory__shade"></div>
				<div class="moon-scrollstory__copy" data-moon-scene-copy>
					<p class="moon-eyebrow moon-scrollstory__eyebrow"><span dir="ltr"><?php echo esc_html( $scene['eyebrow'] ); ?></span></p>
					<p class="moon-scrollstory__text"><?php echo esc_html( $scene['text'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>

		<p class="moon-scrollstory__word" data-moon-scene-title aria-hidden="true" dir="ltr">The Moon Campaign</p>

		<div class="moon-scrollstory__progress" aria-hidden="true">
			<div class="moon-scrollstory__bar" data-moon-scene-bar></div>
		</div>
	</div>
</section>
