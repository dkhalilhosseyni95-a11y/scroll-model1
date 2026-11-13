<?php
/**
 * Product resolution + queries for the WooCommerce widgets.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

trait Product {

	/**
	 * Source controls shared by single-product widgets.
	 *
	 * @param string $default_source current|specific.
	 */
	protected function product_source_controls( string $default_source = 'current' ): void {
		$this->add_control(
			'product_source',
			array(
				'label'   => __( 'Product source', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => $default_source,
				'options' => array(
					'current'  => __( 'Current product (single product template)', 'moon-core' ),
					'specific' => __( 'Choose a product', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'       => __( 'Product', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->product_options(),
				'condition'   => array( 'product_source' => 'specific' ),
			)
		);
	}

	/**
	 * Published products for the editor select (capped for performance).
	 *
	 * @return array<string,string>
	 */
	protected function product_options(): array {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array();
		}

		$options = array();
		$posts   = get_posts(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => 100,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'suppress_filters'       => false,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $posts as $post ) {
			$options[ (string) $post->ID ] = $post->post_title;
		}

		return $options;
	}

	/**
	 * Resolve the product a widget should render.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 */
	protected function resolve_product( array $settings ): ?\WC_Product {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$source = $settings['product_source'] ?? 'current';

		if ( 'specific' === $source && ! empty( $settings['product_id'] ) ) {
			$product = wc_get_product( (int) $settings['product_id'] );

			return $product instanceof \WC_Product ? $product : null;
		}

		$current = $this->current_product();
		if ( $current ) {
			return $current;
		}

		// Editor fallback: show a real product so the widget is never empty.
		if ( $this->is_edit_mode() ) {
			$products = wc_get_products(
				array(
					'status'  => 'publish',
					'limit'   => 1,
					'orderby' => 'date',
					'order'   => 'DESC',
				)
			);

			return $products[0] ?? null;
		}

		return null;
	}

	/**
	 * Product of the current single-product request, if any.
	 */
	protected function current_product(): ?\WC_Product {
		global $product;

		if ( $product instanceof \WC_Product ) {
			return $product;
		}

		$id = get_queried_object_id();
		if ( $id && 'product' === get_post_type( $id ) ) {
			$resolved = wc_get_product( $id );

			return $resolved instanceof \WC_Product ? $resolved : null;
		}

		return null;
	}

	/**
	 * Query products for the listing widgets.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @param string              $prefix   Control prefix.
	 * @return \WC_Product[]
	 */
	protected function query_products( array $settings, string $prefix = '' ): array {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array();
		}

		$limit   = isset( $settings[ $prefix . 'limit' ] ) ? max( 1, (int) $settings[ $prefix . 'limit' ] ) : 8;
		$source  = $settings[ $prefix . 'source' ] ?? 'latest';
		$orderby = $settings[ $prefix . 'orderby' ] ?? 'date';
		$order   = $settings[ $prefix . 'order' ] ?? 'DESC';

		$args = array(
			'status'  => 'publish',
			'limit'   => $limit,
			'orderby' => $orderby,
			'order'   => $order,
		);

		switch ( $source ) {
			case 'featured':
				$args['featured'] = true;
				break;
			case 'sale':
				$args['include'] = wc_get_product_ids_on_sale();
				if ( empty( $args['include'] ) ) {
					return array();
				}
				break;
			case 'category':
				$categories = $settings[ $prefix . 'category' ] ?? array();
				if ( ! empty( $categories ) ) {
					$args['category'] = array_map( 'sanitize_title', (array) $categories );
				}
				break;
			case 'tag':
				$tags = $settings[ $prefix . 'tag' ] ?? array();
				if ( ! empty( $tags ) ) {
					$args['tag'] = array_map( 'sanitize_title', (array) $tags );
				}
				break;
			case 'ids':
				$ids = array_filter( array_map( 'absint', explode( ',', (string) ( $settings[ $prefix . 'ids' ] ?? '' ) ) ) );
				if ( empty( $ids ) ) {
					return array();
				}
				$args['include'] = $ids;
				$args['orderby'] = 'post__in';
				break;
			case 'related':
				$current = $this->current_product();
				if ( $current ) {
					$args['related'] = $current->get_id();
				}
				break;
		}

		if ( 'rand' === $orderby ) {
			$args['orderby'] = 'rand';
		}

		$products = wc_get_products( $args );

		return array_values(
			array_filter(
				$products,
				static function ( $item ) {
					return $item instanceof \WC_Product && $item->is_visible();
				}
			)
		);
	}

	/**
	 * Product category options for Elementor selects.
	 *
	 * @return array<string,string>
	 */
	protected function product_category_options(): array {
		$options = array();

		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return $options;
		}

		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}

		return $options;
	}

	/**
	 * Product tag options for Elementor selects.
	 *
	 * @return array<string,string>
	 */
	protected function product_tag_options(): array {
		$options = array();

		if ( ! taxonomy_exists( 'product_tag' ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return $options;
		}

		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}

		return $options;
	}

	/**
	 * Shared "Source" controls for listing widgets.
	 *
	 * @param string $prefix Control prefix.
	 */
	protected function product_query_controls( string $prefix = '' ): void {
		$has_categories = taxonomy_exists( 'product_cat' ) && ! empty( $this->product_category_options() );

		$options = array(
			'latest'   => __( 'Latest products', 'moon-core' ),
			'featured' => __( 'Featured products', 'moon-core' ),
			'sale'     => __( 'On sale', 'moon-core' ),
		);

		if ( $has_categories ) {
			$options['category'] = __( 'By category', 'moon-core' );
		}

		if ( taxonomy_exists( 'product_tag' ) && ! empty( $this->product_tag_options() ) ) {
			$options['tag'] = __( 'By tag', 'moon-core' );
		}

		$options['ids'] = __( 'Hand-picked (product IDs)', 'moon-core' );

		$this->add_control(
			$prefix . 'source',
			array(
				'label'   => __( 'Products from', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'latest',
				'options' => $options,
			)
		);

		if ( $has_categories ) {
			$this->add_control(
				$prefix . 'category',
				array(
					'label'       => __( 'Product categories', 'moon-core' ),
					'type'        => \Elementor\Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'options'     => $this->product_category_options(),
					'condition'   => array( $prefix . 'source' => 'category' ),
				)
			);
		}

		$this->add_control(
			$prefix . 'tag',
			array(
				'label'       => __( 'Product tags (slugs)', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => 'new-season, tailored',
				'condition'   => array( $prefix . 'source' => 'tag' ),
			)
		);

		$this->add_control(
			$prefix . 'ids',
			array(
				'label'       => __( 'Product IDs (comma separated)', 'moon-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '12, 34, 56',
				'condition'   => array( $prefix . 'source' => 'ids' ),
			)
		);

		$this->add_control(
			$prefix . 'limit',
			array(
				'label'     => __( 'Number of products', 'moon-core' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'default'   => 8,
				'separator' => 'before',
			)
		);

		$this->add_control(
			$prefix . 'orderby',
			array(
				'label'   => __( 'Order by', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => __( 'Date', 'moon-core' ),
					'title'      => __( 'Title', 'moon-core' ),
					'price'      => __( 'Price', 'moon-core' ),
					'rating'     => __( 'Rating', 'moon-core' ),
					'popularity' => __( 'Popularity', 'moon-core' ),
					'menu_order' => __( 'Menu order', 'moon-core' ),
					'rand'       => __( 'Random', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			$prefix . 'order',
			array(
				'label'   => __( 'Order', 'moon-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => __( 'Descending', 'moon-core' ),
					'ASC'  => __( 'Ascending', 'moon-core' ),
				),
			)
		);
	}
}
