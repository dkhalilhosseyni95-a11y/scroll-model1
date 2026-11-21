<?php
/**
 * Template Name: صفحه قوانین / حریم خصوصی
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	list( $moon_toc, $moon_body ) = moon_legal_toc( apply_filters( 'the_content', get_the_content() ) );
	?>
	<article <?php post_class( 'moon-page moon-legal' ); ?>>
		<?php moon_page_header( sprintf( /* translators: %s: date. */ __( 'آخرین به‌روزرسانی: %s', 'moon-theme' ), moon_fa_digits( get_the_modified_date() ) ) ); ?>

		<div class="moon-legal__wrap">
			<?php if ( $moon_toc ) : ?>
				<aside class="moon-legal__toc" aria-label="<?php esc_attr_e( 'فهرست مطالب', 'moon-theme' ); ?>">
					<p class="moon-legal__toc-title"><?php esc_html_e( 'فهرست مطالب', 'moon-theme' ); ?></p>
					<?php echo wp_kses_post( $moon_toc ); ?>
				</aside>
			<?php endif; ?>
			<div class="moon-legal__body moon-content"><?php echo wp_kses_post( $moon_body ); ?></div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
