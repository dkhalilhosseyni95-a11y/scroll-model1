<?php
/**
 * Archive template (categories, tags, authors, dates).
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="moon-container moon-index">
	<header class="moon-page-head">
		<p class="moon-eyebrow"><?php esc_html_e( 'Archive', 'moon-child' ); ?></p>
		<h1 class="moon-page-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
		<?php
		$moon_description = get_the_archive_description();
		if ( $moon_description ) :
			?>
			<div class="moon-page-head__desc"><?php echo wp_kses_post( $moon_description ); ?></div>
		<?php endif; ?>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="moon-index__grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', get_post_type() );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<p class="moon-empty"><?php esc_html_e( 'Nothing here yet.', 'moon-child' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
