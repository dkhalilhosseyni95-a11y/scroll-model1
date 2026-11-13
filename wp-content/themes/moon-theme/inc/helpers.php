<?php
/**
 * Theme helpers and default content.
 *
 * The theme keeps a complete set of defaults so the site renders correctly even
 * when the MOON Core companion plugin is not active.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every stored setting and its default value.
 *
 * @return array
 */
function moon_option_defaults() {
	$defaults = array(
		// Brand.
		'brand_name'           => '',
		'brand_tagline'        => '',
		'logo_id'              => 0,
		'accent'               => '',
		'font_display'         => 'serif',
		'type_scale'           => '100',

		// Mobile behaviour.
		'hero_mobile_min_height' => 640,
		'mobile_hide_scrollstory' => 0,

		// Header.
		'header_search'        => 1,
		'header_account'       => 1,
		'header_wishlist'      => 1,
		'header_theme_toggle'  => 1,
		'header_sticky'        => 1,
		'header_menu'          => 0,

		// Footer.
		'footer_about'         => 'پوشاک لوکس زنانه و مردانه؛ ساخته‌شده برای زمان.',
		'footer_instagram'     => 'https://instagram.com',
		'footer_telegram'      => 'https://t.me',
		'footer_copyright'     => '© ۲۰۲۶ MOON — تمامی حقوق محفوظ است.',
		'footer_cities'        => 'Tehran · Paris · Milan',
		'footer_col1'          => "زنانه|#\nمردانه|#\nکالکشن جدید|#\nاکسسوری|#",
		'footer_col2'          => "راهنمای سایز|#\nارسال و بازگشت|#\nپیگیری سفارش|#\nسوالات متداول|#",
		'footer_col3'          => "داستان ما|#\nآتلیه|#\nفرصت‌های شغلی|#\nتماس با ما|#",

		// Newsletter.
		'news_eyebrow'         => 'The Moon Letter',
		'news_title'           => 'اولین نفری باشید که کالکشن بعدی را می‌بیند.',
		'news_note'            => 'ممنون! به زودی خبرتان می‌کنیم.',

		// Hero.
		'hero_eyebrow'         => 'MOON / New Collection',
		'hero_title_1'         => 'استایل متفاوت،',
		'hero_title_2'         => 'انتخابی فراتر از زمان',
		'hero_desc'            => 'لباس‌هایی برای کسانی که به جزئیات اهمیت می‌دهند.',
		'hero_btn1_text'       => 'مشاهده کالکشن زنانه',
		'hero_btn1_url'        => '',
		'hero_btn2_text'       => 'مشاهده کالکشن مردانه',
		'hero_btn2_url'        => '',
		'hero_image_id'        => 0,

		// Categories.
		'cat1_en'              => 'WOMEN',
		'cat1_label'           => 'کالکشن زنانه',
		'cat1_note'            => 'ابریشم، کتان، سکوت',
		'cat1_url'             => '',
		'cat1_image_id'        => 0,
		'cat2_en'              => 'MEN',
		'cat2_label'           => 'کالکشن مردانه',
		'cat2_note'            => 'پشم، چرم، تیرگی',
		'cat2_url'             => '',
		'cat2_image_id'        => 0,

		// Rack.
		'rack_eyebrow'         => 'The Rack — 01',
		'rack_title'           => 'یک لباس را از رگال بردارید',
		'rack_desc'            => 'روی هر لباس کلیک کنید تا از رگال جدا شود و جزئیاتش را از نزدیک ببینید.',
		'rack_note'            => 'قیمت نمایشی است',

		// Studio.
		'studio_eyebrow'       => 'Fitting Room — 02',
		'studio_title'         => 'استودیو استایل؛ لباس را عوض کنید، نه مدل را',
		'studio_cta'           => 'مشاهده این استایل',

		// Products.
		'products_eyebrow'     => 'Latest — 03',
		'products_title'       => 'تازه‌ترین‌های کالکشن',
		'products_note'        => '* محصولات و قیمت‌ها نمایشی هستند.',
		'products_source'      => 'recent',
		'products_limit'       => 8,

		// Campaign.
		'campaign_eyebrow'     => 'Campaign — Desert Light',
		'campaign_title'       => 'Golden Hour',
		'campaign_text'        => 'کالکشن «نور صحرا»؛ ترکیبی از رنگ‌های خاکی، برش‌های آزاد و سکوت غروب.',
		'campaign_btn_text'    => 'کشف کمپین',
		'campaign_btn_url'     => '',
		'campaign_image_id'    => 0,

		// Brand story.
		'story_eyebrow'        => 'Our Story',
		'story_title'          => 'ماه همیشه همان است؛',
		'story_title_2'        => 'فقط نورش تغییر می‌کند.',
		'story_text'           => 'MOON از دل یک آتلیه کوچک متولد شد؛ جایی که هر لباس با دست برش می‌خورد و هر درز معنایی داشت. امروز همان دقت را در کالکشن‌هایی می‌بینید که برای زمان ساخته شده‌اند، نه برای فصل.',
		'story_image_id'       => 0,
		'story_year'           => '2014',
		'story_year_note'      => 'آغاز در یک آتلیه کوچک',

		// Scroll story.
		'scene1_eyebrow'       => 'Chapter I — Craft',
		'scene1_text'          => 'همه‌چیز از یک برش آغاز می‌شود',
		'scene1_image_id'      => 0,
		'scene2_eyebrow'       => 'Chapter II — Form',
		'scene2_text'          => 'فرم، سکوتی است که دیده می‌شود',
		'scene2_image_id'      => 0,
		'scene3_eyebrow'       => 'Chapter III — Night',
		'scene3_text'          => 'برای شب‌هایی که به یاد می‌مانند',
		'scene3_image_id'      => 0,

		// Testimonials.
		'quote1_text'          => 'کیفیت پارچه و دوخت پالتو واقعاً فراتر از انتظارم بود. هر بار که می‌پوشمش حس خاصی دارد.',
		'quote1_name'          => 'سارا م.',
		'quote1_city'          => 'تهران',
		'quote2_text'          => 'بسته‌بندی، ارسال و حتی کارت دست‌نویس داخل جعبه؛ همه‌چیز حس یک برند لوکس واقعی را داشت.',
		'quote2_name'          => 'امیر ر.',
		'quote2_city'          => 'اصفهان',
		'quote3_text'          => 'استودیو استایل سایت کمکم کرد قبل از خرید، ست کامل را تصور کنم. انتخاب برایم راحت شد.',
		'quote3_name'          => 'نیلوفر ک.',
		'quote3_city'          => 'شیراز',
	);

	// Four service benefits below the brand story.
	$benefits = array(
		1 => array( 'کیفیت ممتاز', 'پارچه‌های منتخب و دوخت دقیق', 'gem' ),
		2 => array( 'پرداخت امن', 'درگاه‌های معتبر و رمزنگاری‌شده', 'shield-check' ),
		3 => array( 'ارسال سریع', 'تحویل در تهران طی ۲۴ ساعت', 'truck' ),
		4 => array( 'پشتیبانی', 'همراه شما، هر روز هفته', 'headphones' ),
	);

	foreach ( $benefits as $index => $benefit ) {
		$defaults[ 'benefit' . $index . '_title' ] = $benefit[0];
		$defaults[ 'benefit' . $index . '_desc' ]  = $benefit[1];
		$defaults[ 'benefit' . $index . '_icon' ]  = $benefit[2];
	}

	return apply_filters( 'moon_option_defaults', $defaults );
}

