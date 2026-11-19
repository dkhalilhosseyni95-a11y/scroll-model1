<?php
/**
 * Theme settings panel (Settings API) and generated CSS.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read one theme setting.
 *
 * @param string $key      Setting key.
 * @param mixed  $fallback Fallback when the key is unknown.
 * @return mixed
 */
function netelec_opt( $key, $fallback = null ) {
	$opts = wp_parse_args( (array) get_option( 'netelec_settings', array() ), netelec_defaults() );

	if ( array_key_exists( $key, $opts ) ) {
		return $opts[ $key ];
	}

	return $fallback;
}

/**
 * Lighten or darken a hex color.
 *
 * @param string $hex     Hex color.
 * @param int    $percent Positive lightens, negative darkens.
 * @return string
 */
function netelec_adjust_color( $hex, $percent ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return '#' . $hex;
	}

	$out = '#';
	foreach ( str_split( $hex, 2 ) as $part ) {
		$value = max( 0, min( 255, (int) round( hexdec( $part ) + ( 255 * $percent / 100 ) ) ) );
		$out  .= str_pad( dechex( $value ), 2, '0', STR_PAD_LEFT );
	}
	return $out;
}

/**
 * Build the CSS custom properties from the saved settings.
 *
 * @return string
 */
function netelec_dynamic_css() {
	$primary = netelec_opt( 'primary_color' );
	$dark    = netelec_opt( 'dark_color' );
	$radius  = (int) netelec_opt( 'radius' );

	$shadows = array(
		'none'   => 'none',
		'soft'   => '0 4px 18px rgba(15,23,42,.06)',
		'medium' => '0 10px 30px rgba(15,23,42,.10)',
		'strong' => '0 18px 44px rgba(15,23,42,.16)',
	);
	$shadow  = isset( $shadows[ netelec_opt( 'shadow' ) ] ) ? $shadows[ netelec_opt( 'shadow' ) ] : $shadows['soft'];

	$font = 'vazirmatn' === netelec_opt( 'font_stack' )
		? "'Vazirmatn', Tahoma, Arial, sans-serif"
		: "system-ui, -apple-system, 'Segoe UI', Tahoma, Arial, sans-serif";

	$vars = array(
		'--nc-primary'      => $primary,
		'--nc-primary-dark' => netelec_adjust_color( $primary, -12 ),
		'--nc-primary-soft' => 'rgba(' . implode( ',', netelec_hex_to_rgb( $primary ) ) . ',.10)',
		'--nc-dark'         => $dark,
		'--nc-text'         => netelec_opt( 'text_color' ),
		'--nc-muted'        => netelec_opt( 'muted_color' ),
		'--nc-bg'           => netelec_opt( 'bg_color' ),
		'--nc-card'         => netelec_opt( 'card_color' ),
		'--nc-border'       => netelec_opt( 'border_color' ),
		'--nc-success'      => netelec_opt( 'success_color' ),
		'--nc-sale'         => netelec_opt( 'sale_color' ),
		'--nc-btn-text'     => netelec_opt( 'button_text_color' ),
		'--nc-header-bg'    => netelec_opt( 'header_bg' ),
		'--nc-footer-bg'    => netelec_opt( 'footer_bg' ),
		'--nc-radius'       => $radius . 'px',
		'--nc-radius-sm'    => max( 4, round( $radius * 0.6 ) ) . 'px',
		'--nc-radius-lg'    => round( $radius * 1.5 ) . 'px',
		'--nc-container'    => (int) netelec_opt( 'container' ) . 'px',
		'--nc-header-h'     => (int) netelec_opt( 'header_height' ) . 'px',
		'--nc-shadow'       => $shadow,
		'--nc-shadow-lg'    => 'strong' === netelec_opt( 'shadow' ) ? $shadows['strong'] : '0 14px 38px rgba(15,23,42,.12)',
		'--nc-font'         => $font,
		'--nc-shop-cols'    => (int) netelec_opt( 'shop_columns' ),
		'--nc-mobile-cols'  => (int) netelec_opt( 'mobile_columns' ),
		'--nc-blog-cols'    => (int) netelec_opt( 'blog_columns' ),
	);

	$css = ':root{';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	$css .= '}';

	if ( netelec_opt( 'logo_width' ) ) {
		$css .= '.site-brand .custom-logo,.site-brand img{max-width:' . (int) netelec_opt( 'logo_width' ) . 'px}';
	}

	return $css;
}

