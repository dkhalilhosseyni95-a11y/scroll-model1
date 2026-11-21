<?php
/**
 * Sample testimonials carousel.
 *
 * @package Moon_Theme
 */


defined( 'ABSPATH' ) || exit;

$moon_quotes = array();

for ( $i = 1; $i <= 3; $i++ ) {
	$quote = moon_opt( 'quote' . $i . '_text' );

	if ( ! $quote ) {
		continue;
	}

	$moon_quotes[] = array(
		'text' => $quote,
		'name' => moon_opt( 'quote' . $i . '_name' ),
		'city' => moon_opt( 'quote' . $i . '_city' ),
	);
}

if ( empty( $moon_quotes ) ) {
	return;
}
?>
<section id="voices" class="moon-testimonials" data-moon-testimonials>
	<div class="moon-testimonials__inner">
		<p class="moon-eyebrow moon-testimonials__eyebrow"><span dir="ltr">Voices</span></p>
		<p class="moon-testimonials__hint"><?php esc_html_e( 'نظرات نمونه (نمایشی)', 'moon-theme' ); ?></p>

		<div class="moon-testimonials__stage">
			<?php foreach ( $moon_quotes as $index => $quote ) : ?>
				<blockquote class="moon-testimonials__item<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-quote="<?php echo esc_attr( (string) $index ); ?>">
					<p class="moon-testimonials__quote">«<?php echo esc_html( $quote['text'] ); ?>»</p>
					<footer class="moon-testimonials__author"><?php echo esc_html( trim( $quote['name'] . ' — ' . $quote['city'], ' —' ) ); ?></footer>
				</blockquote>
			<?php endforeach; ?>
		</div>

		<div class="moon-testimonials__dots">
			<?php foreach ( $moon_quotes as $index => $quote ) : ?>
				<button type="button" class="moon-testimonials__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" data-moon-quote-dot="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'نظر %s', 'moon-theme' ), number_format_i18n( $index + 1 ) ) ); ?>"></button>
			<?php endforeach; ?>
		</div>
	</div>
</section>
