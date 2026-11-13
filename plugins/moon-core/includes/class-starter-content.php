<?php
/**
 * MOON starter content.
 *
 * Builds the MOON homepage from real Elementor containers and MOON widgets,
 * imports the bundled photography into the media library and — only when asked —
 * creates a small demo catalogue so the store widgets have something to show.
 *
 * Everything is idempotent: running it twice updates the same page, menu and
 * products instead of duplicating them. Demo products are ordinary WooCommerce
 * products and can be deleted from Products at any time.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Starter_Content {

	private const MEDIA_OPTION = 'moon_core_starter_media';
	private const PAGE_OPTION  = 'moon_core_home_page';
	private const FLAG_OPTION  = 'moon_core_starter_imported';

	/**
	 * Import the MOON starter site.
	 *
	 * @param array<string,mixed> $args {
	 *     @type bool $products Create the demo catalogue (default true).
	 *     @type bool $pages    Create/refresh the homepage and menu (default true).
	 * }
	 * @return array<string,mixed> Report of what happened.
	 */
	public static function import( array $args = array() ): array {
		$args = wp_parse_args(
			$args,
			array(
				'products' => true,
				'pages'    => true,
			)
		);

		$report = array(
			'media'    => 0,
			'homepage' => 0,
			'products' => 0,
			'menu'     => 0,
		);

		$media         = self::import_media();
		$report['media'] = count( $media );

		if ( $args['products'] && class_exists( 'WooCommerce' ) ) {
			$report['products'] = self::import_products( $media );
		}

		if ( $args['pages'] ) {
			$report['homepage'] = self::build_homepage( $media );
			$report['menu']     = self::build_menus();
		}

		self::configure_store();

		update_option(
			self::FLAG_OPTION,
			array(
				'time'    => time(),
				'report'  => $report,
				'version' => MOON_CORE_VERSION,
			)
		);

		return $report;
	}

	/* ----------------------------------------------------------------- Media */

	/**
	 * Bundled photographs, imported once and reused afterwards.
	 *
	 * @return array<string,int> File name => attachment ID.
	 */
	public static function import_media(): array {
		$map     = get_option( self::MEDIA_OPTION, array() );
		$map     = is_array( $map ) ? $map : array();
		$bundled = array(
			'hero.jpg'      => __( 'MOON hero — coat in a dark studio', 'moon-core' ),
			'campaign.jpg'  => __( 'MOON campaign — desert at sunset', 'moon-core' ),
			'women.jpg'     => __( 'MOON women — ivory silk shirt', 'moon-core' ),
			'men.jpg'       => __( 'MOON men — charcoal coat', 'moon-core' ),
			'atelier.jpg'   => __( 'MOON atelier — cutting fabric', 'moon-core' ),
			'outfit-1.jpg'  => __( 'MOON outfit 01', 'moon-core' ),
			'outfit-2.jpg'  => __( 'MOON outfit 02', 'moon-core' ),
			'outfit-3.jpg'  => __( 'MOON outfit 03', 'moon-core' ),
			'outfit-4.jpg'  => __( 'MOON outfit 04', 'moon-core' ),
			'outfit-5.jpg'  => __( 'MOON outfit 05', 'moon-core' ),
			'g-coat.png'    => __( 'MOON garment — wool coat', 'moon-core' ),
			'g-jacket.png'  => __( 'MOON garment — cotton jacket', 'moon-core' ),
			'g-shirt.png'   => __( 'MOON garment — silk shirt', 'moon-core' ),
			'g-hoodie.png'  => __( 'MOON garment — hoodie', 'moon-core' ),
			'g-sweat.png'   => __( 'MOON garment — fleece sweat', 'moon-core' ),
		);

		require_once ABSPATH . 'wp-admin/includes/image.php';

		foreach ( $bundled as $file => $title ) {
			$existing = isset( $map[ $file ] ) ? (int) $map[ $file ] : 0;

			if ( $existing && get_post( $existing ) ) {
				continue;
			}

			$path = MOON_CORE_DIR . 'assets/demo/' . $file;

			if ( ! is_readable( $path ) ) {
				continue;
			}

			$upload = wp_upload_bits( $file, null, (string) file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local bundled asset.

			if ( ! empty( $upload['error'] ) ) {
				continue;
			}

			$filetype   = wp_check_filetype( $upload['file'] );
			$attachment = array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => $title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attachment_id = wp_insert_attachment( $attachment, $upload['file'] );

			if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
				continue;
			}

			wp_update_attachment_metadata(
				$attachment_id,
				wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
			);

			$map[ $file ] = (int) $attachment_id;
		}

		update_option( self::MEDIA_OPTION, $map );

		return array_map( 'intval', $map );
	}

	/**
	 * Elementor media control value for an imported file.
	 *
	 * @param array<string,int> $media Media map.
	 * @param string            $file  File name.
	 */
	private static function m( array $media, string $file ): array {
		$id = isset( $media[ $file ] ) ? (int) $media[ $file ] : 0;

		if ( ! $id ) {
			return array( 'id' => 0, 'url' => '' );
		}

		$url = wp_get_attachment_image_url( $id, 'full' );

		return array(
			'id'  => $id,
			'url' => $url ? $url : '',
		);
	}

	/* -------------------------------------------------------------- Homepage */

	/**
	 * Create or refresh the Elementor homepage and use it as the front page.
	 *
	 * @param array<string,int> $media Media map.
	 */
	private static function build_homepage( array $media ): int {
		$page_id   = (int) get_option( self::PAGE_OPTION, 0 );
		$page_data = array(
			'post_title'   => __( 'Home', 'moon-core' ),
			'post_name'    => 'home',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		);

		if ( $page_id && get_post( $page_id ) ) {
			$page_data['ID'] = $page_id;
			$page_id         = (int) wp_update_post( $page_data );
		} else {
			$page_id = (int) wp_insert_post( $page_data );
		}

		if ( ! $page_id ) {
			return 0;
		}

		update_option( self::PAGE_OPTION, $page_id );

		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
		update_post_meta( $page_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );

		$data = wp_json_encode( self::homepage_elements( $media ) );
		update_post_meta( $page_id, '_elementor_data', wp_slash( (string) $data ) );

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );

		// Elementor caches rendered CSS per post; drop it so edits appear at once.
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		return $page_id;
	}

	/**
	 * Elementor element IDs are short hex strings.
	 */
	private static function eid(): string {
		return substr( md5( uniqid( '', true ) ), 0, 7 );
	}

	/**
	 * One Elementor container with a widget inside.
	 *
	 * @param array<string,mixed> $settings Container settings.
	 * @param array<int,array<string,mixed>> $widgets Widget elements.
	 */
	private static function container( array $settings, array $widgets ): array {
		return array(
			'id'       => self::eid(),
			'elType'   => 'container',
			'settings' => array_merge(
				array(
					'content_width' => 'boxed',
					'flex_direction' => 'column',
					'flex_gap'      => array( 'column' => '0', 'row' => '0', 'isLinked' => true, 'unit' => 'px', 'size' => 0 ),
					'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
				),
				$settings
			),
			'elements' => $widgets,
		);
	}

	/**
	 * One widget element.
	 *
	 * @param string              $type     Elementor widget name (moon-…).
	 * @param array<string,mixed> $settings Widget settings.
	 */
	private static function widget( string $type, array $settings ): array {
		return array(
			'id'         => self::eid(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	/**
	 * The complete homepage layout. Every section is a separate editable widget.
	 *
	 * @param array<string,int> $media Media map.
	 * @return array<int,array<string,mixed>>
	 */
	private static function homepage_elements( array $media ): array {
		$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		$hero = self::m( $media, 'hero.jpg' );

		$rack = array();
		foreach (
			array(
				array( 'g-coat.png', __( 'The Coat', 'moon-core' ), __( 'Double-faced wool — €480', 'moon-core' ), __( 'Unlined, cut long and narrow, finished by hand.', 'moon-core' ) ),
				array( 'g-jacket.png', __( 'The Jacket', 'moon-core' ), __( 'Cotton twill — €320', 'moon-core' ), __( 'A boxy workwear cut with a soft shoulder.', 'moon-core' ) ),
				array( 'g-shirt.png', __( 'The Shirt', 'moon-core' ), __( 'Sandwashed silk — €210', 'moon-core' ), __( 'Mother-of-pearl buttons, French seams.', 'moon-core' ) ),
				array( 'g-hoodie.png', __( 'The Hoodie', 'moon-core' ), __( 'Loopback cotton — €180', 'moon-core' ), __( 'Heavy loopback, raw seams, unisex.', 'moon-core' ) ),
				array( 'g-sweat.png', __( 'The Sweat', 'moon-core' ), __( 'Brushed fleece — €160', 'moon-core' ), __( 'Brushed fleece in a warm oatmeal tone.', 'moon-core' ) ),
			) as $garment
		) {
			$rack[] = array(
				'_id'         => self::eid(),
				'image'       => self::m( $media, $garment[0] ),
				'name'        => $garment[1],
				'meta'        => $garment[2],
				'description' => $garment[3],
				'link'        => array( 'url' => $shop, 'is_external' => '', 'nofollow' => '' ),
			);
		}

		$outfits = array();
		$looks   = array(
			array( 'outfit-1.jpg', __( 'Look 01 — The Ivory Set', 'moon-core' ), __( 'Ivory / Camel', 'moon-core' ), '€640', __( 'Silk shirt, wide wool trouser, leather belt.', 'moon-core' ) ),
			array( 'outfit-2.jpg', __( 'Look 02 — Charcoal Tailoring', 'moon-core' ), __( 'Charcoal', 'moon-core' ), '€780', __( 'Oversized blazer over a fine knit.', 'moon-core' ) ),
			array( 'outfit-3.jpg', __( 'Look 03 — Camel Coat', 'moon-core' ), __( 'Camel / Black', 'moon-core' ), '€920', __( 'Long coat, silk shirt, tailored trouser.', 'moon-core' ) ),
			array( 'outfit-4.jpg', __( 'Look 04 — Evening Silk', 'moon-core' ), __( 'Black', 'moon-core' ), '€560', __( 'Bias-cut silk with a narrow belt.', 'moon-core' ) ),
			array( 'outfit-5.jpg', __( 'Look 05 — Knit & Leather', 'moon-core' ), __( 'Oat / Black', 'moon-core' ), '€480', __( 'Cropped knit over leather tailoring.', 'moon-core' ) ),
		);
		foreach ( $looks as $look ) {
			$outfits[] = array(
				'_id'    => self::eid(),
				'image'  => self::m( $media, $look[0] ),
				'title'  => $look[1],
				'color'  => $look[2],
				'price'  => $look[3],
				'note'   => $look[4],
				'link'   => array( 'url' => $shop, 'is_external' => '', 'nofollow' => '' ),
			);
		}

		$women = get_term_by( 'slug', 'women', 'product_cat' );
		$men   = get_term_by( 'slug', 'men', 'product_cat' );

		return array(
			self::container(
				array( 'content_width' => 'full', 'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ) ),
				array(
					self::widget(
						'moon-hero',
						array(
							'eyebrow'        => __( 'Autumn / Winter — The Atelier Edit', 'moon-core' ),
							'title'          => __( 'Quiet luxury,', 'moon-core' ),
							'title_accent'   => __( 'loudly cut', 'moon-core' ),
							'description'    => __( 'Tailored coats, silk shirting and the quiet architecture of a wardrobe built to outlast the season.', 'moon-core' ),
							'primary_text'   => __( 'Shop the collection', 'moon-core' ),
							'primary_link'   => array( 'url' => $shop, 'is_external' => '', 'nofollow' => '' ),
							'secondary_text' => __( 'The atelier story', 'moon-core' ),
							'secondary_link' => array( 'url' => '#moon-story', 'is_external' => '', 'nofollow' => '' ),
							'side_label'     => __( 'Made in Portugal', 'moon-core' ),
							'image'          => $hero,
							'layout'         => 'overlay',
							'height'         => array( 'unit' => 'vh', 'size' => 92 ),
							'priority'       => 'yes',
							'reveal'         => 'none',
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section', 'css_id' => 'moon-rack' ),
				array(
					self::widget(
						'moon-clothing-rack',
						array(
							'heading_eyebrow'     => __( 'The rail', 'moon-core' ),
							'heading_title'       => __( 'Six pieces, one rail', 'moon-core' ),
							'heading_description' => __( 'Hover to let a garment move. Select to lift it from the rail.', 'moon-core' ),
							'garments'            => $rack,
							'layout'              => 'side',
							'garment_width'       => array( 'unit' => 'px', 'size' => 150 ),
							'show_hanger'         => 'yes',
							'swing'               => 'yes',
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section' ),
				array(
					self::widget(
						'moon-outfit-studio',
						array(
							'heading_eyebrow'     => __( 'Outfit studio', 'moon-core' ),
							'heading_title'       => __( 'Build the look', 'moon-core' ),
							'heading_description' => __( 'Five ways to wear the season, photographed on the same model in the same light.', 'moon-core' ),
							'outfits'             => $outfits,
							'transition'          => 'mask',
							'thumbnails'          => 'side',
							'show_arrows'         => 'yes',
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section' ),
				array(
					self::widget(
						'moon-category-cards',
						array(
							'heading_eyebrow'     => __( 'Shop by world', 'moon-core' ),
							'heading_title'       => __( 'Wardrobe directions', 'moon-core' ),
							'heading_description' => '',
							'cards'               => array(
								array(
									'_id'      => self::eid(),
									'image'    => self::m( $media, 'women.jpg' ),
									'title'    => __( 'Women', 'moon-core' ),
									'subtitle' => __( 'The winter edit', 'moon-core' ),
									'link'     => array( 'url' => $women ? get_term_link( $women ) : $shop ),
									'wide'     => '',
								),
								array(
									'_id'      => self::eid(),
									'image'    => self::m( $media, 'men.jpg' ),
									'title'    => __( 'Men', 'moon-core' ),
									'subtitle' => __( 'Tailoring & knitwear', 'moon-core' ),
									'link'     => array( 'url' => $men ? get_term_link( $men ) : $shop ),
									'wide'     => '',
								),
							),
							'columns'             => '2',
							'ratio'               => array( 'unit' => 'px', 'size' => 1.2 ),
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section' ),
				array(
					self::widget(
						'moon-product-grid',
						array(
							'heading_eyebrow'     => __( 'New in', 'moon-core' ),
							'heading_title'       => __( 'The latest pieces', 'moon-core' ),
							'heading_description' => __( 'Everything is in stock in the studio. Orders ship within 48 hours.', 'moon-core' ),
							'source'              => 'latest',
							'limit'               => 4,
							'orderby'             => 'date',
							'order'               => 'DESC',
							'columns'             => '4',
							'show_category'       => 'yes',
							'show_quick_view'     => 'yes',
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section' ),
				array(
					self::widget(
						'moon-editorial-banner',
						array(
							'heading_eyebrow'     => __( 'Campaign 02', 'moon-core' ),
							'heading_title'       => __( 'The desert hours', 'moon-core' ),
							'heading_description' => __( 'Sun-bleached tailoring photographed at first light. A study in warmth, movement and restraint.', 'moon-core' ),
							'image'               => self::m( $media, 'campaign.jpg' ),
							'image_size'          => 'moon-editorial',
							'button_text'         => __( 'Discover the campaign', 'moon-core' ),
							'button_link'         => array( 'url' => $shop, 'is_external' => '', 'nofollow' => '' ),
							'card_position'       => 'start',
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section', 'css_id' => 'moon-story' ),
				array(
					self::widget(
						'moon-brand-story',
						array(
							'heading_eyebrow'     => __( 'The atelier', 'moon-core' ),
							'heading_title'       => __( "Cut slowly,\nworn for years", 'moon-core' ),
							'heading_description' => __( 'Every MOON piece begins as a paper pattern on a long oak table. We work with two family-run mills in Portugal, cut in small runs and finish each garment by hand.', 'moon-core' ),
							'image_main'          => self::m( $media, 'women.jpg' ),
							'image_detail'        => self::m( $media, 'atelier.jpg' ),
							'image_position'      => 'start',
							'caption'             => __( 'Atelier, Porto — sample room', 'moon-core' ),
							'stats'               => array(
								array( '_id' => self::eid(), 'value' => '2', 'label' => __( 'Family mills', 'moon-core' ) ),
								array( '_id' => self::eid(), 'value' => '100%', 'label' => __( 'Traceable wool', 'moon-core' ) ),
								array( '_id' => self::eid(), 'value' => '48', 'label' => __( 'Pieces per season', 'moon-core' ) ),
							),
							'button_text'         => __( 'Read the journal', 'moon-core' ),
							'button_link'         => array( 'url' => home_url( '/journal/' ), 'is_external' => '', 'nofollow' => '' ),
							'signature'           => __( 'Moon Atelier', 'moon-core' ),
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section moon-section--tight' ),
				array(
					self::widget(
						'moon-benefits',
						array(
							'items'   => array(
								array( '_id' => self::eid(), 'icon' => 'bag', 'title' => __( 'Complimentary shipping', 'moon-core' ), 'text' => __( 'On all orders over €150, worldwide.', 'moon-core' ) ),
								array( '_id' => self::eid(), 'icon' => 'arrow-up', 'title' => __( '30-day returns', 'moon-core' ), 'text' => __( 'Post it back free, keep the packaging.', 'moon-core' ) ),
								array( '_id' => self::eid(), 'icon' => 'heart', 'title' => __( 'Made in small runs', 'moon-core' ), 'text' => __( 'Family mills, traceable wool, no overproduction.', 'moon-core' ) ),
								array( '_id' => self::eid(), 'icon' => 'check', 'title' => __( 'Personal styling', 'moon-core' ), 'text' => __( 'The studio answers within a day.', 'moon-core' ) ),
							),
							'columns' => '4',
							'style'   => 'dividers',
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section' ),
				array(
					self::widget(
						'moon-testimonials',
						array(
							'heading_eyebrow' => __( 'Worn & reviewed', 'moon-core' ),
							'heading_title'   => __( 'Words from the wardrobe', 'moon-core' ),
							'layout'          => 'slider',
							'show_rating'     => 'yes',
							'items'           => array(
								array( '_id' => self::eid(), 'quote' => __( 'The coat arrived in three days and feels like something my mother would have kept for thirty years.', 'moon-core' ), 'author' => __( 'Elise M.', 'moon-core' ), 'role' => __( 'Verified purchase — Paris', 'moon-core' ), 'rating' => array( 'size' => 5 ) ),
								array( '_id' => self::eid(), 'quote' => __( 'I own three of the silk shirts. Nothing else in my wardrobe gets this much wear.', 'moon-core' ), 'author' => __( 'Nadia K.', 'moon-core' ), 'role' => __( 'Verified purchase — Amsterdam', 'moon-core' ), 'rating' => array( 'size' => 5 ) ),
								array( '_id' => self::eid(), 'quote' => __( 'Small runs, honest fabric, and a studio that answers emails like a person.', 'moon-core' ), 'author' => __( 'Jonas R.', 'moon-core' ), 'role' => __( 'Verified purchase — Berlin', 'moon-core' ), 'rating' => array( 'size' => 4 ) ),
							),
						)
					),
				)
			),

			self::container(
				array( 'css_classes' => 'moon-section' ),
				array(
					self::widget(
						'moon-newsletter',
						array(
							'heading_eyebrow'     => __( 'The list', 'moon-core' ),
							'heading_title'       => __( 'Be first to the cutting table', 'moon-core' ),
							'heading_description' => __( 'New drops, restocks and studio notes. One considered email a month.', 'moon-core' ),
							'layout'              => 'panel',
							'background_image'    => self::m( $media, 'campaign.jpg' ),
							'button_text'         => __( 'Subscribe', 'moon-core' ),
							'note'                => __( 'No noise. Unsubscribe in one click.', 'moon-core' ),
						)
					),
				)
			),
		);
	}

	/* --------------------------------------------------------------- Products */

	/**
	 * Demo catalogue: categories, a variable product and simple products.
	 *
	 * @param array<string,int> $media Media map.
	 */
	private static function import_products( array $media ): int {
		$categories = array(
			'women'       => __( 'Women', 'moon-core' ),
			'men'         => __( 'Men', 'moon-core' ),
			'accessories' => __( 'Accessories', 'moon-core' ),
		);

		$category_ids = array();
		foreach ( $categories as $slug => $name ) {
			$term = term_exists( $slug, 'product_cat' );

			if ( ! $term ) {
				$term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			}

			if ( is_wp_error( $term ) ) {
				continue;
			}

			$term_id                = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
			$category_ids[ $slug ] = $term_id;

			if ( ! empty( $media[ 'g-coat.png' ] ) && 'women' === $slug ) {
				update_term_meta( $term_id, 'thumbnail_id', (int) $media[ 'g-coat.png' ] );
			}
			if ( ! empty( $media[ 'g-jacket.png' ] ) && 'men' === $slug ) {
				update_term_meta( $term_id, 'thumbnail_id', (int) $media[ 'g-jacket.png' ] );
			}
			if ( ! empty( $media[ 'g-shirt.png' ] ) && 'accessories' === $slug ) {
				update_term_meta( $term_id, 'thumbnail_id', (int) $media[ 'g-shirt.png' ] );
			}
		}

		$simple = array(
			array(
				'name'  => __( 'Oversized Wool Coat', 'moon-core' ),
				'sku'   => 'MOON-COAT-01',
				'price' => '480',
				'sale'  => '',
				'cat'   => 'women',
				'image' => 'g-coat.png',
				'desc'  => __( 'Double-faced wool, unlined, cut long and narrow with a hand-finished hem.', 'moon-core' ),
				'stock' => 8,
			),
			array(
				'name'  => __( 'Cotton Twill Jacket', 'moon-core' ),
				'sku'   => 'MOON-JKT-02',
				'price' => '380',
				'sale'  => '299',
				'cat'   => 'men',
				'image' => 'g-jacket.png',
				'desc'  => __( 'Boxy workwear cut in brushed cotton twill, garment-dyed in charcoal.', 'moon-core' ),
				'stock' => 12,
			),
			array(
				'name'  => __( 'Sandwashed Silk Shirt', 'moon-core' ),
				'sku'   => 'MOON-SHT-03',
				'price' => '210',
				'sale'  => '',
				'cat'   => 'women',
				'image' => 'g-shirt.png',
				'desc'  => __( 'Sandwashed silk with mother-of-pearl buttons and French seams.', 'moon-core' ),
				'stock' => 20,
			),
			array(
				'name'  => __( 'Loopback Hoodie', 'moon-core' ),
				'sku'   => 'MOON-HOD-04',
				'price' => '180',
				'sale'  => '',
				'cat'   => 'men',
				'image' => 'g-hoodie.png',
				'desc'  => __( 'Heavy loopback cotton with raw seams. Unisex sizing.', 'moon-core' ),
				'stock' => 0,
			),
			array(
				'name'  => __( 'Brushed Fleece Sweat', 'moon-core' ),
				'sku'   => 'MOON-SWT-05',
				'price' => '160',
				'sale'  => '',
				'cat'   => 'men',
				'image' => 'g-sweat.png',
				'desc'  => __( 'Brushed fleece in a warm oatmeal tone, with a relaxed shoulder.', 'moon-core' ),
				'stock' => 15,
			),
		);

		$count = self::create_variable_product( $media, $category_ids );

		foreach ( $simple as $item ) {
			$existing_id = wc_get_product_id_by_sku( $item['sku'] );
			$product     = $existing_id ? wc_get_product( $existing_id ) : new \WC_Product_Simple();

			if ( ! $product ) {
				$product = new \WC_Product_Simple();
			}

			$product->set_name( $item['name'] );
			$product->set_sku( $item['sku'] );
			$product->set_status( 'publish' );
			$product->set_regular_price( $item['price'] );
			$product->set_sale_price( $item['sale'] );
			$product->set_description( $item['desc'] );
			$product->set_short_description( $item['desc'] );
			$product->set_manage_stock( true );
			$product->set_stock_quantity( (int) $item['stock'] );
			$product->set_stock_status( $item['stock'] > 0 ? 'instock' : 'outofstock' );
			$product->set_catalog_visibility( 'visible' );

			if ( isset( $media[ $item['image'] ] ) ) {
				$product->set_image_id( (int) $media[ $item['image'] ] );
			}

			if ( isset( $category_ids[ $item['cat'] ] ) ) {
				$product->set_category_ids( array( $category_ids[ $item['cat'] ] ) );
			}

			$product->save();
			$count++;
		}

		return $count;
	}

	/**
	 * A variable product with Size and Colour variations.
	 *
	 * @param array<string,int> $media        Media map.
	 * @param array<string,int> $category_ids Term IDs by slug.
	 */
	private static function create_variable_product( array $media, array $category_ids ): int {
		$sku    = 'MOON-SET-06';
		$exists = wc_get_product_id_by_sku( $sku );
		$product = $exists ? wc_get_product( $exists ) : null;

		if ( ! $product instanceof \WC_Product_Variable ) {
			$product = new \WC_Product_Variable();
		}

		$product->set_name( __( 'Atelier Silk Set', 'moon-core' ) );
		$product->set_sku( $sku );
		$product->set_status( 'publish' );
		$product->set_description( __( 'The ivory silk set from campaign 02. Shirt and wide trouser, sold together in two colourways.', 'moon-core' ) );
		$product->set_short_description( __( 'Silk shirt and wide trouser in ivory or black. Sizes S–L.', 'moon-core' ) );

		$attributes = array();
		foreach ( array( 'size' => array( 'S', 'M', 'L' ), 'colour' => array( 'Ivory', 'Black' ) ) as $name => $values ) {
			$attribute = new \WC_Product_Attribute();
			$attribute->set_id( 0 );
			$attribute->set_name( ucfirst( $name ) );
			$attribute->set_options( $values );
			$attribute->set_position( 0 );
			$attribute->set_visible( true );
			$attribute->set_variation( true );
			$attributes[] = $attribute;
		}
		$product->set_attributes( $attributes );

		if ( isset( $media['outfit-1.jpg'] ) ) {
			$product->set_image_id( (int) $media['outfit-1.jpg'] );
		}

		$gallery = array();
		foreach ( array( 'outfit-2.jpg', 'outfit-4.jpg' ) as $file ) {
			if ( isset( $media[ $file ] ) ) {
				$gallery[] = (int) $media[ $file ];
			}
		}
		$product->set_gallery_image_ids( $gallery );

		if ( isset( $category_ids['women'] ) ) {
			$product->set_category_ids( array( $category_ids['women'] ) );
		}

		$parent_id = $product->save();

		$existing_children = $parent_id ? get_children(
			array(
				'post_parent' => $parent_id,
				'post_type'   => 'product_variation',
				'post_status' => 'any',
				'fields'      => 'ids',
			)
		) : array();

		if ( ! empty( $existing_children ) ) {
			return 1;
		}

		$prices = array(
			'Ivory' => '420',
			'Black' => '440',
		);

		foreach ( array( 'S', 'M', 'L' ) as $size ) {
			foreach ( array( 'Ivory', 'Black' ) as $colour ) {
				$variation = new \WC_Product_Variation();
				$variation->set_parent_id( $parent_id );
				$variation->set_attributes(
					array(
						'Size'   => $size,
						'Colour' => $colour,
					)
				);
				$variation->set_regular_price( $prices[ $colour ] );
				$variation->set_manage_stock( true );
				$variation->set_stock_quantity( 'L' === $size ? 2 : 6 );
				$variation->set_stock_status( 'instock' );
				$variation->save();
			}
		}

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients( $parent_id );
		}
		\WC_Product_Variable::sync( $parent_id );

		return 1;
	}

	/* ------------------------------------------------------------------ Menus */

	/**
	 * Primary and footer menus wired to the theme locations.
	 */
	private static function build_menus(): int {
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations = is_array( $locations ) ? $locations : array();
		$count     = 0;

		$primary_items = array(
			array( 'label' => __( 'Shop', 'moon-core' ), 'url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ),
			array( 'label' => __( 'Women', 'moon-core' ), 'term' => 'women' ),
			array( 'label' => __( 'Men', 'moon-core' ), 'term' => 'men' ),
			array( 'label' => __( 'Lookbook', 'moon-core' ), 'url' => home_url( '/#moon-rack' ) ),
			array( 'label' => __( 'Journal', 'moon-core' ), 'url' => home_url( '/journal/' ) ),
		);

		$menu_id = self::menu( 'MOON Primary', $primary_items );

		if ( $menu_id ) {
			$locations['primary'] = $menu_id;
			$locations['mobile']  = $menu_id;
			$count++;
		}

		$footer_items = array(
			array( 'label' => __( 'Shop', 'moon-core' ), 'url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ),
			array( 'label' => __( 'My account', 'moon-core' ), 'url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) ),
			array( 'label' => __( 'Cart', 'moon-core' ), 'url' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ),
		);

		$footer_menu = self::menu( 'MOON Footer', $footer_items );

		if ( $footer_menu ) {
			$locations['footer'] = $footer_menu;
			$count++;
		}

		set_theme_mod( 'nav_menu_locations', $locations );

		return $count;
	}

	/**
	 * Create or refresh a menu and its items.
	 *
	 * @param string                             $name  Menu name.
	 * @param array<int,array<string,mixed>>     $items Items: label + url|term.
	 */
	private static function menu( string $name, array $items ): int {
		$menu = wp_get_nav_menu_object( $name );

		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				return 0;
			}
		} else {
			$menu_id = (int) $menu->term_id;
			$current = wp_get_nav_menu_items( $menu_id );

			foreach ( (array) $current as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}

		foreach ( $items as $item ) {
			$args = array(
				'menu-item-title'  => $item['label'],
				'menu-item-status' => 'publish',
			);

			if ( isset( $item['term'] ) ) {
				$term = get_term_by( 'slug', $item['term'], 'product_cat' );
				if ( ! $term ) {
					continue;
				}
				$args['menu-item-type']      = 'taxonomy';
				$args['menu-item-object']    = 'product_cat';
				$args['menu-item-object-id'] = (int) $term->term_id;
			} else {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = (string) $item['url'];
			}

			wp_update_nav_menu_item( $menu_id, 0, $args );
		}

		return (int) $menu_id;
	}

	/* ------------------------------------------------------------- Store setup */

	/**
	 * WooCommerce 8.3+ ships block Cart/Checkout pages. The MOON storefront styles
	 * the classic WooCommerce templates (tables, order review, notices), so the
	 * starter import switches these two pages to the native shortcodes once.
	 * Re-run manually with: wp eval 'MoonCore\Starter_Content::use_classic_store_pages();'
	 *
	 * @return bool Whether anything changed.
	 */
	public static function use_classic_store_pages(): bool {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return false;
		}

		$changed = false;
		$pages   = array(
			'cart'     => '[woocommerce_cart]',
			'checkout' => '[woocommerce_checkout]',
		);

		foreach ( $pages as $key => $shortcode ) {
			$page_id = (int) wc_get_page_id( $key );
			$page    = $page_id ? get_post( $page_id ) : null;

			if ( ! $page instanceof \WP_Post ) {
				continue;
			}

			if ( false === strpos( $page->post_content, 'woocommerce/' . $key ) && false === strpos( $page->post_content, 'wc-block-' ) ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $shortcode,
				)
			);

			$changed = true;
		}

		return $changed;
	}

	/**
	 * Store defaults that make the demo immediately usable (no payment methods,
	 * no credentials — those stay in WooCommerce settings).
	 */
	private static function configure_store(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! get_option( self::FLAG_OPTION ) ) {
			self::use_classic_store_pages();
		}

		// The demo catalogue is priced in euro; only switch the untouched default.
		if ( 'USD' === get_option( 'woocommerce_currency' ) && ! get_option( self::FLAG_OPTION ) ) {
			update_option( 'woocommerce_currency', 'EUR' );
		}

		$defaults = array(
			'woocommerce_currency'         => 'EUR',
			'woocommerce_default_country'  => 'PT:PT',
			'woocommerce_weight_unit'      => 'kg',
			'woocommerce_dimension_unit'   => 'cm',
			'woocommerce_enable_reviews'   => 'yes',
			'woocommerce_enable_guest_checkout' => 'yes',
			'woocommerce_calc_taxes'       => 'no',
			'woocommerce_placeholder_image' => isset( get_option( 'moon_core_starter_media', array() )['g-coat.png'] ) ? (int) get_option( 'moon_core_starter_media', array() )['g-coat.png'] : 0,
		);

		foreach ( $defaults as $key => $value ) {
			$current = get_option( $key, null );

			// Only fill values that were never set, so a store owner's choices win.
			if ( null === $current || '' === $current || 0 === $current ) {
				update_option( $key, $value );
			}
		}
	}
}
