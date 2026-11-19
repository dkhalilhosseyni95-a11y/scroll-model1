<?php
/**
 * Template Name: تماس با ما
 *
 * @package Netelec
 */

get_header();

$netelec_cards = array(
	array( 'phone', 'تلفن', netelec_opt( 'footer_phone' ), netelec_opt( 'footer_phone' ) ? 'tel:' . preg_replace( '/[^0-9+]/', '', netelec_opt( 'footer_phone' ) ) : '' ),
	array( 'mail', 'ایمیل', netelec_opt( 'footer_email' ), netelec_opt( 'footer_email' ) ? 'mailto:' . netelec_opt( 'footer_email' ) : '' ),
	array( 'pin', 'آدرس', netelec_opt( 'footer_address' ), '' ),
	array( 'clock', 'ساعات پاسخ‌گویی', netelec_opt( 'footer_hours' ), '' ),
);
?>
<?php netelec_page_header( get_the_title(), 'پاسخ سوالات و مشاوره خرید' ); ?>

<div class="container contact-page">
	<ul class="contact-cards">
		<?php foreach ( $netelec_cards as $netelec_card ) : ?>
			<?php if ( ! $netelec_card[2] ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<li class="contact-card">
				<span class="contact-card__icon"><?php netelec_icon( $netelec_card[0] ); ?></span>
				<span class="contact-card__label"><?php echo esc_html( $netelec_card[1] ); ?></span>
				<?php if ( $netelec_card[3] ) : ?>
					<a href="<?php echo esc_url( $netelec_card[3] ); ?>"><?php echo esc_html( $netelec_card[2] ); ?></a>
				<?php else : ?>
					<strong><?php echo esc_html( $netelec_card[2] ); ?></strong>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="contact-grid">
		<div class="contact-form-card">
			<h2>پیام خود را بنویسید</h2>
			<p>در اولین فرصت کاری پاسخ می‌دهیم.</p>

			<form class="contact-form" id="netelec-contact-form" novalidate>
				<div class="form-row">
					<label>نام و نام خانوادگی <span>*</span>
						<input type="text" name="name" required autocomplete="name">
					</label>
					<label>ایمیل <span>*</span>
						<input type="email" name="email" required autocomplete="email" dir="ltr">
					</label>
				</div>
				<div class="form-row">
					<label>شماره تماس
						<input type="tel" name="phone" autocomplete="tel" dir="ltr">
					</label>
					<label>موضوع
						<input type="text" name="subject">
					</label>
				</div>
				<label>متن پیام <span>*</span>
					<textarea name="message" rows="6" required></textarea>
				</label>
				<div class="contact-form__honeypot" aria-hidden="true">
					<label>وب‌سایت<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
				</div>
				<button type="submit" class="btn btn--primary">ارسال پیام</button>
				<p class="form-message" role="status" aria-live="polite"></p>
			</form>
		</div>

		<div class="contact-content">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( get_the_content() ) :
					?>
					<div class="entry-content"><?php the_content(); ?></div>
					<?php
				endif;
			endwhile;
			?>

			<?php if ( netelec_opt( 'social_whatsapp' ) || netelec_opt( 'social_telegram' ) || netelec_opt( 'social_instagram' ) ) : ?>
				<div class="contact-social">
					<h2>شبکه‌های اجتماعی</h2>
					<div class="contact-social__links">
						<?php if ( netelec_opt( 'social_whatsapp' ) ) : ?>
							<a href="<?php echo esc_url( netelec_opt( 'social_whatsapp' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php netelec_icon( 'whatsapp', 'nc-icon--sm' ); ?> واتساپ</a>
						<?php endif; ?>
						<?php if ( netelec_opt( 'social_telegram' ) ) : ?>
							<a href="<?php echo esc_url( netelec_opt( 'social_telegram' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php netelec_icon( 'telegram', 'nc-icon--sm' ); ?> تلگرام</a>
						<?php endif; ?>
						<?php if ( netelec_opt( 'social_instagram' ) ) : ?>
							<a href="<?php echo esc_url( netelec_opt( 'social_instagram' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php netelec_icon( 'instagram', 'nc-icon--sm' ); ?> اینستاگرام</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>
