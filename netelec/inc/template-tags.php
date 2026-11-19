<?php
/**
 * Icons and template helpers.
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG sprite, printed once in the footer.
 */
function netelec_icon_sprite() {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$printed = true;

	$icons = array(
		'search'      => '<circle cx="11" cy="11" r="7"/><path d="m20.5 20.5-3.6-3.6"/>',
		'user'        => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/>',
		'heart'       => '<path d="M20.8 5.6a5 5 0 0 0-7.1 0L12 7.3l-1.7-1.7a5 5 0 1 0-7.1 7.1L12 21l8.8-8.3a5 5 0 0 0 0-7.1Z"/>',
		'cart'        => '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3H5l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L21.5 7H6"/>',
		'menu'        => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'       => '<path d="M6 6l12 12M18 6 6 18"/>',
		'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
		'mail'        => '<rect x="2.5" y="4.5" width="19" height="15" rx="2.5"/><path d="m3 7 9 6 9-6"/>',
		'pin'         => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
		'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
		'chevron'     => '<path d="m6 9 6 6 6-6"/>',
		'arrow-left'  => '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
		'arrow-up'    => '<path d="M12 19V5"/><path d="m5 12 7-7 7 7"/>',
		'star'        => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1L3.2 9.5l6.1-.9Z"/>',
		'compare'     => '<circle cx="6" cy="6" r="2.6"/><circle cx="18" cy="18" r="2.6"/><path d="M6 9v5.4A3.6 3.6 0 0 0 9.6 18h5.8"/><path d="M18 15V9.6A3.6 3.6 0 0 0 14.4 6H8.6"/>',
		'eye'         => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
		'truck'       => '<path d="M2.5 6.5h10.5v10H2.5z"/><path d="M13 9.5h3.6l3.4 3.4v3.6H13z"/><circle cx="6.5" cy="18" r="1.8"/><circle cx="16.5" cy="18" r="1.8"/>',
		'shield'      => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3Z"/><path d="m8.8 11.8 2.2 2.2 4.2-4.2"/>',
		'headset'     => '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="2" y="13.5" width="4" height="6.5" rx="2"/><rect x="18" y="13.5" width="4" height="6.5" rx="2"/><path d="M20 20a3 3 0 0 1-3 3h-3"/>',
		'card'        => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19"/>',
		'filter'      => '<path d="M3.5 5h17l-6.6 7.6V19l-3.8 2v-8.4L3.5 5Z"/>',
		'trash'       => '<path d="M4 7h16"/><path d="M9.5 7V4.8h5V7"/><path d="m6 7 1 13h10l1-13"/>',
		'check'       => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'plus'        => '<path d="M12 5v14M5 12h14"/>',
		'minus'       => '<path d="M5 12h14"/>',
		'instagram'   => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1.1" fill="currentColor" stroke="none"/>',
		'telegram'    => '<path d="M21 4.5 18.2 20l-6-4.2-3 2.9-.6-4.9L20 5.4 7.4 12.6 3.4 11 21 4.5Z"/>',
		'whatsapp'    => '<path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.5-4.7a8.5 8.5 0 1 1 16-4.1Z"/><path d="M8.6 9.2c0 3.6 2.8 6.2 6 6.2l1-1.6-2-1-.9 1a6 6 0 0 1-2.7-2.7l1-.9-1-2-1.4.1Z"/>',
		'linkedin'    => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M7.5 10.5V17"/><circle cx="7.5" cy="7.6" r="1"/><path d="M11.5 17v-3.6a2.4 2.4 0 0 1 4.8 0V17"/><path d="M11.5 10.5V17"/>',
		'package'     => '<path d="m12 3 8.5 4.5v9L12 21l-8.5-4.5v-9L12 3Z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
		'grid'        => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.6"/>',
		'zap'         => '<path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z"/>',
		'award'       => '<circle cx="12" cy="9" r="5.5"/><path d="m8.5 13.5-1.5 8 5-2.5 5 2.5-1.5-8"/>',
		'lock'        => '<rect x="4.5" y="10.5" width="15" height="10" rx="2.4"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"/>',
		'logout'      => '<path d="M15 4.5h3.5a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H15"/><path d="M10 16.5 5.5 12 10 7.5"/><path d="M5.5 12H15"/>',
		'download'    => '<path d="M12 3.5v11"/><path d="m7.5 10 4.5 4.5L16.5 10"/><path d="M4.5 19.5h15"/>',
		'home'        => '<path d="m3.5 10.5 8.5-7 8.5 7"/><path d="M5.5 9.5v10h13v-10"/>',
		'file'        => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
		'alert'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5"/><circle cx="12" cy="16.4" r=".9" fill="currentColor" stroke="none"/>',
		'refresh'     => '<path d="M20 12a8 8 0 1 1-2.6-5.9"/><path d="M20 4v5h-5"/>',
		'info'        => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><circle cx="12" cy="8" r=".9" fill="currentColor" stroke="none"/>',
		'tag'         => '<path d="M11 3.5H4.5v6.5L13 19a2 2 0 0 0 2.8 0l3.2-3.2a2 2 0 0 0 0-2.8L11 3.5Z"/><circle cx="8" cy="7" r="1.1" fill="currentColor" stroke="none"/>',
	);

	echo '<svg xmlns="http://www.w3.org/2000/svg" class="nc-sprite" aria-hidden="true" focusable="false" style="position:absolute;width:0;height:0;overflow:hidden">';
	foreach ( $icons as $name => $paths ) {
		echo '<symbol id="nc-' . esc_attr( $name ) . '" viewBox="0 0 24 24">' . $paths . '</symbol>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
	}
	echo '</svg>';
}