/**
 * Read a theme option.
 *
 * @param string $key     Option key.
 * @param mixed  $default Fallback used when the stored value is empty.
 * @return mixed
 */
function moon_opt( $key, $default = null ) {
	static $options = null;

	if ( null === $options ) {
		$stored  = get_option( 'moon_options', array() );
		$options = wp_parse_args( is_array( $stored ) ? $stored : array(), moon_option_defaults() );
	}

	$value = isset( $options[ $key ] ) ? $options[ $key ] : '';

	if ( null !== $default && ( '' === $value || null === $value ) ) {
		return $default;
	}

	return $value;
}

/**
 * Whether WooCommerce is active.
 *
 * @return bool
 */
function moon_is_woo() {
	return class_exists( 'WooCommerce' );
}

/**
 * Shop archive URL (falls back to the front page).
 *
 * @return string
 */
function moon_shop_url() {
	if ( moon_is_woo() ) {
		$url = get_permalink( wc_get_page_id( 'shop' ) );
		if ( $url ) {
			return $url;
		}
	}

	return home_url( '/' );
}

/**
 * Product category archive URL.
 *
 * @param string $slug Category slug.
 * @return string
 */
function moon_product_cat_url( $slug ) {
	if ( moon_is_woo() ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$url = get_term_link( $term );
			if ( ! is_wp_error( $url ) ) {
				return $url;
			}
		}
	}

	return moon_shop_url();
}

