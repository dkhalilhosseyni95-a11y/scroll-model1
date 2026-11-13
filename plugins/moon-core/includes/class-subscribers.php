<?php
/**
 * Newsletter subscribers: a private, admin-only record store.
 *
 * Used when no external email provider is configured. Entries are never public,
 * never exposed through REST, and are only readable by administrators. An
 * optional CSV export is protected by a capability check and a nonce.
 *
 * @package MoonCore
 */

namespace MoonCore;

defined( 'ABSPATH' ) || exit;

final class Subscribers {

	public const POST_TYPE = 'moon_subscriber';

	public function init(): void {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'admin_post_moon_core_export_subscribers', array( $this, 'export' ) );
	}

	public function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Subscribers', 'moon-core' ),
					'singular_name' => __( 'Subscriber', 'moon-core' ),
					'menu_name'     => __( 'Subscribers', 'moon-core' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'moon-core',
				'show_in_rest'        => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'rewrite'             => false,
				'query_var'           => false,
				'capability_type'     => 'post',
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
					'edit_post'    => 'manage_options',
					'read_post'    => 'manage_options',
					'delete_post'  => 'manage_options',
					'edit_posts'   => 'manage_options',
				),
				'map_meta_cap'        => false,
				'supports'            => array( 'title' ),
			)
		);
	}

	/**
	 * Store a subscriber. Idempotent per email address.
	 *
	 * @param string $email  Valid email.
	 * @param string $source Page the signup came from.
	 * @return int|\WP_Error
	 */
	public static function add( string $email, string $source = '' ) {
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'moon_invalid_email', __( 'Please enter a valid email address.', 'moon-core' ) );
		}

		$existing = get_posts(
			array(
				'post_type'        => self::POST_TYPE,
				'post_status'      => 'any',
				'posts_per_page'   => 1,
				'fields'           => 'ids',
				'meta_key'         => '_moon_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- single lookup.
				'meta_value'       => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'suppress_filters' => false,
				'no_found_rows'    => true,
			)
		);

		if ( ! empty( $existing ) ) {
			return (int) $existing[0];
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => $email,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, '_moon_email', $email );
		update_post_meta( $post_id, '_moon_source', $source );
		update_post_meta( $post_id, '_moon_created', current_time( 'mysql' ) );

		return (int) $post_id;
	}

	/**
	 * Total subscriber count.
	 */
	public static function count(): int {
		$counts = wp_count_posts( self::POST_TYPE );

		return isset( $counts->publish ) ? (int) $counts->publish : 0;
	}

	/**
	 * CSV export of all subscribers (administrator only).
	 */
	public function export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to export subscribers.', 'moon-core' ), 403 );
		}

		check_admin_referer( Ajax::NONCE_EXPORT );

		$subscribers = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 5000,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=moon-subscribers.csv' );

		$output = fopen( 'php://output', 'w' );
		if ( false === $output ) {
			exit;
		}

		fputcsv( $output, array( 'email', 'subscribed_at', 'source' ) );

		foreach ( $subscribers as $subscriber ) {
			fputcsv(
				$output,
				array(
					(string) get_post_meta( $subscriber->ID, '_moon_email', true ),
					(string) get_post_meta( $subscriber->ID, '_moon_created', true ),
					(string) get_post_meta( $subscriber->ID, '_moon_source', true ),
				)
			);
		}

		fclose( $output );
		exit;
	}
}
