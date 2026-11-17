<?php
/**
 * Blog post card.
 *
 * @package Moon_Theme
 */

?>
<article <?php post_class( 'moon-post-card' ); ?>>
	<a class="moon-post-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'moon-card', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<?php moon_placeholder_icon( 36 ); ?>
		<?php endif; ?>
	</a>
	<p class="moon-post-card__date"><?php echo esc_html( get_the_date() ); ?></p>
	<h2 class="moon-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<p class="moon-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
</article>
