<?php
/**
 * Moon Core settings screen: status, design tokens, widget switches and
 * diagnostics. Content creation stays in Elementor and WooCommerce.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Admin {

	public const PAGE = 'moon-core';

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'save' ) );
		add_action( 'admin_post_moon_core_import_starter', array( $this, 'import_starter' ) );
		add_filter( 'plugin_action_links_' . MOON_CORE_BASENAME, array( $this, 'action_links' ) );
	}

	/**
	 * Import the MOON homepage, menus and demo catalogue.
	 */
	public function import_starter(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to import starter content.', 'moon-core' ), 403 );
		}

		check_admin_referer( 'moon_core_import_starter' );

		$report = Starter_Content::import();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => self::PAGE,
					'imported'      => 1,
					'imported_page' => (int) ( $report['homepage'] ?? 0 ),
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public function menu(): void {
		add_menu_page(
			__( 'Moon Core', 'moon-core' ),
			__( 'Moon Core', 'moon-core' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render' ),
			'dashicons-star-filled',
			58
		);
	}

	/**
	 * Settings link on the Plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public function action_links( array $links ): array {
		$links[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ),
			esc_html__( 'Settings', 'moon-core' )
		);

		return $links;
	}

	/**
	 * Handle the settings form.
	 */
	public function save(): void {
		if ( ! isset( $_POST['moon_core_settings_nonce'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to change these settings.', 'moon-core' ), 403 );
		}

		check_admin_referer( 'moon_core_save_settings', 'moon_core_settings_nonce' );

		$raw = array(
			'brand_name'        => isset( $_POST['brand_name'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_name'] ) ) : '',
			'brand_tagline'     => isset( $_POST['brand_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_tagline'] ) ) : '',
			'default_scheme'    => isset( $_POST['default_scheme'] ) ? sanitize_key( wp_unslash( $_POST['default_scheme'] ) ) : 'auto',
			'header_sticky'     => isset( $_POST['header_sticky'] ) ? 'yes' : 'no',
			'header_hide'       => isset( $_POST['header_hide'] ) ? 'yes' : 'no',
			'accent'            => isset( $_POST['accent'] ) ? sanitize_hex_color( wp_unslash( $_POST['accent'] ) ) : '',
			'radius'            => isset( $_POST['radius'] ) ? absint( wp_unslash( $_POST['radius'] ) ) : 4,
			'container'         => isset( $_POST['container'] ) ? absint( wp_unslash( $_POST['container'] ) ) : 1320,
			'quick_view'        => isset( $_POST['quick_view'] ) ? 'yes' : 'no',
			'newsletter'        => isset( $_POST['newsletter'] ) ? sanitize_key( wp_unslash( $_POST['newsletter'] ) ) : 'store',
			'newsletter_action' => isset( $_POST['newsletter_action'] ) ? esc_url_raw( wp_unslash( $_POST['newsletter_action'] ) ) : '',
			'disabled_widgets'  => isset( $_POST['disabled_widgets'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['disabled_widgets'] ) ) : array(),
		);

		Settings::update( $raw );

		add_settings_error( 'moon_core', 'saved', __( 'MOON settings saved.', 'moon-core' ), 'updated' );
	}

	/**
	 * Dependency status rows.
	 *
	 * @return array<int,array{label:string,value:string,ok:bool}>
	 */
	private function status_rows(): array {
		$wp_version      = isset( $GLOBALS['wp_version'] ) ? (string) $GLOBALS['wp_version'] : '—';
		$elementor       = defined( 'ELEMENTOR_VERSION' ) ? (string) ELEMENTOR_VERSION : '';
		$elementor_pro   = defined( 'ELEMENTOR_PRO_VERSION' ) ? (string) ELEMENTOR_PRO_VERSION : '';
		$woocommerce     = defined( 'WC_VERSION' ) ? (string) WC_VERSION : '';
		$theme           = wp_get_theme();
		$parent          = $theme->parent() ? $theme->parent()->get( 'Name' ) : '';

		return array(
			array(
				'label' => __( 'WordPress', 'moon-core' ),
				/* translators: %1$s: version, %2$s: minimum */
				'value' => sprintf( __( '%1$s (minimum %2$s)', 'moon-core' ), $wp_version, MOON_CORE_MIN_WP ),
				'ok'    => version_compare( $wp_version, MOON_CORE_MIN_WP, '>=' ),
			),
			array(
				'label' => __( 'PHP', 'moon-core' ),
				/* translators: %1$s: version, %2$s: minimum */
				'value' => sprintf( __( '%1$s (minimum %2$s)', 'moon-core' ), PHP_VERSION, MOON_CORE_MIN_PHP ),
				'ok'    => version_compare( PHP_VERSION, MOON_CORE_MIN_PHP, '>=' ),
			),
			array(
				'label' => __( 'Elementor', 'moon-core' ),
				'value' => $elementor ? sprintf( /* translators: %1$s: version, %2$s: minimum */ __( '%1$s (minimum %2$s)', 'moon-core' ), $elementor, MOON_CORE_MIN_ELEMENTOR ) : __( 'Not active', 'moon-core' ),
				'ok'    => $elementor && version_compare( $elementor, MOON_CORE_MIN_ELEMENTOR, '>=' ),
			),
			array(
				'label' => __( 'Elementor Pro', 'moon-core' ),
				'value' => $elementor_pro ? $elementor_pro : __( 'Not active — using theme chrome and WooCommerce templates', 'moon-core' ),
				'ok'    => (bool) $elementor_pro,
			),
			array(
				'label' => __( 'WooCommerce', 'moon-core' ),
				'value' => $woocommerce ? sprintf( /* translators: %1$s: version, %2$s: minimum */ __( '%1$s (minimum %2$s)', 'moon-core' ), $woocommerce, MOON_CORE_MIN_WOOCOMMERCE ) : __( 'Not active — store widgets hidden', 'moon-core' ),
				'ok'    => $woocommerce && version_compare( $woocommerce, MOON_CORE_MIN_WOOCOMMERCE, '>=' ),
			),
			array(
				'label' => __( 'Active theme', 'moon-core' ),
				/* translators: %1$s: theme name, %2$s: parent theme */
				'value' => $parent ? sprintf( '%1$s (%2$s %3$s)', $theme->get( 'Name' ), __( 'parent:', 'moon-core' ), $parent ) : $theme->get( 'Name' ),
				'ok'    => 'moon-child' === $theme->get_template() || 'moon-child' === $theme->get_stylesheet(),
			),
			array(
				'label' => __( 'HPOS (custom order tables)', 'moon-core' ),
				'value' => class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) && \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'custom_order_tables' ) ? __( 'Enabled — declared compatible', 'moon-core' ) : __( 'Disabled', 'moon-core' ),
				'ok'    => true,
			),
			array(
				'label' => __( 'Homepage', 'moon-core' ),
				'value' => get_option( 'show_on_front' ) === 'page' && get_option( 'page_on_front' )
					/* translators: %s: page title */
					? sprintf( __( 'Static page: %s', 'moon-core' ), get_the_title( (int) get_option( 'page_on_front' ) ) )
					: __( 'Latest posts (no static homepage yet)', 'moon-core' ),
				'ok'    => true,
			),
		);
	}

	/**
	 * Screen output.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = Settings::all();
		$registered = Widget_Registry::instance()->available();
		$all        = Widget_Registry::instance()->definitions();
		$theme      = wp_get_theme();
		settings_errors( 'moon_core' );
		?>
		<div class="wrap moon-admin">
			<h1><?php esc_html_e( 'Moon Core', 'moon-core' ); ?>
				<span class="moon-admin__version">v<?php echo esc_html( MOON_CORE_VERSION ); ?></span>
			</h1>
			<p class="moon-admin__intro">
				<?php esc_html_e( 'MOON widgets and storefront behaviour. Page content is edited in Elementor; products, orders and payments stay in WooCommerce.', 'moon-core' ); ?>
			</p>

			<h2><?php esc_html_e( 'Status', 'moon-core' ); ?></h2>
			<table class="widefat striped moon-admin__status">
				<tbody>
					<?php foreach ( $this->status_rows() as $row ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
							<td>
								<span class="moon-dot <?php echo $row['ok'] ? 'is-ok' : 'is-warn'; ?>" aria-hidden="true"></span>
								<?php echo esc_html( $row['value'] ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Widgets registered', 'moon-core' ); ?></th>
						<td><?php echo esc_html( sprintf( '%1$d / %2$d', count( $registered ), count( $all ) ) ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Newsletter subscribers', 'moon-core' ); ?></th>
						<td>
							<?php echo esc_html( number_format_i18n( Subscribers::count() ) ); ?>
							<?php if ( Subscribers::count() > 0 ) : ?>
								<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=moon_core_export_subscribers' ), Ajax::NONCE_EXPORT ) ); ?>"><?php esc_html_e( 'Export CSV', 'moon-core' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<form method="post" action="">
				<?php wp_nonce_field( 'moon_core_save_settings', 'moon_core_settings_nonce' ); ?>

				<?php if ( isset( $_GET['imported'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php esc_html_e( 'MOON starter content imported. The homepage template is ready to edit in Elementor.', 'moon-core' ); ?>
						<?php if ( ! empty( $_GET['imported_page'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
							<a href="<?php echo esc_url( get_edit_post_link( (int) $_GET['imported_page'] ) ); ?>"><?php esc_html_e( 'Edit the homepage', 'moon-core' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Brand & design tokens', 'moon-core' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="brand_name"><?php esc_html_e( 'Brand name', 'moon-core' ); ?></label></th>
						<td><input name="brand_name" id="brand_name" type="text" class="regular-text" value="<?php echo esc_attr( (string) $settings['brand_name'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="brand_tagline"><?php esc_html_e( 'Brand tagline', 'moon-core' ); ?></label></th>
						<td><input name="brand_tagline" id="brand_tagline" type="text" class="regular-text" value="<?php echo esc_attr( (string) $settings['brand_tagline'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Default colour mode', 'moon-core' ); ?></th>
						<td>
							<select name="default_scheme" id="default_scheme">
								<?php foreach ( array( 'auto' => __( 'Follow the visitor’s system', 'moon-core' ), 'light' => __( 'Light', 'moon-core' ), 'dark' => __( 'Dark', 'moon-core' ) ) as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['default_scheme'], $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Visitors can always switch manually; their choice is remembered.', 'moon-core' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="accent"><?php esc_html_e( 'Champagne accent', 'moon-core' ); ?></label></th>
						<td><input name="accent" id="accent" type="color" value="<?php echo esc_attr( (string) $settings['accent'] ); ?>"> <code><?php echo esc_html( (string) $settings['accent'] ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><label for="radius"><?php esc_html_e( 'Base radius (px)', 'moon-core' ); ?></label></th>
						<td><input name="radius" id="radius" type="number" min="0" max="32" value="<?php echo esc_attr( (string) $settings['radius'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="container"><?php esc_html_e( 'Content width (px)', 'moon-core' ); ?></label></th>
						<td><input name="container" id="container" type="number" min="960" max="1920" step="20" value="<?php echo esc_attr( (string) $settings['container'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Header behaviour', 'moon-core' ); ?></th>
						<td>
							<label><input type="checkbox" name="header_sticky" value="yes" <?php checked( $settings['header_sticky'], 'yes' ); ?>> <?php esc_html_e( 'Stick to the top while scrolling', 'moon-core' ); ?></label><br>
							<label><input type="checkbox" name="header_hide" value="yes" <?php checked( $settings['header_hide'], 'yes' ); ?>> <?php esc_html_e( 'Hide when scrolling down, reveal when scrolling up', 'moon-core' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Product quick view', 'moon-core' ); ?></th>
						<td><label><input type="checkbox" name="quick_view" value="yes" <?php checked( $settings['quick_view'], 'yes' ); ?>> <?php esc_html_e( 'Show the quick view button on MOON product cards', 'moon-core' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Newsletter', 'moon-core' ); ?></th>
						<td>
							<select name="newsletter" id="newsletter">
								<option value="store" <?php selected( $settings['newsletter'], 'store' ); ?>><?php esc_html_e( 'Store subscribers in WordPress (private list)', 'moon-core' ); ?></option>
								<option value="remote" <?php selected( $settings['newsletter'], 'remote' ); ?>><?php esc_html_e( 'Forward to an email provider endpoint', 'moon-core' ); ?></option>
							</select>
							<p><input name="newsletter_action" id="newsletter_action" type="url" class="regular-text" placeholder="https://provider.example/subscribe" value="<?php echo esc_attr( (string) $settings['newsletter_action'] ); ?>"></p>
							<p class="description"><?php esc_html_e( 'With a provider URL, signups are forwarded server-side (email field only). Without one, they are stored privately and exportable as CSV.', 'moon-core' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Widgets', 'moon-core' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Uncheck a widget to remove it from the Elementor panel. Existing pages keep rendering it.', 'moon-core' ); ?></p>
				<div class="moon-admin__widgets">
					<?php foreach ( $all as $slug => $config ) : ?>
						<?php
						$disabled   = in_array( $slug, (array) $settings['disabled_widgets'], true );
						$needs_woo  = in_array( 'woocommerce', (array) ( $config['requires'] ?? array() ), true );
						$unavailable = $needs_woo && ! class_exists( 'WooCommerce' );
						?>
						<label class="moon-admin__widget<?php echo $unavailable ? ' is-unavailable' : ''; ?>">
							<input type="checkbox" name="disabled_widgets[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $disabled ); ?>>
							<span><?php echo esc_html( 'moon-' . $slug ); ?></span>
							<?php if ( $needs_woo ) : ?>
								<em><?php esc_html_e( 'WooCommerce', 'moon-core' ); ?></em>
							<?php endif; ?>
						</label>
					<?php endforeach; ?>
				</div>

				<?php submit_button( __( 'Save MOON settings', 'moon-core' ) ); ?>
			</form>

			<h2><?php esc_html_e( 'Starter content', 'moon-core' ); ?></h2>
			<p class="description" style="max-width:70ch">
				<?php esc_html_e( 'Creates the MOON homepage as editable Elementor containers and MOON widgets, imports the bundled photography into the media library, builds the menus and (optionally) a small demo catalogue. Safe to run more than once: it updates the same page, menu and products instead of duplicating them.', 'moon-core' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-block:0.75rem 1.5rem">
				<input type="hidden" name="action" value="moon_core_import_starter">
				<?php wp_nonce_field( 'moon_core_import_starter' ); ?>
				<?php submit_button( __( 'Import MOON starter content', 'moon-core' ), 'secondary', 'submit', false ); ?>
			</form>

			<h2><?php esc_html_e( 'Diagnostics', 'moon-core' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<tr><th scope="row"><?php esc_html_e( 'Plugin path', 'moon-core' ); ?></th><td><code><?php echo esc_html( MOON_CORE_DIR ); ?></code></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Theme', 'moon-core' ); ?></th><td><code><?php echo esc_html( $theme->get_stylesheet_directory() ); ?></code></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'PHP memory limit', 'moon-core' ); ?></th><td><?php echo esc_html( (string) ini_get( 'memory_limit' ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Max upload size', 'moon-core' ); ?></th><td><?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Image sizes', 'moon-core' ); ?></th><td><?php echo esc_html( implode( ', ', array_keys( Helpers::image_size_options() ) ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Elementor theme locations', 'moon-core' ); ?></th><td><?php echo Requirements::elementor_pro_active() ? esc_html__( 'Handled by Elementor Pro Theme Builder when a template exists', 'moon-core' ) : esc_html__( 'Elementor Pro inactive — MOON chrome renders the header and footer', 'moon-core' ); ?></td></tr>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Documentation', 'moon-core' ); ?></h2>
			<ul class="moon-admin__docs">
				<li><a href="https://elementor.com/help/" target="_blank" rel="noopener"><?php esc_html_e( 'Elementor documentation', 'moon-core' ); ?></a></li>
				<li><a href="https://woocommerce.com/documentation/" target="_blank" rel="noopener"><?php esc_html_e( 'WooCommerce documentation', 'moon-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=moon_subscriber' ) ); ?>"><?php esc_html_e( 'Newsletter subscribers', 'moon-core' ); ?></a></li>
			</ul>
		</div>
		<?php
	}
}