/**
 * Hex to RGB triplet.
 *
 * @param string $hex Hex color.
 * @return array
 */
function netelec_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return array( 37, 99, 235 );
	}
	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * Register the settings row.
 */
function netelec_register_settings() {
	register_setting(
		'netelec_options_group',
		'netelec_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'netelec_sanitize_options',
			'show_in_rest'      => false,
		)
	);
}
add_action( 'admin_init', 'netelec_register_settings' );

/**
 * Sanitize one field value.
 *
 * @param array $field Field definition.
 * @param mixed $raw   Raw submitted value.
 * @return mixed
 */
function netelec_sanitize_field( $field, $raw ) {
	switch ( $field['type'] ) {
		case 'checkbox':
			return $raw ? 1 : 0;

		case 'number':
			$value = is_scalar( $raw ) ? (int) $raw : (int) $field['default'];
			if ( isset( $field['min'] ) ) {
				$value = max( (int) $field['min'], $value );
			}
			if ( isset( $field['max'] ) ) {
				$value = min( (int) $field['max'], $value );
			}
			return $value;

		case 'color':
			$color = sanitize_hex_color( is_scalar( $raw ) ? (string) $raw : '' );
			return $color ? $color : $field['default'];

		case 'url':
			return esc_url_raw( is_scalar( $raw ) ? (string) $raw : '' );

		case 'textarea':
			return sanitize_textarea_field( is_scalar( $raw ) ? (string) $raw : '' );

		case 'password':
			// Keep the stored value when the masked field is submitted empty.
			$value = is_scalar( $raw ) ? (string) $raw : '';
			if ( '' === $value ) {
				$saved = (array) get_option( 'netelec_settings', array() );
				$key   = isset( $field['key'] ) ? $field['key'] : '';
				return isset( $saved[ $key ] ) ? $saved[ $key ] : '';
			}
			return sanitize_text_field( $value );

		case 'media':
			return absint( $raw );

		case 'select':
			$options = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
			$value   = is_scalar( $raw ) ? sanitize_key( (string) $raw ) : '';
			return in_array( $value, array_map( 'strval', $options ), true ) ? $value : $field['default'];

		default:
			return sanitize_text_field( is_scalar( $raw ) ? (string) $raw : '' );
	}
}

/**
 * Sanitize the whole option row. Only the submitted tab is rewritten, so
 * saving one tab never wipes the others.
 *
 * @param mixed $input Submitted values.
 * @return array
 */
