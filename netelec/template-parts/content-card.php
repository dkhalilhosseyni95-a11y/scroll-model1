<?php
/**
 * Blog post card.
 *
 * @package Netelec
 */
?>
<article <?php post_class( 'post-card' ); ?>>
	<a class="post-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'netelec-blog', array( 'alt' => get_the_title(), 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="post-card__placeholder"><?php netelec_icon( 'file' ); ?></span>
		<?php endif; ?>
	</a>

	<div class="post-card__body">
		<?php netelec_post_meta(); ?>

		<h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

		<?php if ( netelec_opt( 'blog_show_excerpt' ) ) : ?>
			<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
		<?php endif; ?>

		<a class="post-card__more" href="<?php the_permalink(); ?>">
			ادامه مطلب
			<?php netelec_icon( 'arrow-left', 'nc-icon--sm' ); ?>
		</a>
	</div>
</article>