/**
 * Print an icon from the sprite.
 *
 * @param string $name  Icon name.
 * @param string $class Extra class.
 */
function netelec_icon( $name, $class = '' ) {
	printf(
		'<svg class="nc-icon %1$s" aria-hidden="true" focusable="false"><use href="#nc-%2$s"></use></svg>',
		esc_attr( $class ),
		esc_attr( $name )
	);
}

/**
 * Reading time in minutes.
 *
 * @param int|null $post_id Post ID.
 * @return int
 */
function netelec_reading_time( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = max( 1, str_word_count( wp_strip_all_tags( (string) $content ) ) );
	// Persian text does not space the same way; fall back to character count.
	if ( $words < 20 ) {
		$words = max( 1, (int) ( mb_strlen( wp_strip_all_tags( (string) $content ) ) / 5 ) );
	}
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Post meta line.
 *
 * @param bool $with_author Include the author.
 */
function netelec_post_meta( $with_author = true ) {
	echo '<div class="post-meta">';

	$category = get_the_category();
	if ( $category ) {
		printf(
			'<a class="post-meta__cat" href="%1$s">%2$s</a>',
			esc_url( get_category_link( $category[0] ) ),
			esc_html( $category[0]->name )
		);
	}

	if ( netelec_opt( 'blog_show_meta' ) ) {
		printf(
			'<span class="post-meta__item">%1$s%2$s</span>',
			netelec_icon( 'clock', 'nc-icon--sm' ),
			esc_html( get_the_date() )
		);
	}

	if ( $with_author && netelec_opt( 'blog_show_author' ) ) {
		printf(
			'<span class="post-meta__item">%1$s%2$s</span>',
			netelec_icon( 'user', 'nc-icon--sm' ),
			esc_html( get_the_author() )
		);
	}

	if ( netelec_opt( 'blog_show_readtime' ) ) {
		printf(
			'<span class="post-meta__item">%1$s%2$s دقیقه مطالعه</span>',
			netelec_icon( 'file', 'nc-icon--sm' ),
			esc_html( netelec_reading_time() )
		);
	}

	echo '</div>';
}

/**
 * Breadcrumb trail. Uses Yoast / Rank Math when available.
 */
function netelec_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}

	// Delegate to SEO plugins so their schema stays intact.
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="nc-breadcrumb nc-breadcrumb--seo" aria-label="مسیر صفحه">', '</nav>' );
		return;
	}

	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		echo '<nav class="nc-breadcrumb nc-breadcrumb--seo" aria-label="مسیر صفحه">';
		rank_math_the_breadcrumbs();
		echo '</nav>';
		return;
	}

	echo '<nav class="nc-breadcrumb" aria-label="مسیر صفحه"><ol>';

	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">خانه</a></li>';

	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		if ( is_product() ) {
			$terms = get_the_terms( get_the_ID(), 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				echo '<li><a href="' . esc_url( get_term_link( $terms[0] ) ) . '">' . esc_html( $terms[0]->name ) . '</a></li>';
			}
			echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
		} elseif ( is_product_taxonomy() ) {
			echo '<li aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</li>';
		} else {
			echo '<li aria-current="page">فروشگاه</li>';
		}
	} elseif ( is_singular() ) {
		echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
	} elseif ( is_archive() ) {
		echo '<li aria-current="page">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</li>';
	} elseif ( is_search() ) {
		echo '<li aria-current="page">نتایج جستجو</li>';
	} elseif ( is_404() ) {
		echo '<li aria-current="page">صفحه پیدا نشد</li>';
	}

	echo '</ol></nav>';
}

