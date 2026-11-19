<?php
/**
 * Home benefits strip.
 *
 * @package Netelec
 */

$netelec_benefits = netelec_benefits();
if ( ! $netelec_benefits ) {
	return;
}
?>
<section class="benefits">
	<div class="container">
		<ul class="benefits__list">
			<?php foreach ( $netelec_benefits as $netelec_benefit ) : ?>
				<li class="benefit">
					<span class="benefit__icon"><?php netelec_icon( $netelec_benefit['icon'] ); ?></span>
					<span class="benefit__text">
						<strong><?php echo esc_html( $netelec_benefit['title'] ); ?></strong>
						<small><?php echo esc_html( $netelec_benefit['desc'] ); ?></small>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
