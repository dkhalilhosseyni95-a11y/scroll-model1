<?php
/**
 * Theme settings: schema, sanitising, defaults, migration and portability.
 *
 * Everything the panel writes lives in one option (`moon_options`), which is
 * never deleted by an update: activating a new theme version merges the new
 * defaults *under* the stored values, so a shop keeps every customisation.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings definition.
 */
class Moon_Settings {

	/**
	 * Stored shape of the settings option.
	 */
	const VERSION = 4;

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_migrate' ), 5 );
	}

	/**
	 * Register the option with the Settings API.
	 */
	public static function register() {
		register_setting( 'moon_options_group', 'moon_options', array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			'default'           => array(),
		) );
	}

	/**
	 * Load the schema.
	 *
	 * @return array
	 */
	public static function schema() {
		static $schema = null;

		if ( null === $schema ) {
			require_once MOON_THEME_CORE_DIR . 'settings/schema.php';
			$schema = moon_settings_schema();
		}

		return $schema;
	}

	/**
	 * Flat key => field map for every page.
	 *
	 * @return array
	 */
	public static function fields() {
		static $fields = null;

		if ( null !== $fields ) {
			return $fields;
		}

		$fields = array();

		foreach ( self::schema() as $page ) {
			foreach ( $page['tabs'] as $tab ) {
				if ( empty( $tab['fields'] ) ) {
					continue;
				}

				foreach ( $tab['fields'] as $field ) {
					$fields[ $field['key'] ] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Default value of every field.
	 *
	 * @return array
	 */
	public static function schema_defaults() {
		$defaults = array();

		foreach ( self::fields() as $key => $field ) {
			$defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
		}

		return $defaults;
	}

	/**
	 * Complete defaults: schema fields plus the theme's content defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		$defaults = self::schema_defaults();

		if ( function_exists( 'moon_option_defaults' ) ) {
			foreach ( moon_option_defaults() as $key => $value ) {
				// The schema declares an empty default for every text field, but
				// the theme ships real copy for the homepage, footer and brand.
				// An empty schema default must not blank that shipped content
				// (that is what left the hero without a title on a fresh install).
				if ( ! array_key_exists( $key, $defaults ) || ( '' === $defaults[ $key ] && '' !== $value ) ) {
					$defaults[ $key ] = $value;
				}
			}
		}

		return apply_filters( 'moon_settings_defaults', $defaults );
	}

	/**
	 * Every setting, merged over the defaults.
	 *
	 * @return array
	 */
	public static function all() {
		$stored = get_option( 'moon_options', array() );

		return wp_parse_args( is_array( $stored ) ? $stored : array(), self::defaults() );
	}

	/**
	 * One setting.
	 *
	 * @param string $key     Key.
	 * @param mixed  $default Fallback when empty.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$options = self::all();
		$value   = isset( $options[ $key ] ) ? $options[ $key ] : '';

		if ( null !== $default && ( '' === $value || null === $value ) ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Sanitise one submitted value against its field type.
	 *
	 * @param array $field Field definition.
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	public static function sanitize_value( $field, $value ) {
		$type = $field['type'];

		switch ( $type ) {
			case 'checkbox':
				return empty( $value ) ? 0 : 1;

			case 'textarea':
				return sanitize_textarea_field( (string) $value );

			case 'code':
				// Administrators with unfiltered_html may store markup and scripts.
				if ( current_user_can( 'unfiltered_html' ) ) {
					return (string) $value;
				}

				return wp_kses_post( (string) $value );

			case 'url':
				return esc_url_raw( trim( (string) $value ) );

			case 'media_url':
				return esc_url_raw( trim( (string) $value ) );

			case 'color':
				$color = sanitize_hex_color( trim( (string) $value ) );

				// Allow clearing a colour back to the theme default.
				return $color ? $color : '';

			case 'media':
				return absint( $value );

			case 'number':
				$number = (int) $value;
				$min    = isset( $field['min'] ) ? (int) $field['min'] : 0;
				$max    = isset( $field['max'] ) ? (int) $field['max'] : PHP_INT_MAX;

				return max( $min, min( $max, $number ) );

			case 'select':
				$options = isset( $field['options'] ) ? array_map( 'strval', array_keys( $field['options'] ) ) : array();
				$value   = (string) $value;

				return in_array( $value, $options, true ) ? $value : ( isset( $field['default'] ) ? (string) $field['default'] : '' );

			case 'checkboxes':
				$options = isset( $field['options'] ) ? array_map( 'strval', array_keys( $field['options'] ) ) : array();
				$values  = is_array( $value ) ? array_map( 'strval', $value ) : array_filter( explode( ',', (string) $value ) );

				return implode( ',', array_values( array_intersect( array_map( 'sanitize_key', $values ), $options ) ) );

			case 'sortable':
				$options = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
				$rows    = array();
				$seen    = array();

				// Accepts "key:1,key:0" from the panel.
				foreach ( explode( ',', (string) $value ) as $chunk ) {
					$parts = explode( ':', trim( $chunk ) );
					$key   = sanitize_key( $parts[0] );

					if ( ! $key || ! in_array( $key, $options, true ) || isset( $seen[ $key ] ) ) {
						continue;
					}

					$seen[ $key ] = true;
					$rows[]       = $key . ':' . ( empty( $parts[1] ) ? '0' : '1' );
				}

				// Elements the request omitted are appended switched off, so a new
				// element never disappears silently after an update.
				foreach ( $options as $key ) {
					if ( ! isset( $seen[ $key ] ) ) {
						$rows[] = $key . ':0';
					}
				}

				return implode( ',', $rows );

			default:
				return sanitize_text_field( (string) $value );
		}
	}

	/**
	 * Sanitise the whole submitted option.
	 *
	 * Stored values for keys the request does not submit are kept: the panel is
	 * split into pages and a save on one page must not wipe another.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$stored = get_option( 'moon_options', array() );
		$stored = is_array( $stored ) ? $stored : array();
		$input  = is_array( $input ) ? $input : array();
		$output = $stored;

		// A full form submit marks which page it came from; only that page's keys
		// are rewritten, so a checkbox left out is switched off rather than kept
		// forever. Without the marker (import/reset) every submitted key is used.
		$page = isset( $input['moon_page'] ) ? sanitize_key( $input['moon_page'] ) : '';
		$keys = array();

		if ( $page ) {
			$schema = self::schema();

			if ( isset( $schema[ $page ] ) ) {
				foreach ( $schema[ $page ]['tabs'] as $tab ) {
					foreach ( isset( $tab['fields'] ) ? $tab['fields'] : array() as $field ) {
						$keys[] = $field['key'];
					}
				}
			}
		}

		if ( empty( $keys ) ) {
			$keys = array_keys( self::fields() );
		}

		foreach ( $keys as $key ) {
			$field = self::fields()[ $key ];
			$type  = $field['type'];

			if ( 'checkbox' === $type || 'checkboxes' === $type ) {
				$output[ $key ] = self::sanitize_value( $field, isset( $input[ $key ] ) ? $input[ $key ] : '' );
				continue;
			}

			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}

			$output[ $key ] = self::sanitize_value( $field, $input[ $key ] );
		}

		unset( $output['moon_page'] );

		return $output;
	}

	/* ----------------------------------------------------------------------
	 * Migration
	 * -------------------------------------------------------------------- */

	/**
	 * Bring stored settings up to date without losing anything.
	 */
	public static function maybe_migrate() {
		$version = (int) get_option( 'moon_options_version', 0 );

		if ( $version >= self::VERSION ) {
			return;
		}

		$stored = get_option( 'moon_options', array() );
		$stored = is_array( $stored ) ? $stored : array();

		// New keys appear with their default value; stored values always win.
		$merged = wp_parse_args( $stored, self::defaults() );

		// v2 → v3: the separate header icon switches became one ordered list.
		if ( $version < 3 && empty( $stored['header_elements'] ) ) {
			$elements = array();

			foreach ( array( 'search', 'account', 'wishlist', 'cart', 'theme' ) as $element ) {
				$legacy_key = 'theme' === $element ? 'header_theme_toggle' : 'header_' . $element;
				$enabled    = array_key_exists( $legacy_key, $stored ) ? (int) $stored[ $legacy_key ] : 1;
				$elements[] = $element . ':' . ( $enabled ? '1' : '0' );
			}

			$elements[]             = 'compare:1';
			$merged['header_elements'] = implode( ',', $elements );
		}

		// v1 → v2: the old "accent" option is now a full colour system.
		if ( ! empty( $stored['accent'] ) && empty( $merged['button_bg'] ) ) {
			$merged['button_bg'] = $stored['accent'];
		}

		// v3 → v4: empty text values were written for every key on the first
		// migration, which blanked the shipped hero, campaign and footer copy.
		// Restore the theme's own text wherever the stored value is empty and
		// the theme has copy to show.
		if ( $version < 4 && function_exists( 'moon_option_defaults' ) ) {
			foreach ( moon_option_defaults() as $content_key => $content_value ) {
				if ( '' !== $content_value && isset( $merged[ $content_key ] ) && '' === $merged[ $content_key ] ) {
					$merged[ $content_key ] = $content_value;
				}
			}
		}

		update_option( 'moon_options', $merged );
		update_option( 'moon_options_version', self::VERSION );
	}

	/* ----------------------------------------------------------------------
	 * Footer columns
	 * -------------------------------------------------------------------- */

	/**
	 * Column types available in the footer builder.
	 *
	 * @return array
	 */
	public static function footer_column_types() {
		return array(
			'links'      => __( 'لینک‌های دستی', 'moon-theme' ),
			'menu'       => __( 'منوی وردپرس', 'moon-theme' ),
			'text'       => __( 'متن آزاد', 'moon-theme' ),
			'contact'    => __( 'اطلاعات تماس', 'moon-theme' ),
			'social'     => __( 'شبکه‌های اجتماعی', 'moon-theme' ),
			'newsletter' => __( 'خبرنامه', 'moon-theme' ),
			'payments'   => __( 'روش‌های پرداخت', 'moon-theme' ),
		);
	}

	/**
	 * Default footer columns (the shipped design).
	 *
	 * @return array
	 */
	public static function footer_columns_default() {
		return array(
			array(
				'type'    => 'links',
				'title'   => 'فروشگاه',
				'content' => "زنانه|\nمردانه|\nکالکشن جدید|\nاکسسوری|",
			),
			array(
				'type'    => 'links',
				'title'   => 'خدمات',
				'content' => "راهنمای سایز|\nارسال و بازگشت|\nپیگیری سفارش|\nسوالات متداول|",
			),
			array(
				'type'    => 'links',
				'title'   => 'MOON',
				'content' => "داستان ما|\nتماس با ما|\nقوانین و مقررات|\nحریم خصوصی|",
			),
		);
	}

	/**
	 * Stored footer columns.
	 *
	 * @return array
	 */
	public static function footer_columns() {
		$stored = get_option( 'moon_footer_columns', array() );

		if ( is_array( $stored ) && ! empty( $stored ) ) {
			return $stored;
		}

		// MOON 2.2 kept the footer as three plain text columns. Carry those
		// values into the footer builder so an upgraded site keeps its links.
		$legacy = array();

		foreach ( array( 'footer_col1', 'footer_col2', 'footer_col3' ) as $index => $key ) {
			$value = (string) self::get( $key );

			if ( '' === trim( $value ) ) {
				continue;
			}

			$titles  = array( 'فروشگاه', 'خدمات', 'MOON' );
			$legacy[] = array(
				'type'    => 'links',
				'title'   => $titles[ $index ],
				'menu_id' => 0,
				'content' => $value,
			);
		}

		return $legacy ? $legacy : self::footer_columns_default();
	}

	/**
	 * Sanitise footer column rows.
	 *
	 * @param mixed $rows Raw rows.
	 * @return array
	 */
	public static function sanitize_footer_columns( $rows ) {
		$types = array_keys( self::footer_column_types() );
		$clean = array();

		if ( ! is_array( $rows ) ) {
			return $clean;
		}

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$type = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'links';

			if ( ! in_array( $type, $types, true ) ) {
				$type = 'links';
			}

			$clean[] = array(
				'type'    => $type,
				'title'   => isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '',
				'menu_id' => isset( $row['menu_id'] ) ? absint( $row['menu_id'] ) : 0,
				'content' => isset( $row['content'] ) ? sanitize_textarea_field( $row['content'] ) : '',
			);

			if ( count( $clean ) >= 6 ) {
				break;
			}
		}

		return $clean;
	}

	/* ----------------------------------------------------------------------
	 * Portability: export, import, reset
	 * -------------------------------------------------------------------- */

	/**
	 * Portable snapshot of every builder + setting.
	 *
	 * @return array
	 */
	public static function export() {
		return array(
			'format'          => 'moon-settings',
			'schema_version'  => self::VERSION,
			'theme_version'   => defined( 'MOON_THEME_VERSION' ) ? MOON_THEME_VERSION : '',
			'exported_at'     => gmdate( 'c' ),
			'options'         => self::all(),
			'sections'        => get_option( 'moon_sections', array() ),
			'section_draft'   => get_option( 'moon_sections_draft', array() ),
			'footer_columns'  => self::footer_columns(),
		);
	}

	/**
	 * Import a snapshot produced by `export()`.
	 *
	 * @param string $json Raw JSON.
	 * @return array|WP_Error Report.
	 */
	public static function import( $json ) {
		$data = json_decode( (string) $json, true );

		if ( ! is_array( $data ) || empty( $data['options'] ) || ! is_array( $data['options'] ) ) {
			return new WP_Error( 'moon_import_format', __( 'فایل تنظیمات معتبر نیست.', 'moon-theme' ) );
		}

		// Every imported value is sanitised exactly like panel input.
		$options = self::sanitize( $data['options'] );

		update_option( 'moon_options', $options );
		update_option( 'moon_options_version', self::VERSION );

		if ( ! empty( $data['sections'] ) && is_array( $data['sections'] ) ) {
			$sections = Moon_Sections::sanitize_rows( $data['sections'] );

			if ( $sections ) {
				update_option( 'moon_sections', $sections );
			}
		}

		if ( ! empty( $data['footer_columns'] ) && is_array( $data['footer_columns'] ) ) {
			$columns = self::sanitize_footer_columns( $data['footer_columns'] );

			if ( $columns ) {
				update_option( 'moon_footer_columns', $columns );
			}
		}

		return array(
			'options'  => count( $options ),
			'sections' => ! empty( $data['sections'] ) ? count( $data['sections'] ) : 0,
		);
	}

	/**
	 * Reset one panel page (or everything) to the shipped defaults.
	 *
	 * @param string $page Page key, or 'all'.
	 * @return int Number of keys reset.
	 */
	public static function reset( $page = 'all' ) {
		$stored   = get_option( 'moon_options', array() );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = self::defaults();
		$keys     = array();

		if ( 'all' === $page ) {
			$stored = array();
			$keys   = array_keys( $defaults );
		} else {
			$schema = self::schema();

			if ( ! isset( $schema[ $page ] ) ) {
				return 0;
			}

			foreach ( $schema[ $page ]['tabs'] as $tab ) {
				foreach ( isset( $tab['fields'] ) ? $tab['fields'] : array() as $field ) {
					$keys[] = $field['key'];
					unset( $stored[ $field['key'] ] );
				}
			}
		}

		update_option( 'moon_options', $stored );

		return count( $keys );
	}
}
