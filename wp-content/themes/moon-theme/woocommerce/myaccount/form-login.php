<?php
/**
 * Login, phone (OTP) login and registration screen.
 *
 * Phone login is handled by MOON Core; when the Digits plugin is active its own
 * OTP screen replaces this form (see Moon_OTP::mode()).
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

$moon_mode      = class_exists( 'Moon_OTP' ) ? Moon_OTP::mode() : 'password';
$moon_otp_on    = ! class_exists( 'Moon_Settings' ) || Moon_Settings::get( 'otp_login', '1' );
$moon_register  = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' )
	&& ( ! class_exists( 'Moon_Settings' ) || Moon_Settings::get( 'otp_register', '1' ) );

if ( ! $moon_otp_on ) {
	$moon_mode = 'password';
}

$moon_digits    = 'digits' === $moon_mode && class_exists( 'Moon_OTP' ) ? Moon_OTP::digits_form() : '';
$moon_redirect  = isset( $_GET['redirect'] ) ? esc_url_raw( wp_unslash( $_GET['redirect'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$moon_lost      = wc_lostpassword_url();
?>

<div class="moon-auth" data-moon-auth>
	<?php if ( $moon_digits ) : ?>
		<div class="moon-auth__digits">
			<?php echo $moon_digits; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Digits shortcode output. ?>
		</div>
	<?php else : ?>

		<div class="moon-auth__tabs" role="tablist">
			<?php if ( 'otp' === $moon_mode ) : ?>
				<button type="button" class="moon-auth__tab is-active" role="tab" data-moon-auth-tab="otp" aria-selected="true"><?php esc_html_e( 'ورود با کد پیامکی', 'moon-theme' ); ?></button>
				<button type="button" class="moon-auth__tab" role="tab" data-moon-auth-tab="login" aria-selected="false"><?php esc_html_e( 'ورود با رمز', 'moon-theme' ); ?></button>
			<?php else : ?>
				<button type="button" class="moon-auth__tab is-active" role="tab" data-moon-auth-tab="login" aria-selected="true"><?php esc_html_e( 'ورود', 'moon-theme' ); ?></button>
				<button type="button" class="moon-auth__tab" role="tab" data-moon-auth-tab="otp" aria-selected="false"><?php esc_html_e( 'ورود با کد پیامکی', 'moon-theme' ); ?></button>
			<?php endif; ?>

			<?php if ( $moon_register ) : ?>
				<button type="button" class="moon-auth__tab" role="tab" data-moon-auth-tab="register" aria-selected="false"><?php esc_html_e( 'ثبت‌نام', 'moon-theme' ); ?></button>
			<?php endif; ?>
		</div>

		<?php /* ---------------- Phone login ---------------- */ ?>
		<div class="moon-auth__panel<?php echo 'otp' === $moon_mode ? ' is-active' : ''; ?>" data-moon-auth-panel="otp" role="tabpanel">
			<h2><?php esc_html_e( 'ورود با شماره موبایل', 'moon-theme' ); ?></h2>
			<p class="moon-auth__hint"><?php esc_html_e( 'شماره موبایل خود را وارد کنید؛ کد تأیید پیامک می‌شود و بدون رمز عبور وارد حساب می‌شوید.', 'moon-theme' ); ?></p>

			<form class="moon-auth__form" data-moon-otp-form>
				<label class="moon-field moon-field--inline">
					<span class="moon-field__grow">
						<span><?php esc_html_e( 'شماره موبایل', 'moon-theme' ); ?></span>
						<input type="tel" name="phone" inputmode="tel" autocomplete="tel" placeholder="۰۹۱۲۳۴۵۶۷۸۹" required>
					</span>
					<button type="button" class="moon-auth__send" data-moon-otp-send><?php esc_html_e( 'ارسال کد', 'moon-theme' ); ?></button>
				</label>

				<label class="moon-field">
					<span><?php esc_html_e( 'کد تأیید', 'moon-theme' ); ?></span>
					<input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="- - - - - -">
				</label>

				<button type="submit" class="moon-btn moon-btn--dark moon-auth__submit"><?php esc_html_e( 'ورود به حساب', 'moon-theme' ); ?></button>
				<p class="moon-auth__note" data-moon-otp-note role="status" aria-live="polite"></p>
			</form>
		</div>

		<?php /* ---------------- Password login ---------------- */ ?>
		<div class="moon-auth__panel<?php echo 'otp' === $moon_mode ? '' : ' is-active'; ?>" data-moon-auth-panel="login" role="tabpanel">
			<h2><?php esc_html_e( 'ورود به حساب', 'moon-theme' ); ?></h2>
			<p class="moon-auth__hint"><?php esc_html_e( 'با شماره موبایل یا ایمیل و رمز عبور وارد شوید.', 'moon-theme' ); ?></p>

			<form class="woocommerce-form woocommerce-form-login login moon-auth__form" method="post">
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<label class="moon-field">
					<span><?php esc_html_e( 'شماره موبایل یا ایمیل', 'moon-theme' ); ?></span>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required>
				</label>

				<label class="moon-field">
					<span><?php esc_html_e( 'رمز عبور', 'moon-theme' ); ?></span>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required>
				</label>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

				<?php if ( $moon_redirect ) : ?>
					<input type="hidden" name="redirect" value="<?php echo esc_attr( $moon_redirect ); ?>" />
				<?php endif; ?>

				<button type="submit" class="moon-btn moon-btn--dark moon-auth__submit" name="login" value="<?php esc_attr_e( 'ورود', 'moon-theme' ); ?>"><?php esc_html_e( 'ورود', 'moon-theme' ); ?></button>

				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline moon-auth__remember">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever">
					<span><?php esc_html_e( 'مرا به خاطر بسپار', 'moon-theme' ); ?></span>
				</label>

				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>

			<div class="moon-auth__alt">
				<a href="<?php echo esc_url( $moon_lost ); ?>"><?php esc_html_e( 'رمز عبور را فراموش کرده‌اید؟', 'moon-theme' ); ?></a>
				<?php if ( $moon_register ) : ?>
					<button type="button" class="moon-linkbtn" data-moon-auth-tab="register"><?php esc_html_e( 'ساخت حساب جدید', 'moon-theme' ); ?></button>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $moon_register ) : ?>
			<?php /* ---------------- Registration ---------------- */ ?>
			<div class="moon-auth__panel" data-moon-auth-panel="register" role="tabpanel">
				<h2><?php esc_html_e( 'ساخت حساب کاربری', 'moon-theme' ); ?></h2>
				<p class="moon-auth__hint"><?php esc_html_e( 'با شماره موبایل ثبت‌نام کنید؛ سفارش‌ها، آدرس‌ها و علاقه‌مندی‌هایتان یک‌جا ذخیره می‌شود.', 'moon-theme' ); ?></p>

				<form class="moon-auth__form" data-moon-register-form>
					<?php /* Step 1 — prove ownership of the number. */ ?>
					<div class="moon-auth__step" data-moon-register-step="verify">
						<label class="moon-field moon-field--inline">
							<span class="moon-field__grow">
								<span><?php esc_html_e( 'شماره موبایل', 'moon-theme' ); ?></span>
								<input type="tel" name="phone" inputmode="tel" autocomplete="tel" placeholder="۰۹۱۲۳۴۵۶۷۸۹" required>
							</span>
							<button type="button" class="moon-auth__send" data-moon-register-send><?php esc_html_e( 'ارسال کد', 'moon-theme' ); ?></button>
						</label>

						<label class="moon-field">
							<span><?php esc_html_e( 'کد تأیید پیامکی', 'moon-theme' ); ?></span>
							<input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="- - - - - -">
						</label>

						<button type="button" class="moon-btn moon-auth__submit" data-moon-register-verify><?php esc_html_e( 'تأیید شماره موبایل', 'moon-theme' ); ?></button>
					</div>

					<?php /* Step 2 — only reachable with the token a verified code returns. */ ?>
					<div class="moon-auth__step" data-moon-register-step="details" hidden>
						<p class="moon-auth__verified"><?php esc_html_e( 'شماره شما تأیید شد. اطلاعات حساب را کامل کنید.', 'moon-theme' ); ?></p>

						<input type="hidden" name="token" value="" data-moon-register-token>

						<div class="moon-auth__row">
							<label class="moon-field">
								<span><?php esc_html_e( 'نام', 'moon-theme' ); ?></span>
								<input type="text" name="first_name" autocomplete="given-name" required>
							</label>
							<label class="moon-field">
								<span><?php esc_html_e( 'نام خانوادگی', 'moon-theme' ); ?></span>
								<input type="text" name="last_name" autocomplete="family-name">
							</label>
						</div>

						<label class="moon-field">
							<span><?php esc_html_e( 'ایمیل (اختیاری)', 'moon-theme' ); ?></span>
							<input type="email" name="email" autocomplete="email">
						</label>

						<label class="moon-field">
							<span><?php esc_html_e( 'رمز عبور (اختیاری)', 'moon-theme' ); ?></span>
							<input type="password" name="password" autocomplete="new-password" minlength="8">
						</label>

						<button type="submit" class="moon-btn moon-btn--dark moon-auth__submit"><?php esc_html_e( 'ثبت‌نام و ورود', 'moon-theme' ); ?></button>
					</div>

					<p class="moon-auth__note" data-moon-register-note role="status" aria-live="polite"></p>
				</form>
			</div>
		<?php endif; ?>

	<?php endif; ?>
</div>
