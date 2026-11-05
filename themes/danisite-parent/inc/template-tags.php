<?php
/**
 * Template helpers shared by the theme templates and the DaniSite Core plugin.
 *
 * Every helper degrades gracefully when the DaniSite Core plugin is inactive,
 * so the theme always renders a complete page.
 *
 * @package DaniSite
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default site options. The DaniSite Core plugin stores overrides in the
 * `dani_options` option; the theme falls back to these values.
 *
 * @return array
 */
function dani_default_options() {
	return array(
		'site_name'        => 'DaniSite',
		'tagline'          => 'DaniSite — طراحی برای متمایز بودن.',
		'brand_desc'       => 'استودیوی طراحی و توسعه وب که تجربه‌های دیجیتال متمایز می‌سازد.',
		'email'            => 'info@danisite.ir',
		'phone'            => '۰۹۰۰ ۰۰۰ ۰۰۰۰',
		'phone_link'       => '+989000000000',
		'address'          => 'تهران، ایران',
		'hours'            => 'شنبه تا چهارشنبه، ۹ تا ۱۸',
		'cta_label'        => 'شروع همکاری',
		'cta_url'          => '/contact/',
		'footer_note'      => 'DaniSite — طراحی برای متمایز بودن.',
		'copyright'        => '© ۱۴۰۵ تمامی حقوق محفوظ است.',
		'hero_eyebrow'     => 'DANISITE STUDIO · DIGITAL EXPERIENCE',
		'social_instagram' => 'https://instagram.com',
		'social_linkedin'  => 'https://linkedin.com',
		'social_telegram'  => 'https://t.me',
		'social_whatsapp'  => '',
	);
}

/**
 * Read a design/site option.
 *
 * @param string $key     Option key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function dani_option( $key, $default = null ) {
	$options  = get_option( 'dani_options', array() );
	$defaults = dani_default_options();

	if ( is_array( $options ) && isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}
	if ( null !== $default ) {
		return $default;
	}

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * Read a DaniSite post meta value.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Field key without the `_dani_` prefix.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function dani_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( $post_id, '_dani_' . $key, true );

	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Services shown in the footer: the CPT when available, the designed default list otherwise.
 *
 * @return array
 */
function dani_footer_services() {
	$services = array();

	if ( post_type_exists( 'dani_service' ) ) {
		$query = new WP_Query( array(
			'post_type'      => 'dani_service',
			'posts_per_page' => 5,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );
		while ( $query->have_posts() ) {
			$query->the_post();
			$services[] = array(
				'title' => get_the_title(),
				'url'   => get_permalink(),
			);
		}
		wp_reset_postdata();
	}

	if ( empty( $services ) ) {
		$services = array(
			array( 'title' => 'طراحی رابط و تجربه کاربری', 'url' => home_url( '/services/' ) ),
			array( 'title' => 'طراحی و توسعه وردپرس', 'url' => home_url( '/services/' ) ),
			array( 'title' => 'توسعه فرانت‌اند', 'url' => home_url( '/services/' ) ),
			array( 'title' => 'هویت دیجیتال برند', 'url' => home_url( '/services/' ) ),
			array( 'title' => 'بهینه‌سازی سرعت و سئو', 'url' => home_url( '/services/' ) ),
		);
	}

	return $services;
}

/**
 * Social profiles that have a URL.
 *
 * @return array
 */
function dani_get_socials() {
	$map = array(
		'instagram' => array( 'label' => 'اینستاگرام', 'icon' => 'instagram' ),
		'linkedin'  => array( 'label' => 'لینکدین', 'icon' => 'linkedin' ),
		'telegram'  => array( 'label' => 'تلگرام', 'icon' => 'telegram' ),
		'whatsapp'  => array( 'label' => 'واتس‌اپ', 'icon' => 'whatsapp' ),
	);

	$out = array();
	foreach ( $map as $key => $meta ) {
		$url = dani_option( 'social_' . $key );
		if ( $url ) {
			$out[] = array(
				'key'   => $key,
				'label' => $meta['label'],
				'icon'  => $meta['icon'],
				'url'   => $url,
			);
		}
	}

	return $out;
}

/**
 * Print a menu with a sensible fallback when no menu is assigned yet.
 *
 * @param string $location Menu location.
 * @param array  $args     wp_nav_menu overrides.
 * @return void
 */
function dani_nav_menu( $location = 'primary', $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'theme_location' => $location,
			'container'      => false,
			'depth'          => 2,
			'fallback_cb'    => 'dani_nav_fallback',
			'menu_class'     => 'dani-nav__list',
		)
	);

	wp_nav_menu( $args );
}

