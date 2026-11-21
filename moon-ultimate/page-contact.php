<?php
/**
 * Template Name: تماس با ما — MOON
 *
 * Contact page with a real form. Contact details come from MOON → تنظیمات → فوتر.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$moon_contact = array(
	'phone'    => moon_opt( 'contact_phone' ),
	'email'    => moon_opt( 'contact_email' ),
	'whatsapp' => moon_opt( 'contact_whatsapp' ),
	'hours'    => moon_opt( 'contact_hours' ),
	'address'  => moon_opt( 'contact_address' ),
);
$moon_digits  = static function ( $value ) {
	return preg_replace( '/[^0-9+]/', '', (string) $value );
};

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'moon-page moon-page--contact' ); ?>>
		<?php moon_page_header(); ?>

		<div class="moon-page-body moon-page-body--wide">
			<div class="moon-contact">
				<section class="moon-contact__main" aria-labelledby="moon-contact-title">
					<h2 id="moon-contact-title" class="moon-contact__title"><?php esc_html_e( 'پیام خود را بنویسید', 'moon-theme' ); ?></h2>
					<p class="moon-contact__note"><?php esc_html_e( 'برای پیگیری سفارش، شماره سفارش را در متن پیام بنویسید.', 'moon-theme' ); ?></p>

					<form class="moon-contact__form" data-contact-form novalidate>
						<div class="moon-field">
							<label for="moon-c-name"><?php esc_html_e( 'نام و نام خانوادگی', 'moon-theme' ); ?> *</label>
							<input id="moon-c-name" name="name" type="text" required autocomplete="name">
						</div>
						<div class="moon-field">
							<label for="moon-c-phone"><?php esc_html_e( 'شماره تماس', 'moon-theme' ); ?></label>
							<input id="moon-c-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" dir="ltr" placeholder="09120000000">
						</div>
						<div class="moon-field">
							<label for="moon-c-email"><?php esc_html_e( 'ایمیل', 'moon-theme' ); ?></label>
							<input id="moon-c-email" name="email" type="email" inputmode="email" autocomplete="email" dir="ltr" placeholder="name@example.com">
						</div>
						<div class="moon-field">
							<label for="moon-c-subject"><?php esc_html_e( 'موضوع', 'moon-theme' ); ?></label>
							<select id="moon-c-subject" name="subject">
								<?php foreach ( array( 'پیگیری سفارش', 'راهنمای سایز', 'تعویض و بازگشت', 'همکاری' ) as $moon_subject ) : ?>
									<option value="<?php echo esc_attr( $moon_subject ); ?>"><?php echo esc_html( $moon_subject ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="moon-field moon-field--full">
							<label for="moon-c-message"><?php esc_html_e( 'متن پیام', 'moon-theme' ); ?> *</label>
							<textarea id="moon-c-message" name="message" rows="5" required maxlength="4000"></textarea>
						</div>
						<div class="moon-field moon-field--honeypot" aria-hidden="true">
							<label for="moon-c-website">Website</label>
							<input id="moon-c-website" name="moon_website" type="text" tabindex="-1" autocomplete="off">
						</div>
						<div class="moon-contact__footer">
							<button type="submit" class="moon-btn moon-btn--dark"><?php esc_html_e( 'ارسال پیام', 'moon-theme' ); ?> <?php moon_the_icon( 'send', 15 ); ?></button>
							<p class="moon-contact__status" role="status" aria-live="polite" data-contact-status></p>
						</div>
					</form>
				</section>

				<aside class="moon-contact__aside">
					<?php if ( get_the_content() ) : ?>
						<div class="moon-contact__intro moon-content"><?php the_content(); ?></div>
					<?php endif; ?>

					<ul class="moon-contact__list">
						<?php if ( $moon_contact['phone'] ) : ?>
							<li><?php moon_the_icon( 'phone', 18 ); ?><span><small><?php esc_html_e( 'تلفن', 'moon-theme' ); ?></small><a href="tel:<?php echo esc_attr( $moon_digits( $moon_contact['phone'] ) ); ?>" dir="ltr"><?php echo esc_html( $moon_contact['phone'] ); ?></a></span></li>
						<?php endif; ?>
						<?php if ( $moon_contact['whatsapp'] ) : ?>
							<li><?php moon_the_icon( 'send', 18 ); ?><span><small><?php esc_html_e( 'واتس‌اپ', 'moon-theme' ); ?></small><a href="https://wa.me/<?php echo esc_attr( $moon_digits( $moon_contact['whatsapp'] ) ); ?>" target="_blank" rel="noopener noreferrer" dir="ltr"><?php echo esc_html( $moon_contact['whatsapp'] ); ?></a></span></li>
						<?php endif; ?>
						<?php if ( $moon_contact['email'] ) : ?>
							<li><?php moon_the_icon( 'mail', 18 ); ?><span><small><?php esc_html_e( 'ایمیل', 'moon-theme' ); ?></small><a href="mailto:<?php echo esc_attr( antispambot( $moon_contact['email'] ) ); ?>" dir="ltr"><?php echo esc_html( antispambot( $moon_contact['email'] ) ); ?></a></span></li>
						<?php endif; ?>
						<?php if ( $moon_contact['hours'] ) : ?>
							<li><?php moon_the_icon( 'clock', 18 ); ?><span><small><?php esc_html_e( 'ساعات پاسخگویی', 'moon-theme' ); ?></small><?php echo esc_html( $moon_contact['hours'] ); ?></span></li>
						<?php endif; ?>
						<?php if ( $moon_contact['address'] ) : ?>
							<li><?php moon_the_icon( 'map-pin', 18 ); ?><span><small><?php esc_html_e( 'نشانی', 'moon-theme' ); ?></small><?php echo esc_html( $moon_contact['address'] ); ?></span></li>
						<?php endif; ?>
					</ul>
				</aside>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
