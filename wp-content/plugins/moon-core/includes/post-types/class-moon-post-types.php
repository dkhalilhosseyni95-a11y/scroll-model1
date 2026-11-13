<?php
/**
 * Custom post types: rack garments and studio outfits.
 *
 * @package Moon_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the content types.
 */
class Moon_Post_Types {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register both post types.
	 */
	public static function register() {
		register_post_type( 'moon_rack', array(
			'labels'          => array(
				'name'          => __( 'اقلام رگال', 'moon-core' ),
				'singular_name' => __( 'قلم رگال', 'moon-core' ),
				'add_new'       => __( 'افزودن قلم', 'moon-core' ),
				'add_new_item'  => __( 'افزودن قلم رگال', 'moon-core' ),
				'edit_item'     => __( 'ویرایش قلم رگال', 'moon-core' ),
				'all_items'     => __( 'اقلام رگال', 'moon-core' ),
				'menu_name'     => __( 'رگال', 'moon-core' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'moon-theme',
			'show_in_rest'    => false,
			'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
			'menu_icon'       => 'dashicons-tag',
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		) );

		register_post_type( 'moon_outfit', array(
			'labels'          => array(
				'name'          => __( 'استایل‌ها', 'moon-core' ),
				'singular_name' => __( 'استایل', 'moon-core' ),
				'add_new'       => __( 'افزودن استایل', 'moon-core' ),
				'add_new_item'  => __( 'افزودن استایل', 'moon-core' ),
				'edit_item'     => __( 'ویرایش استایل', 'moon-core' ),
				'all_items'     => __( 'استایل‌ها', 'moon-core' ),
				'menu_name'     => __( 'استودیو', 'moon-core' ),
			),
			'public'          => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'has_archive'     => false,
			'rewrite'         => array( 'slug' => 'look', 'with_front' => false ),
			'show_ui'         => true,
			'show_in_menu'    => 'moon-theme',
			'show_in_rest'    => false,
			'supports'        => array( 'title', 'thumbnail', 'editor', 'excerpt', 'page-attributes' ),
			'menu_icon'       => 'dashicons-image-filter',
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		) );
	}
}
