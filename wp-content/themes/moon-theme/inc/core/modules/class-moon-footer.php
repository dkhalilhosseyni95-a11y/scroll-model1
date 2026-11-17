<?php
/**
 * Footer builder: columns with their own type, socials, payments and legal bar.
 *
 * Columns are stored in `moon_footer_columns` (its own option so the footer
 * builder saves independently from the design pages) and each column renders
 * one of the types in `Moon_Settings::footer_column_types()`.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site footer.
 */
class Moon_Footer {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_post_moon_save_footer', array( __CLASS__, 'save_columns' ) );
	}

	/**
	 * Social networks configured in the panel.
	 *
	 * @return array
	 */
	public static function social_links() {
		$networks = array(
			'instagram' => array( 'label' => __( 'اینستاگرام', 'moon-theme' ), 'icon' => 'instagram' ),
			'telegram'  => array( 'label' => __( 'تلگرام', 'moon-theme' ), 'icon' => 'send' ),
			'whatsapp'  => array( 'label' => __( 'واتس‌اپ', 'moon-theme' ), 'icon' => 'phone' ),
			'youtube'   => array( 'label' => __( 'یوتیوب', 'moon-theme' ), 'icon' => 'eye' ),
			'pinterest' => array( 'label' => __( 'پینترست', 'moon-theme' ), 'icon' => 'heart' ),
			'x'         => array( 'label' => __( 'ایکس', 'moon-theme' ), 'icon' => 'star' ),
		);

		$links = array();

		foreach ( $networks as $key => $network ) {
			$url = (string) Moon_Settings::get( 'social_' . $key );

			// The footer group kept the original `footer_` keys for the two
			// networks that shipped first.
			if ( ! $url ) {
				$url = (string) Moon_Settings::get( 'footer_' . $key );
			}

			if ( $url ) {
				$links[] = array(
					'key'   => $key,
					'label' => $network['label'],
					'icon'  => $network['icon'],
					'url'   => $url,
				);
			}
		}

		return $links;
	}

	/**
	 * Payment method labels selected in the panel.
	 *
	 * @return array
	 */
	public static function payments() {
		$labels = array(
			'shetab'   => 'شتاب',
			'zarinpal' => 'زرین‌پال',
			'visa'     => 'Visa',
			'master'   => 'Mastercard',
			'cod'      => 'پرداخت در محل',
		);

		$selected = array_filter( explode( ',', (string) Moon_Settings::get( 'footer_payments', 'shetab,zarinpal' ) ) );
		$badges   = array();

		foreach ( $selected as $key ) {
			if ( isset( $labels[ $key ] ) ) {
				$badges[] = $labels[ $key ];
			}
		}

		return $badges;
	}

	/**
	 * Render the footer.
	 */
	public static function render() {
		$columns = Moon_Settings::footer_columns();
		$socials = self::social_links();
		$mobile  = (string) Moon_Settings::get( 'footer_mobile_cols', '1' );
		?>
		<footer class="moon-footer moon-footer--cols-<?php echo esc_attr( (string) count( $columns ) ); ?> moon-footer--mobile-<?php echo esc_attr( sanitize_html_class( $mobile ) ); ?>" id="footer">
			<div class="moon-footer__grid">
				<div class="moon-footer__brandcol">
					<p class="moon-footer__brand<?php echo moon_is_arabic_script( moon_brand_text() ) ? ' is-fa' : ''; ?>"><?php echo esc_html( moon_brand_text() ); ?></p>

					<?php if ( Moon_Settings::get( 'footer_about' ) ) : ?>
						<p class="moon-footer__about"><?php echo esc_html( Moon_Settings::get( 'footer_about' ) ); ?></p>
					<?php endif; ?>

					<?php if ( $socials ) : ?>
						<div class="moon-footer__social">
							<?php foreach ( $socials as $social ) : ?>
								<a href="<?php echo esc_url( $social['url'] ); ?>" aria-label="<?php echo esc_attr( $social['label'] ); ?>" rel="noopener" target="_blank">
									<?php moon_the_icon( $social['icon'], 16 ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php foreach ( $columns as $column ) : ?>
					<div class="moon-footer__column">
						<?php if ( ! empty( $column['title'] ) ) : ?>
							<p class="moon-footer__col-title"><?php echo esc_html( $column['title'] ); ?></p>
						<?php endif; ?>

						<?php self::column_body( $column, $socials ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="moon-footer__wordmark" aria-hidden="true" dir="ltr">MOON</div>

			<div class="moon-footer__bottom">
				<span><?php echo esc_html( Moon_Settings::get( 'footer_copyright' ) ); ?></span>

				<?php $badges = self::payments(); ?>
				<?php if ( $badges ) : ?>
					<span class="moon-footer__pay">
						<?php foreach ( $badges as $badge ) : ?>
							<span class="moon-pay-badge" dir="auto"><?php echo esc_html( $badge ); ?></span>
						<?php endforeach; ?>
					</span>
				<?php endif; ?>

				<?php if ( Moon_Settings::get( 'footer_cities' ) ) : ?>
					<span dir="ltr"><?php echo esc_html( Moon_Settings::get( 'footer_cities' ) ); ?></span>
				<?php endif; ?>
			</div>
		</footer>
		<?php
	}

	/**
	 * Content of one footer column.
	 *
	 * @param array $column  Column row.
	 * @param array $socials Social links.
	 */
	private static function column_body( $column, $socials ) {
		switch ( $column['type'] ) {
			case 'menu':
				if ( ! empty( $column['menu_id'] ) ) {
					wp_nav_menu( array(
						'menu'        => (int) $column['menu_id'],
						'container'   => false,
						'menu_class'  => 'moon-footer__links',
						'depth'       => 1,
						'fallback_cb' => false,
					) );
				}
				break;

			case 'text':
				echo '<div class="moon-footer__text">' . wp_kses_post( wpautop( $column['content'] ) ) . '</div>';
				break;

			case 'contact':
				?>
				<ul class="moon-footer__links moon-footer__contact">
					<?php if ( Moon_Settings::get( 'contact_phone' ) ) : ?>
						<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', (string) Moon_Settings::get( 'contact_phone' ) ) ); ?>" dir="ltr"><?php echo esc_html( Moon_Settings::get( 'contact_phone' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( Moon_Settings::get( 'contact_email' ) ) : ?>
						<li><a href="mailto:<?php echo esc_attr( (string) Moon_Settings::get( 'contact_email' ) ); ?>" dir="ltr"><?php echo esc_html( Moon_Settings::get( 'contact_email' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( Moon_Settings::get( 'contact_hours' ) ) : ?>
						<li><?php echo esc_html( Moon_Settings::get( 'contact_hours' ) ); ?></li>
					<?php endif; ?>
					<?php if ( Moon_Settings::get( 'contact_address' ) ) : ?>
						<li><?php echo esc_html( Moon_Settings::get( 'contact_address' ) ); ?></li>
					<?php endif; ?>
					<?php if ( Moon_Settings::get( 'contact_whatsapp' ) ) : ?>
						<li><a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D+/', '', (string) Moon_Settings::get( 'contact_whatsapp' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'واتس‌اپ', 'moon-theme' ); ?></a></li>
					<?php endif; ?>
				</ul>
				<?php
				break;

			case 'social':
				if ( $socials ) {
					?>
					<ul class="moon-footer__links">
						<?php foreach ( $socials as $social ) : ?>
							<li><a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $social['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<?php
				}
				break;

			case 'newsletter':
				get_template_part( 'template-parts/footer/newsletter' );
				break;

			case 'payments':
				$badges = self::payments();

				if ( $badges ) {
					echo '<p class="moon-footer__pay">';

					foreach ( $badges as $badge ) {
						echo '<span class="moon-pay-badge" dir="auto">' . esc_html( $badge ) . '</span>';
					}

					echo '</p>';
				}
				break;

			case 'links':
			default:
				$links = moon_footer_links( $column['content'] );

				if ( $links ) {
					?>
					<ul class="moon-footer__links">
						<?php foreach ( $links as $link ) : ?>
							<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<?php
				}
				break;
		}

	}

	/**
	 * Save footer columns from the panel.
	 */
	public static function save_columns() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'moon-theme' ), 403 );
		}

		check_admin_referer( 'moon_save_footer' );

		$rows    = isset( $_POST['columns'] ) && is_array( $_POST['columns'] ) ? wp_unslash( $_POST['columns'] ) : array();
		$columns = Moon_Settings::sanitize_footer_columns( $rows );

		if ( empty( $columns ) ) {
			$columns = Moon_Settings::footer_columns_default();
		}

		update_option( 'moon_footer_columns', $columns );

		$notice = isset( $_POST['footer_reset'] ) ? 'footer-reset' : 'footer-saved';

		wp_safe_redirect( add_query_arg( 'moon_notice', $notice, moon_core_admin_url( 'moon-footer' ) ) );
		exit;
	}
}