/**
 * Cart URL.
 *
 * @return string
 */
function moon_cart_url() {
	if ( moon_is_woo() && function_exists( 'wc_get_cart_url' ) ) {
		return wc_get_cart_url();
	}

	return home_url( '/' );
}

/**
 * My account URL.
 *
 * @return string
 */
function moon_account_url() {
	if ( moon_is_woo() ) {
		$page = get_permalink( wc_get_page_id( 'myaccount' ) );
		if ( $page ) {
			return $page;
		}
	}

	return wp_login_url();
}

/**
 * Cart item count.
 *
 * @return int
 */
function moon_cart_count() {
	if ( moon_is_woo() && function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}

	return 0;
}

/**
 * Turn a stored link into a usable URL, falling back when empty.
 *
 * @param string $url      Stored URL.
 * @param string $fallback Fallback URL or anchor.
 * @return string
 */
function moon_link( $url, $fallback = '' ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return $fallback;
	}

	if ( '#' === $url[0] ) {
		return $url;
	}

	return $url;
}

/**
 * Inline an icon from the theme's Lucide icon set.
 *
 * @param string $name  Icon file name.
 * @param int    $size  Size in pixels.
 * @param string $class Extra CSS classes.
 * @return string
 */
function moon_icon( $name, $size = 24, $class = '' ) {
	static $cache = array();

	$name = preg_replace( '/[^a-z0-9\-]/', '', (string) $name );

	if ( '' === $name ) {
		return '';
	}

	if ( ! isset( $cache[ $name ] ) ) {
		$file = MOON_THEME_DIR . '/assets/icons/' . $name . '.svg';

		if ( ! file_exists( $file ) ) {
			$cache[ $name ] = '';
		} else {
			$cache[ $name ] = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset.
		}
	}

	if ( '' === $cache[ $name ] ) {
		return '';
	}

	$svg = preg_replace( '/<!--.*?-->/s', '', $cache[ $name ] );
	$svg = preg_replace( '/<\?xml.*?\?>/s', '', $svg );
	$svg = preg_replace( '/\sclass="[^"]*"/', '', $svg );
	$svg = preg_replace( '/\swidth="[^"]*"/', '', $svg );
	$svg = preg_replace( '/\sheight="[^"]*"/', '', $svg );
	$svg = preg_replace( '/\sstroke-width="[^"]*"/', '', $svg );
	$svg = str_replace( '<svg', '<svg class="moon-icon ' . esc_attr( $class ) . '" width="' . (int) $size . '" height="' . (int) $size . '" stroke-width="1.5" aria-hidden="true" focusable="false"', $svg );

	return $svg;
}

/**
 * Echo an icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Size.
 * @param string $class Extra classes.
 */
function moon_the_icon( $name, $size = 24, $class = '' ) {
	echo moon_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted local SVG.
}

/**
 * Absolute URL of a bundled theme image.
 *
 * @param string $file File name inside assets/images.
 * @return string
 */
function moon_asset( $file ) {
	return MOON_THEME_URI . '/assets/images/' . ltrim( $file, '/' );
}

/**
 * Render an image chosen in the settings panel, falling back to a bundled asset.
 *
 * @param string $option_key  Option holding the attachment ID.
 * @param string $fallback    Bundled file name.
 * @param string $size        Image size.
 * @param array  $attr        Extra attributes.
 * @param bool   $echo        Whether to print or return.
 * @return string
 */
function moon_image( $option_key, $fallback, $size = 'full', $attr = array(), $echo = true ) {
	$stored = get_option( 'moon_options', array() );
	$id     = isset( $stored[ $option_key ] ) ? (int) $stored[ $option_key ] : 0;
	$html   = '';
	$attrs  = wp_parse_args( $attr, array( 'alt' => '' ) );

	if ( $id ) {
		$html = wp_get_attachment_image( $id, $size, false, $attrs );
	}

	if ( ! $html ) {
		$url  = moon_asset( $fallback );
		$meta = '';

		foreach ( $attrs as $key => $value ) {
			if ( 'alt' === $key ) {
				continue;
			}
			$meta .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}

		$html = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $attrs['alt'] ) . '"' . $meta . ' loading="lazy" decoding="async">';
	}

	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
	}

	return $html;
}

