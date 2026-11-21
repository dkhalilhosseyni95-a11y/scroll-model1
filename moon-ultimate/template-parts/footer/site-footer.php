<?php
/**
 * Site footer.
 *
 * The markup is produced by the footer builder (`Moon_Footer`), which reads the
 * columns and content from the MOON panel. The wrapper stays here so a child
 * theme can still override `template-parts/footer/site-footer.php`.
 *
 * @package Moon_Theme
 */

if ( class_exists( 'Moon_Footer' ) ) {
	Moon_Footer::render();
	return;
}

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
			<p class="moon-footer__brand<?php echo moon_is_arabic_script( moon_brand_text() ) ? ' is-fa' : ''; ?>"><?php echo esc_html( moon_brand_text() ); ?></p>
			<p class="moon-footer__about"><?php echo esc_html( moon_opt( 'footer_about' ) ); ?></p>
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

	<div class="moon-footer__bottom">
		<span><?php echo esc_html( moon_opt( 'footer_copyright' ) ); ?></span>
	</div>
</footer>
