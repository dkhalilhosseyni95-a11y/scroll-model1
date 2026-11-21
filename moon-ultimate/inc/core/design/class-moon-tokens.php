<?php
/**
 * Design tokens: every colour, font and layout value from the panel becomes a
 * CSS custom property, so a colour change never needs a file edit.
 *
 * The stylesheet in `assets/css/base.css` already consumes these variables;
 * this class only overrides them (plus a handful of structural rules that the
 * variables feed). Values left empty keep the theme's shipped design.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Token generation.
 */
class Moon_Tokens {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'styles' ), 30 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 2 );
		add_action( 'wp_footer', array( __CLASS__, 'footer' ), 99 );
	}

	/**
	 * Read a setting.
	 *
	 * @param string $key     Key.
	 * @param string $default Default.
	 * @return string
	 */
	private static function opt( $key, $default = '' ) {
		$value = Moon_Settings::get( $key, $default );

		return is_scalar( $value ) ? (string) $value : $default;
	}

	/**
	 * Font family stack for a font choice.
	 *
	 * @param string $choice Font key.
	 * @return string
	 */
	public static function font_stack( $choice ) {
		switch ( $choice ) {
			case 'vazir':
				return '"Vazirmatn", system-ui, sans-serif';
			case 'cormorant':
				return '"Cormorant Garamond", "Vazirmatn", serif';
			case 'system':
				return 'system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif';
			case 'custom':
				$family = trim( self::opt( 'font_custom_family' ) );

				if ( '' !== $family ) {
					return '"' . str_replace( '"', '', $family ) . '", "Vazirmatn", sans-serif';
				}

				return '"Vazirmatn", system-ui, sans-serif';
			default:
				return '';
		}
	}

	/**
	 * The generated CSS.
	 *
	 * @return string
	 */
	public static function css() {
		$root  = array();
		$dark  = array();
		$light = array();
		$extra = array();

		// ---- Shared colours ------------------------------------------------
		$accent = self::opt( 'accent' );

		if ( $accent ) {
			$root[] = '--accent:' . $accent;
			$root[] = '--ring:' . $accent;
		}

		$accent_fg = self::opt( 'accent_fg' );

		if ( $accent_fg ) {
			$root[] = '--accent-foreground:' . $accent_fg;
		}

		foreach ( array(
			'color_sale'    => '--sale',
			'color_success' => '--success',
			'color_warning' => '--warning',
			'color_error'   => '--destructive',
			'color_link'    => '--link',
			'button_bg'     => '--primary',
			'button_fg'     => '--primary-foreground',
		) as $key => $variable ) {
			$value = self::opt( $key );

			if ( $value ) {
				$root[] = $variable . ':' . $value;
			}
		}

		$border = self::opt( 'color_border' );

		if ( $border ) {
			$root[] = '--border:' . $border;
			$root[] = '--input:' . $border;
			$root[] = '--glass-border:' . $border;
		}

		// ---- Dark palette (default look) -----------------------------------
		foreach ( array(
			'theme_dark_bg'   => array( '--background', '--stage' ),
			'theme_dark_card' => array( '--card', '--muted', '--glass' ),
			'theme_dark_text' => array( '--foreground', '--on-overlay' ),
		) as $key => $variables ) {
			$value = self::opt( $key );

			if ( $value ) {
				foreach ( $variables as $variable ) {
					$dark[] = $variable . ':' . $value;
				}
			}
		}

		// ---- Light palette --------------------------------------------------
		// `--on-overlay` stays light in both modes: overlay panels (search,
		// lightbox, quick view scrim) always paint on a dark layer.
		foreach ( array(
			'theme_light_bg'   => array( '--background', '--stage' ),
			'theme_light_card' => array( '--card', '--muted', '--glass' ),
			'theme_light_text' => array( '--foreground' ),
		) as $key => $variables ) {
			$value = self::opt( $key );

			if ( $value ) {
				foreach ( $variables as $variable ) {
					$light[] = $variable . ':' . $value;
				}
			}
		}

		// ---- Typography -----------------------------------------------------
		$body_font = self::font_stack( self::opt( 'font_body' ) );

		if ( $body_font ) {
			$root[] = '--font-sans:' . $body_font;
		}

		$heading_font = self::font_stack( self::opt( 'font_heading' ) );

		if ( $heading_font ) {
			$root[] = '--font-display:' . $heading_font;
			$extra[] = 'h1,h2,h3,h4,h5,h6{font-family:var(--font-display)}';
		}

		$price_font = self::font_stack( self::opt( 'font_price' ) );

		if ( $price_font ) {
			$root[] = '--font-price:' . $price_font;
			$extra[] = '.price,.moon-price,.moon-card__price,.woocommerce-Price-amount{font-family:var(--font-price)}';
		}

		$root[] = '--body-weight:' . ( self::opt( 'body_weight' ) ? self::opt( 'body_weight' ) : '400' );
		$root[] = '--heading-weight:' . ( self::opt( 'heading_weight' ) ? self::opt( 'heading_weight' ) : '400' );

		$line_height = (int) self::opt( 'line_height', '175' );

		if ( $line_height >= 120 && $line_height <= 220 ) {
			$root[]  = '--line-height:' . ( $line_height / 100 );
			$extra[] = 'body{line-height:var(--line-height)}';
		}

		$tracking = self::opt( 'letter_spacing' );

		if ( '' !== $tracking ) {
			$root[]  = '--tracking:' . max( 0, min( 3, (int) $tracking ) ) . 'px';
			$extra[] = 'body{letter-spacing:var(--tracking)}';
		}

		$heading_scale = (int) self::opt( 'heading_scale', '100' );

		if ( 100 !== $heading_scale && $heading_scale >= 85 && $heading_scale <= 125 ) {
			$root[] = '--heading-scale:' . ( $heading_scale / 100 );
		}

		$font_size = (int) self::opt( 'font_size', '100' );

		if ( 100 !== $font_size && $font_size >= 85 && $font_size <= 120 ) {
			$extra[] = 'html{font-size:' . $font_size . '%}';
		}

		$mobile_scale = (int) self::opt( 'mobile_scale', '100' );

		if ( 100 !== $mobile_scale && $mobile_scale >= 85 && $mobile_scale <= 115 ) {
			$extra[] = '@media (max-width:767px){html{font-size:' . $mobile_scale . '%}}';
		}

		// ---- Layout ----------------------------------------------------------
		$container = (int) self::opt( 'container_width', '1360' );

		if ( $container && 1360 !== $container ) {
			$root[]  = '--container:' . $container . 'px';
			$extra[] = '.moon-container{max-width:var(--container)}';
		}

		$section_gap = (int) self::opt( 'section_gap', '96' );

		if ( $section_gap && 96 !== $section_gap ) {
			$root[]  = '--section-space:' . $section_gap . 'px';
			$extra[] = '.moon-home section{padding-block:var(--section-space)}';
		}

		$radius    = self::opt( 'radius', '4' );
		$radius_sm = self::opt( 'radius_sm', '2' );

		if ( '' !== $radius ) {
			$root[] = '--radius:' . max( 0, min( 40, (int) $radius ) ) . 'px';
		}

		if ( '' !== $radius_sm ) {
			$root[] = '--radius-sm:' . max( 0, min( 24, (int) $radius_sm ) ) . 'px';
		}

		$shadow = self::opt( 'shadow', 'soft' );

		if ( 'none' === $shadow ) {
			$root[] = '--shadow-soft:none';
		} elseif ( 'strong' === $shadow ) {
			$root[] = '--shadow-soft:0 36px 80px -28px rgba(0,0,0,.75)';
		}

		$border_width = (int) self::opt( 'border_width', '1' );

		if ( 1 !== $border_width ) {
			$root[]  = '--border-w:' . max( 0, min( 3, $border_width ) ) . 'px';
			$extra[] = '.moon-card,.moon-panel,.moon-widget,input,select,textarea{border-width:var(--border-w)}';
		}

		$button_height = (int) self::opt( 'button_height', '48' );

		if ( $button_height && 48 !== $button_height ) {
			$root[]  = '--btn-h:' . max( 36, min( 64, $button_height ) ) . 'px';
			$extra[] = '.moon-btn{min-height:var(--btn-h)}';
		}

		$input_height = (int) self::opt( 'input_height', '46' );

		if ( $input_height && 46 !== $input_height ) {
			$root[]  = '--input-h:' . max( 36, min( 64, $input_height ) ) . 'px';
			$extra[] = '.moon-field input,.moon-field select,.moon-field textarea{min-height:var(--input-h)}';
		}

		$header_height = (int) self::opt( 'header_height', '72' );

		if ( $header_height && 72 !== $header_height ) {
			$root[]  = '--header-h:' . max( 52, min( 120, $header_height ) ) . 'px';
			$extra[] = '.moon-header{--header-height:var(--header-h)}';
		}

		$header_bg = self::opt( 'header_bg' );

		if ( $header_bg ) {
			$root[] = '--header-bg:' . $header_bg;
		}

		$header_fg = self::opt( 'header_fg' );

		if ( $header_fg ) {
			$root[] = '--header-fg:' . $header_fg;
		}

		$topbar_bg = self::opt( 'topbar_bg' );

		if ( $topbar_bg ) {
			$root[] = '--topbar-bg:' . $topbar_bg;
		}

		$topbar_fg = self::opt( 'topbar_fg' );

		if ( $topbar_fg ) {
			$root[] = '--topbar-fg:' . $topbar_fg;
		}

		$css = '';

		if ( $root ) {
			$css .= ':root{' . implode( ';', $root ) . '}';
		}

		if ( $dark ) {
			$css .= ':root,' . 'html.moon-dark{' . implode( ';', $dark ) . '}';
		}

		if ( $light ) {
			$css .= 'html.moon-light{' . implode( ';', $light ) . '}';
		}

		// ---- Motion ----------------------------------------------------------
		if ( ! self::opt( 'animations', '1' ) ) {
			$css .= '*,*::before,*::after{animation-duration:.001ms!important;animation-iteration-count:1!important;transition-duration:.001ms!important;scroll-behavior:auto!important}';
		}

		if ( ! self::opt( 'anim_reveal', '1' ) ) {
			$css .= '[data-moon-reveal]{opacity:1!important;transform:none!important}';
		}

		// ---- Typography scale ------------------------------------------------
		$css .= 'body{font-weight:var(--body-weight,400)}';
		$css .= 'h1,h2,h3,h4,h5,h6{font-weight:var(--heading-weight,300)}';
		$css .= '.moon-section-title__title{font-size:calc(clamp(1.75rem,4vw,3rem) * var(--heading-scale,1))}';
		$css .= 'h1{font-size:calc(clamp(2rem,4.5vw,3.4rem) * var(--heading-scale,1))}';
		$css .= 'h2{font-size:calc(clamp(1.6rem,3.2vw,2.4rem) * var(--heading-scale,1))}';
		$css .= 'h3{font-size:calc(clamp(1.25rem,2.2vw,1.7rem) * var(--heading-scale,1))}';

		if ( self::opt( 'color_link' ) ) {
			$css .= '.moon-content a,.woocommerce-info a,.moon-auth__alt a{color:var(--link)}';
		}

		if ( self::opt( 'color_sale' ) ) {
			$css .= '.moon-badge--sale,.moon-card__badge--sale,.price ins,.moon-price ins,.onsale{color:var(--sale)}';
		}

		if ( self::opt( 'color_success' ) ) {
			$css .= '.moon-stock--in,.woocommerce-message,.moon-note--ok{color:var(--success)}';
		}

		if ( $extra ) {
			$css .= implode( '', $extra );
		}

		// ---- Brand logos -----------------------------------------------------
		if ( self::opt( 'logo_light_id' ) || self::opt( 'logo_dark_id' ) ) {
			$css .= 'html.moon-light .moon-brand__img--dark,html.moon-dark .moon-brand__img--light{display:none}';
		}

		if ( self::opt( 'logo_mobile_id' ) ) {
			$css .= '@media(min-width:768px){.moon-brand__img--mobile{display:none}}';
			$css .= '@media(max-width:767px){.moon-brand__img--desktop{display:none}}';
		}

		// Custom CSS from the panel is written last so it can override anything.
		$custom = (string) Moon_Settings::get( 'custom_css' );

		if ( '' !== trim( $custom ) ) {
			$css .= trim( $custom );
		}

		/**
		 * Filter the generated token CSS.
		 *
		 * @param string $css CSS.
		 */
		return apply_filters( 'moon_tokens_css', $css );
	}

	/**
	 * Attach the tokens to the base stylesheet.
	 */
	public static function styles() {
		$css = self::css();

		if ( '' === $css ) {
			return;
		}

		wp_register_style( 'moon-tokens', false, array( 'moon-base' ), MOON_THEME_VERSION );
		wp_enqueue_style( 'moon-tokens' );
		wp_add_inline_style( 'moon-tokens', $css );

		// A custom font file is registered only when one was uploaded, so no
		// extra request happens on a site that uses the bundled families.
		$font_url = esc_url_raw( (string) Moon_Settings::get( 'font_custom_url' ) );

		if ( $font_url && trim( (string) Moon_Settings::get( 'font_custom_family' ) ) ) {
			$family = str_replace( '"', '', (string) Moon_Settings::get( 'font_custom_family' ) );
			wp_add_inline_style(
				'moon-tokens',
				'@font-face{font-family:"' . $family . '";src:url("' . $font_url . '") format("woff2");font-display:swap;font-weight:100 900;font-style:normal}'
			);
		}
	}

	/**
	 * Body classes that layout and feature choices rely on.
	 *
	 * @param array $classes Classes.
	 * @return array
	 */
	public static function body_class( $classes ) {
		$card_style = Moon_Settings::get( 'card_style', 'flat' );

		if ( $card_style ) {
			$classes[] = 'moon-card-style-' . sanitize_html_class( $card_style );
		}

		$classes[] = 'moon-cards-' . sanitize_html_class( Moon_Settings::get( 'card_style', 'default' ) );
		$classes[] = 'moon-ratio-' . sanitize_html_class( Moon_Settings::get( 'image_ratio', 'portrait' ) );
		$classes[] = 'moon-header-layout-' . sanitize_html_class( Moon_Settings::get( 'header_layout', 'glass' ) );

		if ( Moon_Settings::get( 'header_transparent' ) && is_front_page() ) {
			$classes[] = 'moon-header-transparent';
		}

		if ( Moon_Settings::get( 'header_hide_scroll' ) ) {
			$classes[] = 'moon-header-hide-on-scroll';
		}

		if ( ! Moon_Settings::get( 'animations', '1' ) ) {
			$classes[] = 'moon-anim-off';
		}

		if ( ! Moon_Settings::get( 'anim_hover', '1' ) ) {
			$classes[] = 'moon-hover-off';
		}

		if ( is_singular( 'product' ) ) {
			$classes[] = 'moon-gallery-' . sanitize_html_class( Moon_Settings::get( 'product_gallery', 'thumbs-bottom' ) );
		}

		$footer_cols = Moon_Settings::get( 'footer_mobile_cols', '1' );
		$classes[]   = 'moon-footer-cols-' . sanitize_html_class( (string) $footer_cols );

		// Where the shop filters live (sidebar, drawer, or hidden).
		$filters   = Moon_Settings::get( 'shop_filters', 'sidebar' );
		$classes[] = 'moon-filters-' . sanitize_html_class( (string) $filters );

		if ( Moon_Settings::get( 'shop_view_toggle', '1' ) ) {
			$classes[] = 'moon-has-view-toggle';
		}

		return $classes;
	}

	/**
	 * Custom head code and the font preload.
	 */
	public static function head() {
		$favicon = (int) self::opt( 'favicon_id' );

		if ( $favicon ) {
			$url = wp_get_attachment_image_url( $favicon, 'full' );

			if ( $url ) {
				echo '<link rel="icon" href="' . esc_url( $url ) . '">' . "\n";
			}
		}

		if ( Moon_Settings::get( 'perf_preload_fonts', '1' ) ) {
			$font = MOON_THEME_URI . '/assets/fonts/vazirmatn-arabic-normal-100_900.woff2';

			echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		}

		$head = (string) Moon_Settings::get( 'custom_head' );

		if ( '' !== trim( $head ) ) {
			echo $head . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- administrator-provided, capability checked on save.
		}
	}

	/**
	 * Custom footer code.
	 */
	public static function footer() {
		$footer = (string) Moon_Settings::get( 'custom_footer' );

		if ( '' !== trim( $footer ) ) {
			echo $footer . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- administrator-provided, capability checked on save.
		}
	}
}