/**
 * Bundled default rack garments (mirrors the source project data).
 *
 * @return array
 */
function moon_default_rack_items() {
	return array(
		array(
			'id'     => 'coat',
			'name'   => 'پالتو پشمی لونا',
			'en'     => 'Luna Wool Coat',
			'price'  => '۱۸٬۹۰۰٬۰۰۰ تومان',
			'image'  => moon_asset( 'g-coat.png' ),
			'colors' => array( '#101010', '#5b4a3a', '#99958F' ),
			'link'   => '',
		),
		array(
			'id'     => 'hoodie',
			'name'   => 'هودی اورسایز دون',
			'en'     => 'Dune Hoodie',
			'price'  => '۴٬۲۰۰٬۰۰۰ تومان',
			'image'  => moon_asset( 'g-hoodie.png' ),
			'colors' => array( '#d8c9b0', '#101010', '#F5F1E9' ),
			'link'   => '',
		),
		array(
			'id'     => 'jacket',
			'name'   => 'کاپشن چرم اکلیپس',
			'en'     => 'Eclipse Leather',
			'price'  => '۲۴٬۵۰۰٬۰۰۰ تومان',
			'image'  => moon_asset( 'g-jacket.png' ),
			'colors' => array( '#101010', '#3b2a20' ),
			'link'   => '',
		),
		array(
			'id'     => 'shirt',
			'name'   => 'پیراهن پاپلین نوا',
			'en'     => 'Nova Poplin Shirt',
			'price'  => '۲٬۸۰۰٬۰۰۰ تومان',
			'image'  => moon_asset( 'g-shirt.png' ),
			'colors' => array( '#FFFFFF', '#cfd8e3', '#101010' ),
			'link'   => '',
		),
		array(
			'id'     => 'sweat',
			'name'   => 'سویشرت کربن',
			'en'     => 'Carbon Crewneck',
			'price'  => '۳٬۶۰۰٬۰۰۰ تومان',
			'image'  => moon_asset( 'g-sweat.png' ),
			'colors' => array( '#3a3a3a', '#d8c9b0' ),
			'link'   => '',
		),
	);
}

/**
 * Bundled default outfits for the style studio.
 *
 * @return array
 */
function moon_default_outfits() {
	return array(
		array(
			'id'     => 1,
			'name'   => 'هودی اورسایز مشکی',
			'en'     => 'Midnight Hoodie',
			'image'  => moon_asset( 'outfit-1.jpg' ),
			'colors' => array( '#101010', '#FFFFFF' ),
			'link'   => '',
		),
		array(
			'id'     => 2,
			'name'   => 'سویشرت بژ',
			'en'     => 'Sand Crewneck',
			'image'  => moon_asset( 'outfit-2.jpg' ),
			'colors' => array( '#d8c9b0', '#101010' ),
			'link'   => '',
		),
		array(
			'id'     => 3,
			'name'   => 'پیراهن سفید مینیمال',
			'en'     => 'Pure White Shirt',
			'image'  => moon_asset( 'outfit-3.jpg' ),
			'colors' => array( '#FFFFFF', '#101010' ),
			'link'   => '',
		),
		array(
			'id'     => 4,
			'name'   => 'کاپشن چرم مشکی',
			'en'     => 'Black Leather',
			'image'  => moon_asset( 'outfit-4.jpg' ),
			'colors' => array( '#101010', '#F5F1E9' ),
			'link'   => '',
		),
		array(
			'id'     => 5,
			'name'   => 'استایل لایه‌ای',
			'en'     => 'Layered Casual',
			'image'  => moon_asset( 'outfit-5.jpg' ),
			'colors' => array( '#a68a6d', '#6b6b6b' ),
			'link'   => '',
		),
	);
}

/**
 * Rack items: managed records from MOON Core when available, bundled defaults otherwise.
 *
 * @return array
 */
function moon_rack_items() {
	$items = apply_filters( 'moon_rack_items', array() );

	if ( ! empty( $items ) ) {
		return $items;
	}

	return moon_default_rack_items();
}

/**
 * Outfits: managed records from MOON Core when available, bundled defaults otherwise.
 *
 * @return array
 */
