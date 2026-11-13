<?php
/**
 * Site footer.
 *
 * @package Moon_Theme
 */

$moon_cols = array(
	moon_footer_links( moon_opt( 'footer_col1' ) ),
	moon_footer_links( moon_opt( 'footer_col2' ) ),
	moon_footer_links( moon_opt( 'footer_col3' ) ),
);

$moon_col_titles = array(
	__( 'فروشگاه', 'moon-theme' ),
	__( 'خدمات', 'moon-theme' ),
	__( 'MOON', 'moon-theme' ),
);
?>
<footer class="moon-footer" id="footer">
	<div class="moon-footer__grid">
		<div>
			<p class="moon-footer__brand"><?php echo esc_html( moon_brand_text() ); ?></p>
			<p class="moon-footer__about"><?php echo esc_html( moon_opt( 'footer_about' ) ); ?></p>
			<div class="moon-footer__social">
				<?php if ( moon_opt( 'footer_instagram' ) ) : ?>
					<a href="<?php echo esc_url( moon_opt( 'footer_instagram' ) ); ?>" aria-label="<?php esc_attr_e( 'اینستاگرام', 'moon-theme' ); ?>" rel="noopener" target="_blank">
						<?php moon_the_icon( 'instagram', 16 ); ?>
					</a>
				<?php endif; ?>
				<?php if ( moon_opt( 'footer_telegram' ) ) : ?>
					<a href="<?php echo esc_url( moon_opt( 'footer_telegram' ) ); ?>" aria-label="<?php esc_attr_e( 'تلگرام', 'moon-theme' ); ?>" rel="noopener" target="_blank">
						<?php moon_the_icon( 'send', 16 ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<?php foreach ( $moon_cols as $index => $links ) : ?>
			<div>
				<p class="moon-footer__col-title"><?php echo esc_html( $moon_col_titles[ $index ] ); ?></p>
				<ul class="moon-footer__links">
					<?php foreach ( $links as $link ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="moon-footer__wordmark" aria-hidden="true" dir="ltr">MOON</div>

	<div class="moon-footer__bottom">
		<span><?php echo esc_html( moon_opt( 'footer_copyright' ) ); ?></span>
		<span dir="ltr"><?php echo esc_html( moon_opt( 'footer_cities' ) ); ?></span>
	</div>
</footer>
