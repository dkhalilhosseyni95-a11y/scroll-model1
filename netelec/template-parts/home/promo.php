<?php
/**
 * Promotional banner.
 *
 * @package Netelec
 */

$netelec_promo_image = (int) netelec_opt( 'promo_image_id' );
?>
<section class="section section--promo">
	<div class="container">
		<div class="promo">
			<div class="promo__content">
				<?php if ( netelec_opt( 'promo_badge' ) ) : ?>
					<span class="promo__badge"><?php echo esc_html( netelec_opt( 'promo_badge' ) ); ?></span>
				<?php endif; ?>
				<h2 class="promo__title"><?php echo esc_html( netelec_opt( 'promo_title' ) ); ?></h2>
				<p class="promo__text"><?php echo esc_html( netelec_opt( 'promo_text' ) ); ?></p>
				<?php if ( netelec_opt( 'promo_btn_text' ) ) : ?>
					<a class="btn btn--light" href="<?php echo esc_url( netelec_opt( 'promo_btn_url' ) ); ?>">
						<?php echo esc_html( netelec_opt( 'promo_btn_text' ) ); ?>
						<?php netelec_icon( 'arrow-left', 'nc-icon--sm' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<?php if ( $netelec_promo_image ) : ?>
				<div class="promo__media">
					<?php echo wp_get_attachment_image( $netelec_promo_image, 'large', false, array( 'alt' => netelec_opt( 'promo_title' ), 'loading' => 'lazy' ) ); ?>
				</div>
			<?php else : ?>
				<div class="promo__art" aria-hidden="true">
					<span class="promo__art-ring"></span>
					<?php netelec_icon( 'headset' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