function moon_outfits() {
	$items = apply_filters( 'moon_outfits', array() );

	if ( ! empty( $items ) ) {
		return $items;
	}

	return moon_default_outfits();
}

/**
 * Bundled demo products used when WooCommerce is unavailable.
 *
 * @return array
 */
function moon_demo_products() {
	$products = array();

	foreach ( moon_default_rack_items() as $item ) {
		$products[] = array(
			'name'  => $item['name'],
			'en'    => $item['en'],
			'price' => $item['price'],
			'image' => $item['image'],
			'url'   => '',
			'id'    => 0,
			'tag'   => 'hoodie' === $item['id'] ? '−۲۰٪' : ( 'coat' === $item['id'] ? 'جدید' : '' ),
			'cover' => false,
		);
	}

	$products[] = array(
		'name'  => 'ست ابریشم آیوری',
		'en'    => 'Ivory Silk Set',
		'price' => '۹٬۸۰۰٬۰۰۰ تومان',
		'image' => moon_asset( 'women.jpg' ),
		'url'   => '',
		'id'    => 0,
		'tag'   => 'جدید',
		'cover' => true,
	);

	$products[] = array(
		'name'  => 'پالتو چارکول',
		'en'    => 'Charcoal Overcoat',
		'price' => '۱۶٬۴۰۰٬۰۰۰ تومان',
		'image' => moon_asset( 'men.jpg' ),
		'url'   => '',
		'id'    => 0,
		'tag'   => '',
		'cover' => true,
	);

	return $products;
}

/**
 * Persian digits for prices and counters.
 *
 * @param string $value Raw value.
 * @return string
 */
function moon_fa_digits( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return $value;
	}

	$map = array(
		'0' => '۰',
		'1' => '۱',
		'2' => '۲',
		'3' => '۳',
		'4' => '۴',
		'5' => '۵',
		'6' => '۶',
		'7' => '۷',
		'8' => '۸',
		'9' => '۹',
	);

	/*
	 * Numeric character references (e.g. WooCommerce's currency symbols such as
	 * "&#x062A;") contain digits that must never be localised, otherwise the
	 * entity breaks and the symbol is printed as literal text.
	 */
	return preg_replace_callback(
		'/&#(?:x[0-9a-fA-F]+|[0-9]+);|[0-9]/',
		static function ( $matches ) use ( $map ) {
			$token = $matches[0];

			if ( '&' === $token[0] ) {
				return $token;
			}

			return $map[ $token ];
		},
		$value
	);
}

/**
 * Registered homepage sections, in the default order of the source design.
 *
 * @return array
 */
function moon_homepage_registry() {
	return array(
		'hero'         => array(
			'label' => 'هیرو',
			'desc'  => 'بخش تمام‌صفحه با تصویر پارالاکس و عنوان اصلی.',
		),
		'categories'   => array(
			'label' => 'دسته‌بندی‌ها',
			'desc'  => 'دو کارت کالکشن زنانه و مردانه.',
		),
		'rack'         => array(
			'label' => 'رگال تعاملی',
			'desc'  => 'لباس‌های آویزان روی رگال با پنل جزئیات.',
		),
		'studio'       => array(
			'label' => 'استودیو استایل',
			'desc'  => 'تغییر استایل مدل با تصاویر آماده.',
		),
		'products'     => array(
			'label' => 'محصولات',
			'desc'  => 'کاروسل افقی محصولات ووکامرس.',
		),
		'campaign'     => array(
			'label' => 'کمپین',
			'desc'  => 'بخش تصویری تمام‌عرض با پارالاکس.',
		),
		'story'        => array(
			'label' => 'داستان برند',
			'desc'  => 'متن برند، تصویر آتلیه و مزیت‌های خرید.',
		),
		'scrollstory'  => array(
			'label' => 'داستان اسکرولی',
			'desc'  => 'سه صحنه متحرک وابسته به اسکرول.',
		),
		'testimonials' => array(
			'label' => 'نظرات',
			'desc'  => 'اسلایدشو خودکار نظرات نمونه.',
		),
	);
}

/**
 * Ordered, enabled homepage sections.
 *
 * @return array Section id => definition.
 */
