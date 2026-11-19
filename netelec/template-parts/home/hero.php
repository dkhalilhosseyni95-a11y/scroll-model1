<?php
/**
 * Home hero.
 *
 * @package Netelec
 */

$netelec_points = array_filter( array_map( 'trim', explode( "\n", (string) netelec_opt( 'hero_points' ) ) ) );
$netelec_image  = (int) netelec_opt( 'hero_image_id' );
?>
<section class="hero">
	<div class="container hero__inner">
		<div class="hero__content">
			<?php if ( netelec_opt( 'hero_badge' ) ) : ?>
				<span class="hero__badge"><?php netelec_icon( 'zap', 'nc-icon--sm' ); ?><?php echo esc_html( netelec_opt( 'hero_badge' ) ); ?></span>
			<?php endif; ?>

			<h1 class="hero__title"><?php echo esc_html( netelec_opt( 'hero_title' ) ); ?></h1>
			<p class="hero__desc"><?php echo esc_html( netelec_opt( 'hero_desc' ) ); ?></p>

			<div class="hero__actions">
				<?php if ( netelec_opt( 'hero_btn_text' ) ) : ?>
					<a class="btn btn--primary btn--lg" href="<?php echo esc_url( netelec_opt( 'hero_btn_url' ) ); ?>">
						<?php echo esc_html( netelec_opt( 'hero_btn_text' ) ); ?>
						<?php netelec_icon( 'arrow-left', 'nc-icon--sm' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( netelec_opt( 'hero_btn2_text' ) ) : ?>
					<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( netelec_opt( 'hero_btn2_url' ) ); ?>">
						<?php echo esc_html( netelec_opt( 'hero_btn2_text' ) ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $netelec_points ) : ?>
				<ul class="hero__points">
					<?php foreach ( $netelec_points as $netelec_point ) : ?>
						<li><?php netelec_icon( 'check', 'nc-icon--sm' ); ?><?php echo esc_html( $netelec_point ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="hero__visual">
			<?php if ( $netelec_image ) : ?>
				<div class="hero__image">
					<?php echo wp_get_attachment_image( $netelec_image, 'netelec-hero', false, array( 'alt' => netelec_opt( 'hero_title' ) ) ); ?>
				</div>
			<?php else : ?>
				<div class="hero-art" aria-hidden="true">
					<span class="hero-art__ring hero-art__ring--1"></span>
					<span class="hero-art__ring hero-art__ring--2"></span>
					<div class="hero-art__core"><?php netelec_icon( 'package' ); ?></div>
					<span class="hero-art__node hero-art__node--1"><?php netelec_icon( 'zap', 'nc-icon--sm' ); ?></span>
					<span class="hero-art__node hero-art__node--2"><?php netelec_icon( 'shield', 'nc-icon--sm' ); ?></span>
					<span class="hero-art__node hero-art__node--3"><?php netelec_icon( 'grid', 'nc-icon--sm' ); ?></span>
				</div>

				<div class="hero-chip hero-chip--1">
					<span class="hero-chip__icon"><?php netelec_icon( 'shield' ); ?></span>
					<span><strong>ضمانت اصالت</strong><small>تأمین از منابع معتبر</small></span>
				</div>
				<div class="hero-chip hero-chip--2">
					<span class="hero-chip__icon"><?php netelec_icon( 'truck' ); ?></span>
					<span><strong>ارسال سریع</strong><small>بسته‌بندی ایمن</small></span>
				</div>
				<div class="hero-chip hero-chip--3">
					<span class="hero-chip__icon"><?php netelec_icon( 'headset' ); ?></span>
					<span><strong>مشاوره تخصصی</strong><small>انتخاب درست تجهیزات</small></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
