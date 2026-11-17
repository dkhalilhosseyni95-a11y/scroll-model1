<?php
/**
 * Blog post card.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

$moon_cats  = get_the_category();
$moon_cat   = $moon_cats ? $moon_cats[0] : null;
$moon_words = preg_split( '/\s+/u', trim( (string) wp_strip_all_tags( get_the_content() ) ) );
$moon_read  = max( 1, (int) ceil( count( (array) $moon_words ) / 200 ) );
?>
<article <?php post_class( 'moon-post-card' ); ?>>
	<a class="moon-post-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'moon-card', array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="moon-post-card__placeholder"><?php moon_placeholder_icon( 32 ); ?></span>
		<?php endif; ?>
	</a>

	<div class="moon-post-card__meta">
		<?php if ( $moon_cat && 'uncategorized' !== $moon_cat->slug ) : ?>
			<a class="moon-post-card__cat" href="<?php echo esc_url( get_category_link( $moon_cat ) ); ?>"><?php echo esc_html( $moon_cat->name ); ?></a>
		<?php endif; ?>
		<span class="moon-post-card__date"><?php echo esc_html( moon_fa_digits( get_the_date() ) ); ?></span>
		<span class="moon-post-card__dot" aria-hidden="true">·</span>
		<span class="moon-post-card__read"><?php echo esc_html( sprintf( /* translators: %s: minutes. */ __( '٪s دقیقه مطالعه', 'moon-theme' ), moon_fa_digits( (string) $moon_read ) ) ); ?></span>
	</div>

	<h2 class="moon-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

	<p class="moon-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>

	<a class="moon-post-card__more" href="<?php the_permalink(); ?>">
		<?php esc_html_e( 'ادامه مطلب', 'moon-theme' ); ?>
		<?php moon_the_icon( 'arrow-left', 14 ); ?>
	</a>
</article>