function moon_home_sections() {
	$registry = moon_homepage_registry();
	$stored   = get_option( 'moon_sections', array() );
	$result   = array();
	$has_rows = is_array( $stored ) && ! empty( $stored );

	if ( $has_rows ) {
		foreach ( $stored as $row ) {
			$id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';

			if ( ! isset( $registry[ $id ] ) || isset( $result[ $id ] ) ) {
				continue;
			}

			if ( empty( $row['enabled'] ) ) {
				continue;
			}

			$result[ $id ] = $registry[ $id ];
		}

		if ( empty( $result ) ) {
			return array();
		}

		return $result;
	}

	return $registry;
}

/**
 * Split footer link text ("label|url" per line) into rows.
 *
 * @param string $raw Raw option value.
 * @return array
 */
function moon_footer_links( $raw ) {
	$rows = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line ) );

		if ( '' === $parts[0] ) {
			continue;
		}

		$rows[] = array(
			'label' => $parts[0],
			'url'   => isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] : '#',
		);
	}

	return $rows;
}

/**
 * Rack items shaped for rendering and for the JavaScript panel.
 *
 * Links to real WooCommerce products (when a product ID is stored) supply the
 * price, the permalink and a working add-to-cart URL.
 *
 * @return array
 */
function moon_rack_items_view() {
	$items = moon_rack_items();
	$view  = array();

	foreach ( $items as $index => $item ) {
		$product_id = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
		$product    = ( $product_id && moon_is_woo() ) ? wc_get_product( $product_id ) : null;

		$image = isset( $item['image'] ) ? $item['image'] : '';

		if ( ! $image && $product ) {
			$image = wp_get_attachment_image_url( $product->get_image_id(), 'moon-card' );
		}

		$link    = '';
		$add_url = '';

		if ( $product ) {
			$link = get_permalink( $product->get_id() );

			if ( $product->is_type( 'simple' ) && $product->is_in_stock() ) {
				$add_url = $product->add_to_cart_url();
			}
		} elseif ( ! empty( $item['link'] ) ) {
			$link = $item['link'];
		}

		$colors = isset( $item['colors'] ) ? $item['colors'] : array();

		if ( ! is_array( $colors ) ) {
			$colors = array_filter( array_map( 'trim', explode( ',', (string) $colors ) ) );
		}

		$view[] = array(
			'id'     => isset( $item['id'] ) ? sanitize_key( (string) $item['id'] ) : 'item-' . $index,
			'name'   => isset( $item['name'] ) ? $item['name'] : '',
			'en'     => isset( $item['en'] ) ? $item['en'] : '',
			'price'  => $product ? $product->get_price_html() : ( isset( $item['price'] ) ? $item['price'] : '' ),
			'image'  => $image,
			'link'   => $link,
			'addUrl' => $add_url,
			'note'   => $product ? '' : moon_opt( 'rack_note' ),
			'colors' => array_values( $colors ),
		);
	}

	return $view;
}

/**
 * Outfits shaped for rendering and for the studio script.
 *
 * @return array
 */
function moon_outfits_view() {
	$items = moon_outfits();
	$view  = array();

	foreach ( $items as $index => $item ) {
		$product_id = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
		$product    = ( $product_id && moon_is_woo() ) ? wc_get_product( $product_id ) : null;

		$image = isset( $item['image'] ) ? $item['image'] : '';

		if ( ! $image && $product ) {
			$image = wp_get_attachment_image_url( $product->get_image_id(), 'moon-card' );
		}

		$colors = isset( $item['colors'] ) ? $item['colors'] : array();

		if ( ! is_array( $colors ) ) {
			$colors = array_filter( array_map( 'trim', explode( ',', (string) $colors ) ) );
		}

		$link = '';

		if ( ! empty( $item['look'] ) ) {
			$link = $item['look'];
		} elseif ( $product ) {
			$link = get_permalink( $product->get_id() );
		} elseif ( ! empty( $item['link'] ) ) {
			$link = $item['link'];
		}

		$view[] = array(
			'id'       => isset( $item['id'] ) ? (int) $item['id'] : $index + 1,
			'name'     => isset( $item['name'] ) ? $item['name'] : '',
			'en'       => isset( $item['en'] ) ? $item['en'] : '',
			'image'    => $image,
			'colors'   => array_values( $colors ),
			'link'     => $link ? $link : moon_shop_url(),
			'products' => isset( $item['products'] ) ? array_map( 'intval', (array) $item['products'] ) : array(),
			'desc'     => isset( $item['desc'] ) ? $item['desc'] : '',
		);
	}

	return $view;
}