/**
 * Fallback menu: primary pages in the site's own information architecture.
 *
 * @param array $args wp_nav_menu arguments.
 * @return void
 */
function dani_nav_fallback( $args = array() ) {
	$is_primary = isset( $args['theme_location'] ) ? 'primary' === $args['theme_location'] : true;
	$class      = $is_primary ? 'dani-nav__list' : 'dani-footer__links';

	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( dani_default_menu_items() as $item ) {
		$current = dani_is_current_url( $item['url'] ) ? ' is-active' : '';
		printf(
			'<li class="%1$s"><a class="%2$s" href="%3$s">%4$s</a></li>',
			esc_attr( trim( 'menu-item' . $current ) ),
			esc_attr( $is_primary ? 'dani-nav__link' . $current : '' ),
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Default navigation used before the demo importer (or a user menu) exists.
 *
 * @return array
 */
function dani_default_menu_items() {
	$items = array(
		array( 'label' => 'خانه', 'slug' => 'home' ),
		array( 'label' => 'درباره ما', 'slug' => 'about' ),
		array( 'label' => 'خدمات', 'slug' => 'services' ),
		array( 'label' => 'نمونه‌کارها', 'slug' => 'portfolio' ),
		array( 'label' => 'وبلاگ', 'slug' => 'blog' ),
		array( 'label' => 'تماس با ما', 'slug' => 'contact' ),
	);

	$out = array();
	foreach ( $items as $item ) {
		$page = get_page_by_path( $item['slug'] );
		$out[] = array(
			'label' => $item['label'],
			'url'   => $page ? get_permalink( $page ) : home_url( '/' ),
			'slug'  => $item['slug'],
		);
	}

	return $out;
}

/**
 * Is the given URL the current page?
 *
 * @param string $url URL to compare.
 * @return bool
 */
function dani_is_current_url( $url ) {
	$current = home_url( add_query_arg( array(), $GLOBALS['wp']->request ? $GLOBALS['wp']->request : '' ) );
	$current = untrailingslashit( strtok( $current, '?' ) );

	return untrailingslashit( strtok( $url, '?' ) ) === $current;
}

/**
 * Site logo: the WordPress custom logo when set, otherwise the bundled wordmark.
 *
 * @param array $args Optional. class, height, echo.
 * @return string
 */
function dani_logo( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class' => '',
			'echo'  => true,
			'size'  => 'md',
		)
	);

	$home = esc_url( home_url( '/' ) );
	$name = dani_option( 'site_name', get_bloginfo( 'name' ) );

	$html  = '<a class="dani-logo ' . esc_attr( $args['class'] ) . '" href="' . $home . '" rel="home" aria-label="' . esc_attr( $name . ' — خانه' ) . '">';
	$html .= '<span class="dani-logo__mark" aria-hidden="true">';
	$html .= dani_logo_mark();
	$html .= '</span>';
	$html .= '<span class="dani-logo__text"><span class="dani-logo__name">' . esc_html( $name ) . '</span></span>';
	$html .= '</a>';

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}

	return $html;
}

/**
 * The DaniSite monogram, drawn in the brand gradient.
 *
 * @return string
 */
function dani_logo_mark() {
	return '<svg viewBox="0 0 40 40" width="40" height="40" role="img" aria-hidden="true" focusable="false">'
		. '<defs><linearGradient id="daniLogoGrad" x1="0" y1="0" x2="1" y2="1">'
		. '<stop offset="0" stop-color="#0754E9"/><stop offset="1" stop-color="#00C7EB"/></linearGradient></defs>'
		. '<rect width="40" height="40" rx="12" fill="url(#daniLogoGrad)"/>'
		. '<path d="M13 12h6.2c5 0 8.3 3.1 8.3 8s-3.3 8-8.3 8H13V12Zm4.1 3.4v9.2h1.9c2.9 0 4.6-1.7 4.6-4.6s-1.7-4.6-4.6-4.6h-1.9Z" fill="#fff"/>'
		. '</svg>';
}

/**
 * Breadcrumbs that mirror the site structure.
 *
 * @return void
 */