function netelec_sanitize_options( $input ) {
	$saved  = wp_parse_args( (array) get_option( 'netelec_settings', array() ), netelec_defaults() );
	$schema = netelec_options_schema();
	$tab    = isset( $_POST['netelec_tab'] ) ? sanitize_key( wp_unslash( $_POST['netelec_tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$input  = is_array( $input ) ? $input : array();

	// Requests that are not the theme panel form (customizer, REST, code) send
	// a full settings array: sanitize every known key that is present.
	if ( ! isset( $schema[ $tab ] ) ) {
		$out = $saved;
		foreach ( $schema as $tab_definition ) {
			foreach ( $tab_definition['sections'] as $section ) {
				foreach ( $section['fields'] as $key => $field ) {
					if ( 'sections' === $field['type'] || ! array_key_exists( $key, $input ) ) {
						continue;
					}
					$field['key'] = $key;
					$out[ $key ]  = netelec_sanitize_field( $field, $input[ $key ] );
				}
			}
		}
		return $out;
	}

	foreach ( $schema[ $tab ]['sections'] as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$field['key'] = $key;

			if ( 'sections' === $field['type'] ) {
				$allowed = array_keys( $field['options'] );
				$order   = isset( $input['home_order'] ) ? explode( ',', sanitize_text_field( $input['home_order'] ) ) : array();
				$order   = array_values( array_intersect( $order, $allowed ) );
				// Append any section the user never saw, so nothing is lost.
				foreach ( $allowed as $slug ) {
					if ( ! in_array( $slug, $order, true ) ) {
						$order[] = $slug;
					}
				}
				$enabled = isset( $input['home_enabled'] ) ? (array) $input['home_enabled'] : array();
				$enabled = array_values( array_intersect( array_map( 'sanitize_key', $enabled ), $allowed ) );

				$saved['home_order']   = implode( ',', $order );
				$saved['home_enabled'] = $enabled;
				continue;
			}

			$raw            = array_key_exists( $key, $input ) ? $input[ $key ] : null;
			$saved[ $key ]  = netelec_sanitize_field( $field, $raw );
		}
	}

	return $saved;
}

/**
 * Register the panel page.
 */
function netelec_add_settings_page() {
	add_menu_page(
		'تنظیمات قالب NETELEC',
		'قالب NETELEC',
		'manage_options',
		'netelec-settings',
		'netelec_render_settings_page',
		'dashicons-networking',
		59
	);
}
add_action( 'admin_menu', 'netelec_add_settings_page' );

/**
 * Admin assets for the panel.
 *
 * @param string $hook Current admin page.
 */
function netelec_admin_assets( $hook ) {
	if ( 'toplevel_page_netelec-settings' !== $hook ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'netelec-admin', NETELEC_URI . '/assets/css/admin.css', array( 'wp-color-picker' ), NETELEC_VERSION );
	wp_enqueue_script( 'netelec-admin', NETELEC_URI . '/assets/js/admin.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), NETELEC_VERSION, true );
	wp_localize_script(
		'netelec-admin',
		'netelecAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'netelec_admin' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'netelec_admin_assets' );

/**
 * Render one field control.
 *
 * @param string $key   Setting key.
 * @param array $field  Field definition.
 * @param mixed  $value Current value.
 */
function netelec_render_field( $key, $field, $value ) {
	$name = 'netelec_settings[' . $key . ']';
	$id   = 'netelec-' . $key;

	switch ( $field['type'] ) {
		case 'checkbox':
			printf(
				'<label class="nc-switch"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s><span></span></label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( 1, (int) $value, false )
			);
			break;

		case 'textarea':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( (string) $value )
			);
			break;

		case 'color':
			printf(
				'<input type="text" class="nc-color" id="%1$s" name="%2$s" value="%3$s" data-default-color="%4$s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_attr( $field['default'] )
			);
			break;

		case 'number':
			printf(
				'<input type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" class="small-text">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_attr( isset( $field['min'] ) ? $field['min'] : 0 ),
				esc_attr( isset( $field['max'] ) ? $field['max'] : 9999 )
			);
			break;

		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $field['options'] as $option_value => $option_label ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $option_value ),
					selected( (string) $value, (string) $option_value, false ),
					esc_html( $option_label )
				);
			}
			echo '</select>';
			break;

		case 'media':
			$url = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
			printf(
				'<div class="nc-media" data-target="%1$s">
					<div class="nc-media-preview">%2$s</div>
					<input type="hidden" id="%1$s" name="%3$s" value="%4$s">
					<button type="button" class="button nc-media-select">انتخاب تصویر</button>
					<button type="button" class="button-link nc-media-remove">حذف</button>
				</div>',
				esc_attr( $id ),
				$url ? '<img src="' . esc_url( $url ) . '" alt="">' : '',
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
			break;

		case 'password':
			printf(
				'<input type="password" id="%1$s" name="%2$s" value="%3$s" class="regular-text" autocomplete="new-password">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
			break;

		case 'sections':
			$order   = explode( ',', (string) netelec_opt( 'home_order' ) );
			$enabled = (array) netelec_opt( 'home_enabled' );
			echo '<div class="nc-sections" data-input="' . esc_attr( $id . '-order' ) . '">';
			echo '<input type="hidden" id="' . esc_attr( $id . '-order' ) . '" name="netelec_settings[home_order]" value="' . esc_attr( implode( ',', $order ) ) . '">';
			foreach ( $order as $slug ) {
				if ( ! isset( $field['options'][ $slug ] ) ) {
					continue;
				}
				printf(
					'<div class="nc-section-row" data-slug="%1$s">
						<span class="nc-drag dashicons dashicons-menu-alt2" aria-hidden="true"></span>
						<label><input type="checkbox" name="netelec_settings[home_enabled][]" value="%1$s" %2$s> %3$s</label>
					</div>',
					esc_attr( $slug ),
					checked( in_array( $slug, $enabled, true ), true, false ),
					esc_html( $field['options'][ $slug ] )
				);
			}
			echo '</div>';
			break;

		default:
			printf(
				'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
	}

	if ( ! empty( $field['desc'] ) ) {
		echo '<p class="description">' . esc_html( $field['desc'] ) . '</p>';
	}
}

/**
 * Render the settings screen.
 */
function netelec_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$schema = netelec_options_schema();
	$tabs   = $schema;
	$tabs['tools'] = array(
		'label'    => 'ابزارها',
		'icon'     => 'dashicons-admin-tools',
		'sections' => array(),
	);

	$current = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $tabs[ $current ] ) ) {
		$current = 'general';
	}
	?>
	<div class="wrap nc-wrap">
		<h1 class="nc-title">
			<span class="dashicons dashicons-networking"></span>
			تنظیمات قالب NETELEC
			<span class="nc-version">نسخه <?php echo esc_html( NETELEC_VERSION ); ?></span>
		</h1>
		<p class="nc-sub">تمام تنظیمات این پنل ذخیره می‌شوند و بلافاصله روی سایت اعمال می‌شوند.</p>

		<nav class="nav-tab-wrapper nc-tabs">
			<?php foreach ( $tabs as $slug => $tab ) : ?>
				<a class="nav-tab <?php echo $slug === $current ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=netelec-settings&tab=' . $slug ) ); ?>">
					<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php settings_errors(); ?>

		<?php if ( 'tools' === $current ) : ?>
			<?php netelec_render_tools_tab(); ?>
		<?php else : ?>
			<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post" class="nc-form">
				<?php settings_fields( 'netelec_options_group' ); ?>
				<input type="hidden" name="netelec_tab" value="<?php echo esc_attr( $current ); ?>">

				<div class="nc-columns">
					<div class="nc-main">
						<?php foreach ( $tabs[ $current ]['sections'] as $section ) : ?>
							<div class="nc-card">
								<h2><?php echo esc_html( $section['title'] ); ?></h2>
								<?php if ( ! empty( $section['desc'] ) ) : ?>
									<p class="nc-card-desc"><?php echo esc_html( $section['desc'] ); ?></p>
								<?php endif; ?>
								<table class="form-table" role="presentation">
									<tbody>
									<?php foreach ( $section['fields'] as $key => $field ) : ?>
										<tr>
											<th scope="row"><label for="netelec-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
											<td><?php netelec_render_field( $key, $field, netelec_opt( $key, $field['default'] ) ); ?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endforeach; ?>

						<?php if ( 'sms' === $current ) : ?>
							<div class="nc-card">
								<h2>تست اتصال سرویس پیامک</h2>
								<p class="nc-card-desc">ابتدا تنظیمات را ذخیره کنید، سپس اتصال را آزمایش کنید. نتیجه فقط وضعیت اتصال را نشان می‌دهد و کدی ارسال نمی‌کند.</p>
								<button type="button" class="button button-secondary" id="nc-sms-test">تست اتصال</button>
								<span id="nc-sms-test-result" class="nc-test-result" aria-live="polite"></span>
							</div>
						<?php endif; ?>

						<p class="submit">
							<button type="submit" class="button button-primary button-large">ذخیره تنظیمات</button>
						</p>
					</div>

					<aside class="nc-side">
						<div class="nc-card nc-help">
							<h2>راهنمای سریع</h2>
							<ul>
								<li>رنگ‌ها و گردی گوشه‌ها از تب «عمومی و ظاهر» روی کل سایت اعمال می‌شود.</li>
								<li>ترتیب سکشن‌های صفحه اصلی را با کشیدن ردیف‌ها تغییر دهید.</li>
								<li>برای ورود پیامکی، ابتدا سرویس‌دهنده و کلید API را ذخیره و تست کنید.</li>
								<li>صفحات قالب (تماس، مقایسه، علاقه‌مندی‌ها...) از تب «ابزارها» ساخته می‌شوند.</li>
							</ul>
						</div>
					</aside>
				</div>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Tools tab: page builder, demo content, system status.
 */
function netelec_render_tools_tab() {
	$pages = array(
		'wishlist' => get_page_by_path( 'wishlist' ),
		'compare'  => get_page_by_path( 'compare' ),
		'contact'  => get_page_by_path( 'contact' ),
		'about'    => get_page_by_path( 'about' ),
	);
	?>
	<div class="nc-columns">
		<div class="nc-main">
			<div class="nc-card">
				<h2>ایجاد صفحات قالب</h2>
				<p class="nc-card-desc">صفحات «درباره ما»، «تماس با ما»، «علاقه‌مندی‌ها»، «مقایسه محصولات»، «قوانین» و «حریم خصوصی» را با قالب درست و لینک در منو می‌سازد. صفحاتی که از قبل وجود دارند دست نمی‌خورند.</p>
				<ul class="nc-status-list">
					<?php foreach ( $pages as $label => $page ) : ?>
						<li>
							<span class="dashicons <?php echo $page ? 'dashicons-yes-alt nc-ok' : 'dashicons-marker nc-off'; ?>"></span>
							<?php echo esc_html( netelec_page_label( $label ) ); ?>
							<?php if ( $page ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>">ویرایش</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<button type="button" class="button button-primary" id="nc-create-pages">ایجاد / تکمیل صفحات</button>
				<span id="nc-create-pages-result" class="nc-test-result" aria-live="polite"></span>
			</div>

			<div class="nc-card">
				<h2>محصولات نمونه</h2>
				<p class="nc-card-desc">برای ارزیابی سریع قالب، چند دسته‌بندی و محصول نمونه (ساده، متغیر، ناموجود و تخفیف‌دار) ساخته می‌شود. این داده‌ها فقط برای تست هستند و می‌توانید بعداً حذفشان کنید.</p>
				<button type="button" class="button" id="nc-seed-demo">ایجاد داده نمونه</button>
				<span id="nc-seed-demo-result" class="nc-test-result" aria-live="polite"></span>
			</div>

			<div class="nc-card">
				<h2>وضعیت سیستم</h2>
				<ul class="nc-status-list">
					<li><span class="dashicons <?php echo class_exists( 'WooCommerce' ) ? 'dashicons-yes-alt nc-ok' : 'dashicons-warning nc-off'; ?>"></span> ووکامرس <?php echo class_exists( 'WooCommerce' ) ? esc_html( WC()->version ) : 'فعال نیست'; ?></li>
					<li><span class="dashicons <?php echo wp_get_theme()->get( 'Name' ) ? 'dashicons-yes-alt nc-ok' : 'dashicons-warning nc-off'; ?>"></span> قالب فعال: <?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></li>
					<li><span class="dashicons <?php echo wp_attachment_is_image( get_theme_mod( 'custom_logo' ) ) || netelec_opt( 'logo_id' ) ? 'dashicons-yes-alt nc-ok' : 'dashicons-marker nc-off'; ?>"></span> لوگوی اختصاصی</li>
					<li><span class="dashicons <?php echo get_option( 'permalink_structure' ) ? 'dashicons-yes-alt nc-ok' : 'dashicons-warning nc-off'; ?>"></span> ساختار پیوند یکتا</li>
				</ul>
			</div>
		</div>
		<aside class="nc-side">
			<div class="nc-card nc-help">
				<h2>پیش‌نیازها</h2>
				<ul>
					<li>ووکامرس (اجباری برای فروشگاه)</li>
					<li>یک سرویس پیامک ایرانی برای ورود با موبایل</li>
					<li>درگاه پرداخت و تنظیمات ارسال از خود ووکامرس</li>
				</ul>
			</div>
		</aside>
	</div>
	<?php
}
