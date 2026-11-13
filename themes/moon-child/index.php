<?php
/**
 * Blog index / fallback archive.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="moon-container moon-index">
	<?php if ( have_posts() ) : ?>
		<header class="moon-page-head">
			<p class="moon-eyebrow"><?php esc_html_e( 'Journal', 'moon-child' ); ?></p>
			<h1 class="moon-page-title"><?php echo esc_html( get_the_archive_title() ? wp_strip_all_tags( get_the_archive_title() ) : get_bloginfo( 'name' ) ); ?></h1>
		</header>
		<div class="moon-index__grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'moon-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="moon-card__media" href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'moon-portrait', array( 'loading' => 'lazy' ) ); ?>
						</a>
					<?php endif; ?>
					<div class="moon-card__body">
						<p class="moon-eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
						<h2 class="moon-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="moon-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<p class="moon-empty"><?php esc_html_e( 'No stories published yet.', 'moon-child' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