function dani_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items = array(
		array( 'label' => 'خانه', 'url' => home_url( '/' ) ),
	);

	if ( is_singular() ) {
		$post_type = get_post_type_object( get_post_type() );
		if ( $post_type && ! in_array( get_post_type(), array( 'post', 'page' ), true ) && ! empty( $post_type->has_archive ) ) {
			$archive = get_post_type_archive_link( get_post_type() );
			if ( $archive ) {
				$items[] = array( 'label' => $post_type->labels->name, 'url' => $archive );
			}
		}
		if ( 'post' === get_post_type() ) {
			$cats = get_the_category();
			if ( ! empty( $cats ) ) {
				$items[] = array( 'label' => $cats[0]->name, 'url' => get_category_link( $cats[0] ) );
			}
		}
		if ( ! is_front_page() && is_page() ) {
			$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
			foreach ( $ancestors as $ancestor ) {
				$items[] = array( 'label' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) );
			}
		}
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array( 'label' => single_term_title( '', false ), 'url' => '' );
	} elseif ( is_search() ) {
		$items[] = array( 'label' => 'جست‌وجو', 'url' => '' );
	} elseif ( is_404() ) {
		$items[] = array( 'label' => 'صفحه پیدا نشد', 'url' => '' );
	} elseif ( is_home() && ! is_front_page() ) {
		$items[] = array( 'label' => 'وبلاگ', 'url' => '' );
	} elseif ( is_archive() ) {
		$items[] = array( 'label' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
	}

	echo '<nav class="dani-crumbs" aria-label="مسیر صفحه">';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i > 0 ) {
			echo '<span class="dani-crumbs__sep" aria-hidden="true">/</span>';
		}
		if ( $i === $last || empty( $item['url'] ) ) {
			echo '<span aria-current="page">' . esc_html( $item['label'] ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		}
	}
	echo '</nav>';
}

/**
 * Section heading block used across templates and widgets.
 *
 * @param array $args eyebrow, title, text, class, tag.
 * @return void
 */
function dani_section_head( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow' => '',
			'title'   => '',
			'text'    => '',
			'class'   => '',
			'center'  => false,
			'tag'     => 'h2',
		)
	);

	printf(
		'<div class="dani-head%1$s %2$s"><p class="dani-eyebrow">%3$s</p><%5$s class="dani-head__title">%4$s</%5$s>%6$s</div>',
		$args['center'] ? ' dani-head--center' : '',
		esc_attr( $args['class'] ),
		esc_html( $args['eyebrow'] ),
		esc_html( $args['title'] ),
		esc_attr( $args['tag'] ),
		$args['text'] ? '<p class="dani-head__text">' . esc_html( $args['text'] ) . '</p>' : ''
	);
}

/**
 * Formatted post meta line.
 *
 * @return void
 */
function dani_post_meta() {
	$time = sprintf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( dani_date() )
	);

	echo '<div class="dani-post__meta">';
	echo '<span>' . $time . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$cats = get_the_category();
	if ( ! empty( $cats ) ) {
		echo '<span aria-hidden="true">·</span>';
		echo '<a class="dani-post__cat" href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
	}
	echo '<span aria-hidden="true">·</span><span>' . esc_html( dani_reading_time() ) . '</span>';
	echo '</div>';
}

/**
 * Persian-friendly post date.
 *
 * Iranian visitors read dates in the Jalali calendar, so the publish date is
 * converted from Gregorian and printed with Persian month names and digits.
 *
 * @param int|WP_Post|null $post Optional. Post to read the date from.
 * @return string
 */
function dani_date( $post = null ) {
	$date = get_the_date( 'Y-m-d', $post );

	if ( ! $date ) {
		return '';
	}

	list( $year, $month, $day ) = array_map( 'intval', explode( '-', $date ) );
	list( $jy, $jm, $jd )       = dani_jalali_date( $year, $month, $day );

	$months = array(
		'فروردین',
		'اردیبهشت',
		'خرداد',
		'تیر',
		'مرداد',
		'شهریور',
		'مهر',
		'آبان',
		'آذر',
		'دی',
		'بهمن',
		'اسفند',
	);

	return dani_fa_number( $jd . ' ' . $months[ $jm - 1 ] . ' ' . $jy );
}

/**
 * Convert a Gregorian date to the Jalali (Solar Hijri) calendar.
 *
 * @param int $gy Gregorian year.
 * @param int $gm Gregorian month.
 * @param int $gd Gregorian day.
 * @return array Year, month and day in the Jalali calendar.
 */
