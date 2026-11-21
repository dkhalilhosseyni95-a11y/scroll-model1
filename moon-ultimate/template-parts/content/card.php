<?php
/**
 * Blog post card.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

$moon_cats = get_the_category();
$moon_cat  = ( $moon_cats && 'uncategorized' !== $moon_cats[0]->slug ) ? $moon_cats[0]->name : '';
$moon_read = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 180 ) );
?>
<article <?php post_class( 'moon-post-card' ); ?>>
	<a class="moon-post-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'moon-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
		<?php else : ?>
			<?php moon_placeholder_icon( 36 ); ?>
		<?php endif; ?>
		<?php if ( $moon_cat ) : ?>
			<span class="moon-post-card__cat"><?php echo esc_html( $moon_cat ); ?></span>
		<?php endif; ?>
	</a>
	<div class="moon-post-card__body">
		<p class="moon-post-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( moon_fa_digits( get_the_date() ) ); ?></time>
			<span><?php echo esc_html( sprintf( /* translators: %s: minutes. */ __( '%s دقیقه مطالعه', 'moon-theme' ), moon_fa_digits( (string) $moon_read ) ) ); ?></span>
		</p>
		<h2 class="moon-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="moon-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
		<a class="moon-post-card__more" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: post title. */ __( 'ادامه مطلب %s', 'moon-theme' ), get_the_title() ) ); ?>">
			<?php esc_html_e( 'ادامه مطلب', 'moon-theme' ); ?>
			<?php moon_the_icon( 'arrow-left', 15 ); ?>
		</a>
	</div>
</article>
