<?php
/**
 * Site footer.
 *
 * @package Netelec
 */

$netelec_socials = array(
	'instagram' => netelec_opt( 'social_instagram' ),
	'telegram'  => netelec_opt( 'social_telegram' ),
	'whatsapp'  => netelec_opt( 'social_whatsapp' ),
	'linkedin'  => netelec_opt( 'social_linkedin' ),
);
?>
</main>

<footer class="site-footer">
	<div class="container">
		<div class="footer-grid">
			<div class="footer-brand">
				<a class="site-brand site-brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php if ( netelec_opt( 'logo_id' ) ) : ?>
						<?php echo wp_get_attachment_image( (int) netelec_opt( 'logo_id' ), 'full', false, array( 'class' => 'site-brand__logo', 'alt' => netelec_opt( 'brand_name' ) ) ); ?>
					<?php else : ?>
						<span class="site-brand__mark" aria-hidden="true">N</span>
						<span class="site-brand__text"><strong><?php echo esc_html( netelec_opt( 'brand_name' ) ); ?></strong></span>
					<?php endif; ?>
				</a>
				<p class="footer-brand__about"><?php echo esc_html( netelec_opt( 'footer_about' ) ); ?></p>

				<?php if ( array_filter( $netelec_socials ) ) : ?>
					<ul class="footer-social" aria-label="شبکه‌های اجتماعی">
						<?php foreach ( $netelec_socials as $netelec_network => $netelec_url ) : ?>
							<?php if ( ! $netelec_url ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<li>
								<a href="<?php echo esc_url( $netelec_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $netelec_network ); ?>">
									<?php netelec_icon( $netelec_network ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<h2 class="footer-col__title">دسترسی سریع</h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'depth'          => 1,
						'fallback_cb'    => 'netelec_footer_fallback_menu',
					)
				);
				?>
			</div>

			<div class="footer-col">
				<h2 class="footer-col__title">دسته‌بندی محصولات</h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_cat',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'depth'          => 1,
						'fallback_cb'    => 'netelec_footer_categories',
					)
				);
				?>
			</div>

			<div class="footer-col footer-col--contact">
				<h2 class="footer-col__title">ارتباط با ما</h2>
				<ul class="footer-contact">
					<?php if ( netelec_opt( 'footer_phone' ) ) : ?>
						<li><?php netelec_icon( 'phone', 'nc-icon--sm' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', netelec_opt( 'footer_phone' ) ) ); ?>"><?php echo esc_html( netelec_opt( 'footer_phone' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( netelec_opt( 'footer_email' ) ) : ?>
						<li><?php netelec_icon( 'mail', 'nc-icon--sm' ); ?><a href="mailto:<?php echo esc_attr( netelec_opt( 'footer_email' ) ); ?>"><?php echo esc_html( netelec_opt( 'footer_email' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( netelec_opt( 'footer_address' ) ) : ?>
						<li><?php netelec_icon( 'pin', 'nc-icon--sm' ); ?><span><?php echo esc_html( netelec_opt( 'footer_address' ) ); ?></span></li>
					<?php endif; ?>
					<?php if ( netelec_opt( 'footer_hours' ) ) : ?>
						<li><?php netelec_icon( 'clock', 'nc-icon--sm' ); ?><span><?php echo esc_html( netelec_opt( 'footer_hours' ) ); ?></span></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<?php
		$netelec_badges = array_filter( array_map( 'trim', explode( "\n", (string) netelec_opt( 'footer_badges' ) ) ) );
		if ( $netelec_badges ) :
			?>
			<div class="footer-badges">
				<?php foreach ( $netelec_badges as $netelec_badge ) : ?>
					<span class="footer-badge"><?php netelec_icon( 'check', 'nc-icon--sm' ); ?><?php echo esc_html( $netelec_badge ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="footer-bottom">
			<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( netelec_opt( 'brand_name' ) ); ?> — <?php echo esc_html( netelec_opt( 'copyright' ) ); ?></p>
			<div class="footer-bottom__links">
				<?php if ( netelec_page_exists( 'terms' ) ) : ?>
					<a href="<?php echo esc_url( netelec_page_url( 'terms' ) ); ?>">قوانین و مقررات</a>
				<?php endif; ?>
				<?php if ( netelec_page_exists( 'privacy' ) ) : ?>
					<a href="<?php echo esc_url( netelec_page_url( 'privacy' ) ); ?>">حریم خصوصی</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>

<?php if ( netelec_opt( 'mobile_floating_buttons' ) ) : ?>
	<div class="floating-actions">
		<?php if ( netelec_opt( 'social_whatsapp' ) ) : ?>
			<a class="floating-btn floating-btn--whatsapp" href="<?php echo esc_url( netelec_opt( 'social_whatsapp' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="واتساپ">
				<?php netelec_icon( 'whatsapp' ); ?>
			</a>
		<?php endif; ?>
		<button class="floating-btn floating-btn--top" type="button" aria-label="بازگشت به بالا" hidden>
			<?php netelec_icon( 'arrow-up' ); ?>
		</button>
	</div>
<?php endif; ?>

<div class="nc-toast" role="status" aria-live="polite" hidden></div>

<div class="quick-view-modal" id="quick-view" hidden>
	<div class="quick-view-modal__dialog" role="dialog" aria-modal="true" aria-label="مشاهده سریع محصول">
		<button class="quick-view-modal__close" type="button" aria-label="بستن"><?php netelec_icon( 'close' ); ?></button>
		<div class="quick-view-modal__body"></div>
	</div>
</div>

<?php netelec_icon_sprite(); ?>
<?php wp_footer(); ?>
</body>
</html>