function dani_jalali_date( $gy, $gm, $gd ) {
	$g_days_in_month = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2             = ( $gm > 2 ) ? $gy + 1 : $gy;
	$days            = 355666 + ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) + $gd + $g_days_in_month[ $gm - 1 ];
	$jy              = -1595 + ( 33 * (int) ( $days / 12053 ) );

	$days %= 12053;
	$jy   += 4 * (int) ( $days / 1461 );
	$days %= 1461;

	if ( $days > 365 ) {
		$jy  += (int) ( ( $days - 1 ) / 365 );
		$days = ( $days - 1 ) % 365;
	}

	if ( $days < 186 ) {
		$jm = 1 + (int) ( $days / 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + (int) ( ( $days - 186 ) / 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}

	return array( $jy, $jm, $jd );
}

/**
 * Estimated reading time in Persian numerals.
 *
 * @return string
 */
function dani_reading_time() {
	$words   = str_word_count( wp_strip_all_tags( get_the_content() ), 0, 'ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهی' );
	$minutes = max( 1, (int) round( $words / 200 ) );

	return dani_fa_number( $minutes ) . ' دقیقه مطالعه';
}

/**
 * Convert western digits to Persian digits.
 *
 * @param string|int $value Input value.
 * @return string
 */
function dani_fa_number( $value ) {
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );

	return str_replace( $en, $fa, (string) $value );
}

/**
 * Post thumbnail or a designed placeholder.
 *
 * @param string $size Image size.
 * @param array  $args Optional. class, ratio.
 * @return void
 */
function dani_thumbnail( $size = 'dani-card', $args = array() ) {
	$args = wp_parse_args( $args, array( 'class' => '', 'placeholder' => true ) );

	if ( has_post_thumbnail() ) {
		the_post_thumbnail( $size, array( 'class' => $args['class'], 'loading' => 'lazy', 'decoding' => 'async' ) );
		return;
	}

	if ( ! $args['placeholder'] ) {
		return;
	}

	echo '<span class="dani-post__media--empty" aria-hidden="true">' . dani_icon( 'image' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Theme-styled pagination.
 *
 * @return void
 */
function dani_pagination() {
	$links = paginate_links( array(
		'type'      => 'array',
		'prev_text' => dani_icon( 'chevron-right' ) . '<span class="screen-reader-text">قبلی</span>',
		'next_text' => dani_icon( 'chevron-left' ) . '<span class="screen-reader-text">بعدی</span>',
	) );

	if ( empty( $links ) ) {
		return;
	}

	echo '<nav class="dani-pagination" aria-label="صفحه‌بندی">';
	foreach ( $links as $link ) {
		echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated by WordPress.
	}
	echo '</nav>';
}

/**
 * Persian-friendly archive title (drops the "دسته:" prefix WordPress adds).
 *
 * @return string
 */
function dani_archive_title() {
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}
	if ( is_post_type_archive() ) {
		$archive = post_type_archive_title( '', false );
		if ( $archive ) {
			return $archive;
		}
	}
	if ( is_author() ) {
		return get_the_author();
	}
	if ( is_search() ) {
		/* translators: %s: search query */
		return sprintf( __( 'نتایج جست‌وجو برای «%s»', 'danisite' ), get_search_query() );
	}
	if ( is_home() ) {
		return __( 'وبلاگ', 'danisite' );
	}

	return wp_strip_all_tags( get_the_archive_title() );
}

/**
 * Archive description text.
 *
 * @return string
 */
function dani_archive_description() {
	$desc = get_the_archive_description();
	if ( $desc ) {
		return wp_strip_all_tags( $desc );
	}

	if ( is_post_type_archive() ) {
		$type = get_post_type();
		$map  = array(
			'dani_project' => 'گزیده‌ای از پروژه‌های طراحی و توسعه دنی سایت؛ از هویت دیجیتال برند تا فروشگاه اینترنتی و اپلیکیشن.',
			'dani_service' => 'خدماتی که برای ساختن یک حضور دیجیتال متمایز ارائه می‌دهیم؛ از طراحی تا اجرا و رشد.',
			'dani_team'    => 'تیمی که ایده‌ها را به تجربه‌های دیجیتال قابل استفاده تبدیل می‌کند.',
			'dani_faq'     => 'پاسخ پرتکرارترین پرسش‌های مشتریان درباره فرایند همکاری با دنی سایت.',
		);

		if ( isset( $map[ $type ] ) ) {
			return $map[ $type ];
		}
	}

	return 'یادداشت‌ها، تحلیل‌ها و تجربه‌های تیم دنی سایت در طراحی، توسعه و رشد دیجیتال.';
}
