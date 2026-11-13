<?php
/**
 * Shared post card used by archives and search.
 *
 * @package Moon_Child
 */

defined( 'ABSPATH' ) || exit;
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
		<p class="moon-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
	</div>
</article>
