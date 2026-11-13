<?php
/**
 * Categories section: two collection cards.
 *
 * @package Moon_Theme
 */

$moon_cats = array(
	array(
		'en'    => moon_opt( 'cat1_en' ),
		'label' => moon_opt( 'cat1_label' ),
		'note'  => moon_opt( 'cat1_note' ),
		'image' => 'cat1_image_id',
		'file'  => 'women.jpg',
		'url'   => moon_link( moon_opt( 'cat1_url' ), moon_product_cat_url( 'women' ) ),
	),
	array(
		'en'    => moon_opt( 'cat2_en' ),
		'label' => moon_opt( 'cat2_label' ),
		'note'  => moon_opt( 'cat2_note' ),
		'image' => 'cat2_image_id',
		'file'  => 'men.jpg',
		'url'   => moon_link( moon_opt( 'cat2_url' ), moon_product_cat_url( 'men' ) ),
	),
);
?>
<section id="categories" class="moon-cats">
	<?php foreach ( $moon_cats as $index => $cat ) : ?>
		<div class="moon-cats__item moon-reveal" style="transition-delay: <?php echo esc_attr( $index * 0.15 ); ?>s;">
			<a class="moon-cats__card" href="<?php echo esc_url( $cat['url'] ); ?>">
				<?php
				moon_image(
					$cat['image'],
					$cat['file'],
					'moon-card',
					array(
						'alt'    => $cat['label'],
						'width'  => 896,
						'height' => 1200,
					)
				);
				?>
				<span class="moon-cats__shade" aria-hidden="true"></span>
				<span class="moon-cats__issue">
					<span class="moon-eyebrow" dir="ltr"><?php echo esc_html( 'Issue 0' . ( $index + 1 ) ); ?></span>
					<span class="moon-eyebrow" dir="ltr">AW26</span>
				</span>
				<span class="moon-cats__foot">
					<span>
						<span class="moon-cats__word" dir="ltr"><?php echo esc_html( $cat['en'] ); ?></span>
						<span class="moon-cats__meta"><?php echo esc_html( $cat['label'] . ' — ' . $cat['note'] ); ?></span>
					</span>
					<span class="moon-cats__arrow" aria-hidden="true"><?php moon_the_icon( 'arrow-up-left', 18 ); ?></span>
				</span>
			</a>
		</div>
	<?php endforeach; ?>
</section>
