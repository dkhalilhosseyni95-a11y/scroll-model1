<?php
/**
 * Lightweight SEO: meta description, Open Graph, JSON-LD and head clean-up.
 *
 * Stands down automatically when Yoast, Rank Math, SEOPress or All in One SEO
 * is active. Product and breadcrumb structured data come from WooCommerce.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a dedicated SEO plugin is handling the head.
 *
 * @return bool
 */
function moon_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Description for the current view.
 *
 * @return string
 */
function moon_seo_description() {
	$text = '';

	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post && ! empty( $post->post_excerpt ) ) {
			$text = $post->post_excerpt;
		} elseif ( $post ) {
			$text = $post->post_content;
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$text = term_description();
	} elseif ( is_front_page() || is_home() ) {
		$text = get_bloginfo( 'description' );
	}

	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( (string) $text ) ) ) );

	if ( '' === $text ) {
		$text = (string) get_bloginfo( 'description' );
	}

	if ( '' === $text ) {
		$text = (string) get_bloginfo( 'name' );
	}

	return wp_html_excerpt( $text, 160, '…' );
}

/**
 * Print description, Open Graph and Twitter tags.
 */
function moon_seo_meta() {
	if ( class_exists( 'Moon_Settings' ) && ! Moon_Settings::get( 'seo_meta', '1' ) ) {
		return;
	}

	if ( moon_seo_plugin_active() || is_404() ) {
		return;
	}

	$desc  = moon_seo_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
	$type  = is_singular( 'product' ) ? 'product' : ( is_singular( 'post' ) ? 'article' : 'website' );
	$image = '';

	if ( is_singular() && has_post_thumbnail() ) {
		$image = (string) get_the_post_thumbnail_url( null, 'large' );
	}

	if ( ! $image ) {
		$image = (string) get_site_icon_url( 512 );
	}

	if ( ! $image ) {
		$image = moon_asset( 'hero.jpg' );
	}

	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}

	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";

	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}

	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
}
add_action( 'wp_head', 'moon_seo_meta', 5 );

/**
 * Avoid a duplicated brand name in the document title.
 *
 * On the front page the title is the tagline (which already carries the brand);
 * elsewhere the site name is dropped when the page title contains it.
 *
 * @param array $parts Title parts.
 * @return array
 */
function moon_seo_document_title( $parts ) {
	$site = isset( $parts['site'] ) ? (string) $parts['site'] : (string) get_bloginfo( 'name' );

	if ( is_front_page() ) {
		$tagline = (string) get_bloginfo( 'description' );

		$parts['title'] = '' !== $tagline ? $tagline : $site;
		unset( $parts['site'], $parts['tagline'] );

		return $parts;
	}

	if ( '' !== $site && isset( $parts['title'] ) && false !== stripos( (string) $parts['title'], $site ) ) {
		unset( $parts['site'] );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'moon_seo_document_title' );

/**
 * Keep thin pages out of the index.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function moon_seo_robots( $robots ) {
	if ( moon_seo_plugin_active() ) {
		return $robots;
	}

	if ( is_search() || ( function_exists( 'is_account_page' ) && is_account_page() ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}

	return $robots;
}
add_filter( 'wp_robots', 'moon_seo_robots' );

/**
 * Organization + WebSite (sitelinks search box) JSON-LD on the homepage.
 */
function moon_seo_schema() {
	if ( class_exists( 'Moon_Settings' ) && ! Moon_Settings::get( 'seo_schema', '1' ) ) {
		return;
	}

	if ( moon_seo_plugin_active() || ! is_front_page() ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array(
			array(
				'@type' => 'Organization',
				'@id'   => home_url( '/#org' ),
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
				'logo'  => get_site_icon_url( 512 ) ? get_site_icon_url( 512 ) : null,
			),
			array(
				'@type'           => 'WebSite',
				'@id'             => home_url( '/#website' ),
				'url'             => home_url( '/' ),
				'name'            => get_bloginfo( 'name' ),
				'inLanguage'      => get_bloginfo( 'language' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => home_url( '/?s={search_term_string}&post_type=product' ),
					'query-input' => 'required name=search_term_string',
				),
			),
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'moon_seo_schema', 6 );

/**
 * Canonical URL for shop and taxonomy archives (core only prints it for singular pages).
 */
function moon_seo_archive_canonical() {
	if ( class_exists( 'Moon_Settings' ) && ! Moon_Settings::get( 'seo_archive_canonical', '1' ) ) {
		return;
	}

	if ( moon_seo_plugin_active() || is_singular() || is_search() || is_404() ) {
		return;
	}

	$url = '';

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$url = get_post_type_archive_link( 'product' );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$url = get_term_link( get_queried_object() );
	} elseif ( is_front_page() ) {
		$url = home_url( '/' );
	}

	if ( $url && ! is_wp_error( $url ) ) {
		$paged = (int) get_query_var( 'paged' );

		if ( $paged > 1 ) {
			$url = trailingslashit( $url ) . 'page/' . $paged . '/';
		}

		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'moon_seo_archive_canonical', 4 );