/**
 * Page header used by archives and pages.
 *
 * @param string $title    Title.
 * @param string $subtitle Optional subtitle.
 */
function netelec_page_header( $title, $subtitle = '' ) {
	?>
	<div class="page-hero">
		<div class="container">
			<?php netelec_breadcrumb(); ?>
			<h1 class="page-hero__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p class="page-hero__sub"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Section heading with optional link.
 *
 * @param string $title Section title.
 * @param string $url   Optional link.
 * @param string $desc  Optional description.
 */
function netelec_section_head( $title, $url = '', $desc = '' ) {
	?>
	<div class="section-head">
		<div>
			<h2 class="section-head__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $desc ) : ?>
				<p class="section-head__desc"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $url ) : ?>
			<a class="section-head__link" href="<?php echo esc_url( $url ); ?>">
				مشاهده همه
				<?php netelec_icon( 'arrow-left', 'nc-icon--sm' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Pagination wrapper.
 */
function netelec_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => 'قبلی',
			'next_text' => 'بعدی',
			'class'     => 'nc-pagination',
		)
	);
}

/**
 * Cart contents count (0 without WooCommerce).
 *
 * @return int
 */
function netelec_cart_count() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return WC()->cart->get_cart_contents_count();
	}
	return 0;
}

/**
 * Human label for a theme page slug.
 *
 * @param string $slug Page slug.
 * @return string
 */
function netelec_page_label( $slug ) {
	$labels = array(
		'about'    => 'درباره ما',
		'contact'  => 'تماس با ما',
		'wishlist' => 'علاقه‌مندی‌ها',
		'compare'  => 'مقایسه محصولات',
		'terms'    => 'قوانین و مقررات',
		'privacy'  => 'حریم خصوصی',
	);
	return isset( $labels[ $slug ] ) ? $labels[ $slug ] : $slug;
}

/**
 * Store benefits shown on the homepage.
 *
 * @return array
 */
function netelec_benefits() {
	$items = array(
		array( 'icon' => 'truck', 'title' => netelec_opt( 'benefit_1_title', 'ارسال سریع' ), 'desc' => netelec_opt( 'benefit_1_desc', 'ارسال به سراسر ایران' ) ),
		array( 'icon' => 'shield', 'title' => netelec_opt( 'benefit_2_title', 'ضمانت اصالت' ), 'desc' => netelec_opt( 'benefit_2_desc', 'تأمین از منابع معتبر' ) ),
		array( 'icon' => 'headset', 'title' => netelec_opt( 'benefit_3_title', 'پشتیبانی تخصصی' ), 'desc' => netelec_opt( 'benefit_3_desc', 'مشاوره پیش و پس از خرید' ) ),
		array( 'icon' => 'card', 'title' => netelec_opt( 'benefit_4_title', 'پرداخت امن' ), 'desc' => netelec_opt( 'benefit_4_desc', 'از طریق درگاه بانکی' ) ),
	);

	return array_values(
		array_filter(
			$items,
			function ( $item ) {
				return '' !== $item['title'];
			}
		)
	);
}

/**
 * Render star rating markup from a 0-5 score.
 *
 * @param float $rating Rating value.
 */
function netelec_stars( $rating ) {
	echo '<span class="nc-stars" aria-label="' . esc_attr( sprintf( 'امتیاز %s از ۵', $rating ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$class = $i <= round( $rating ) ? 'is-filled' : '';
		printf( '<svg class="nc-icon nc-icon--xs %s" aria-hidden="true"><use href="#nc-star"></use></svg>', esc_attr( $class ) );
	}
	echo '</span>';
}
