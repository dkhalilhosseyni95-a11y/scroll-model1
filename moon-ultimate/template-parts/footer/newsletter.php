<?php
/**
 * Newsletter call to action.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

?>
<section class="moon-newsletter" aria-labelledby="moon-news-title">
	<div class="moon-newsletter__grid moon-reveal">
		<div>
			<p class="moon-eyebrow moon-section-title__eyebrow"><span dir="ltr"><?php echo esc_html( moon_opt( 'news_eyebrow' ) ); ?></span></p>
			<h2 class="moon-newsletter__title" id="moon-news-title"><?php echo esc_html( moon_opt( 'news_title' ) ); ?></h2>
		</div>
		<form class="moon-newsletter__form" data-moon-newsletter>
			<label class="moon-sr-only" for="moon-news-email"><?php esc_html_e( 'ایمیل شما', 'moon-theme' ); ?></label>
			<input class="moon-newsletter__input" id="moon-news-email" type="email" name="email" required placeholder="<?php esc_attr_e( 'ایمیل شما', 'moon-theme' ); ?>" dir="ltr" autocomplete="email">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'moon_subscribe' ) ); ?>">
			<button type="submit" class="moon-newsletter__submit" aria-label="<?php esc_attr_e( 'عضویت در خبرنامه', 'moon-theme' ); ?>">
				<?php moon_the_icon( 'arrow-left', 18 ); ?>
			</button>
		</form>
	</div>
	<p class="moon-newsletter__note" data-moon-newsletter-note hidden></p>
</section>
