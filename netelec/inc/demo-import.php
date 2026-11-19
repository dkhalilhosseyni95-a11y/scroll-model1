<?php
/**
 * Optional demo content importer (products for evaluating the theme).
 *
 * @package Netelec
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a simple abstract product image with GD.
 *
 * @param string $seed Seed for the palette.
 * @return int Attachment id, 0 on failure.
 */
function netelec_demo_image( $seed ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return 0;
	}

	$palettes = array(
		array( array( 37, 99, 235 ), array( 14, 23, 42 ) ),
		array( array( 16, 185, 129 ), array( 6, 78, 59 ) ),
		array( array( 249, 115, 22 ), array( 124, 45, 18 ) ),
		array( array( 99, 102, 241 ), array( 30, 27, 75 ) ),
		array( array( 14, 165, 233 ), array( 12, 74, 110 ) ),
		array( array( 236, 72, 153 ), array( 131, 24, 67 ) ),
	);

	$palette = $palettes[ $seed % count( $palettes ) ];
	$width   = 800;
	$height  = 800;

	$image = imagecreatetruecolor( $width, $height );

	for ( $y = 0; $y < $height; $y++ ) {
		$ratio = $y / $height;
		$r     = (int) ( $palette[0][0] + ( $palette[1][0] - $palette[0][0] ) * $ratio );
		$g     = (int) ( $palette[0][1] + ( $palette[1][1] - $palette[0][1] ) * $ratio );
		$b     = (int) ( $palette[0][2] + ( $palette[1][2] - $palette[0][2] ) * $ratio );
		imageline( $image, 0, $y, $width, $y, imagecolorallocate( $image, $r, $g, $b ) );
	}

	$white = imagecolorallocate( $image, 255, 255, 255 );
	$soft  = imagecolorallocatealpha( $image, 255, 255, 255, 100 );

	imagefilledellipse( $image, 560, 220, 420, 420, $soft );
	imagefilledellipse( $image, 220, 620, 300, 300, $soft );
	imagefilledrectangle( $image, 250, 300, 550, 500, $white );
	imagefilledrectangle( $image, 290, 340, 510, 460, imagecolorallocate( $image, $palette[1][0], $palette[1][1], $palette[1][2] ) );
	imagestring( $image, 5, 300, 640, 'NETELEC', $white );

	ob_start();
	imagejpeg( $image, null, 88 );
	$data = ob_get_clean();
	imagedestroy( $image );

	$upload = wp_upload_bits( 'netelec-demo-' . $seed . '-' . wp_generate_password( 6, false ) . '.jpg', null, $data );

	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'NETELEC demo ' . $seed,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );

	return $attachment_id;
}

/**
 * Does a product with this exact title already exist?
 *
 * @param string $title Product title.
 * @return bool
 */
function netelec_demo_product_exists( $title ) {
	$query = new WP_Query(
		array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	return $query->have_posts();
}

/**
 * Create a few demo blog posts so the blog layout can be evaluated.
 *
 * @return int Number of created posts.
 */
function netelec_seed_demo_posts() {
	$category = term_exists( 'راهنمای شبکه', 'category' );
	if ( ! $category ) {
		$category = wp_insert_term( 'راهنمای شبکه', 'category' );
	}
	$category_id = ! is_wp_error( $category ) && isset( $category['term_id'] ) ? (int) $category['term_id'] : 0;

	$posts = array(
		array(
			'title'   => 'Cat5e یا Cat6؟ راهنمای انتخاب کابل شبکه برای دفتر کار',
			'slug'    => 'cat5e-ya-cat6',
			'excerpt' => 'انتخاب کابل شبکه فقط به سرعت اشاره ندارد؛ طول مسیر، نویز محیط و نیاز آینده هم مهم است.',
			'content' => '<p>وقتی صحبت از کابل‌کشی شبکه در یک دفتر کار می‌شود، دو گزینه رایج همیشه مطرح است: Cat5e و Cat6. هر دو استاندارد گیگابیتی هستند اما تفاوت‌هایی دارند که در پروژه‌های بزرگ به چشم می‌آید.</p>
<h2>پهنای باند و فرکانس</h2>
<p>کابل Cat5e تا فرکانس ۱۰۰ مگاهرتز و Cat6 تا ۲۵۰ مگاهرتز آزمون می‌شود. در مسیرهای کوتاه هر دو گیگابیت را پاس می‌کنند، اما در طول‌های بیشتر و محیط‌های پرنویز، حاشیه اطمینان Cat6 بیشتر است.</p>
<h2>نویز و هم‌شنوایی</h2>
<p>در Cat6 جداکننده داخلی (اسپلاین) فاصله جفت‌ها را حفظ می‌کند و هم‌شنوایی را کاهش می‌دهد. اگر کابل‌ها در مسیر موتور، تابلو برق یا کابل برق کشیده می‌شوند، این ویژگی اهمیت دارد.</p>
<h2>پیشنهاد عملی</h2>
<ul><li>برای دفتر کوچک تا ۲۰ کلاینت، Cat5e کافی است.</li><li>برای پروژه‌های جدید و مسیرهای بالای ۴۰ متر، Cat6 انتخاب مطمئن‌تری است.</li><li>همیشه کابل را با سوکت و کیستون هم‌رده (Cat6 با Cat6) استفاده کنید.</li></ul>
<p>در نهایت، کیفیت اجرا به اندازه کیفیت کابل مهم است؛ خم‌شدن‌های تند و سوکت‌زنی نادرست می‌تواند عملکرد بهترین کابل را هم از بین ببرد.</p>',
		),
		array(
			'title'   => 'PoE چیست و چطور تجهیزات شبکه را بدون آداپتور تغذیه کنیم؟',
			'slug'    => 'poe-chist',
			'excerpt' => 'با PoE می‌توان اکسس‌پوینت و دوربین را تنها با یک کابل شبکه هم تغذیه کرد و هم به شبکه وصل کرد.',
			'content' => '<p>PoE یا Power over Ethernet استانداردی است که برق و داده را روی یک کابل شبکه منتقل می‌کند. نتیجه ساده است: کمتر شدن آداپتور، کمتر شدن پریز برق و نصب تمیزتر.</p>
<h2>استانداردهای مهم</h2>
<p>استاندارد 802.3af تا ۱۵.۴ وات و استاندارد 802.3at (PoE+) تا ۳۰ وات توان در هر پورت ارائه می‌دهد. برای اکسس‌پوینت‌های معمول af کافی است، اما دوربین‌های چرخشی و اکسس‌پوینت‌های Wi-Fi 6 معمولاً به at نیاز دارند.</p>
<h2>نکته‌های نصب</h2>
<ul><li>بودجه توان کل سوییچ را بررسی کنید، نه فقط توان هر پورت.</li><li>کابل Cat5e برای PoE کافی است، اما Cat6 تلفات کمتری دارد.</li><li>برای مسیرهای طولانی، افت ولتاژ را در محاسبات لحاظ کنید.</li></ul>
<p>اگر تجهیزات شما PoE را پشتیبانی نمی‌کنند، می‌توان از اینجکتور یا اسپلیتر استاندارد استفاده کرد؛ اما انتخاب سوییچ PoE در پروژه‌های جدید ساده‌تر و مطمئن‌تر است.</p>',
		),
		array(
			'title'   => 'چیدمان رک شبکه: از پچ پنل تا مدیریت کابل',
			'slug'    => 'chideman-rack-shabake',
			'excerpt' => 'یک رک مرتب، عیب‌یابی را سریع‌تر می‌کند و عمر تجهیزات را بالا می‌برد. چند اصل ساده برای چیدمان درست.',
			'content' => '<p>رک شبکه قلب یک شبکه سازمانی است. ترتیب درست اجزا و مدیریت کابل، تفاوت بین یک رک حرفه‌ای و یک رک پر از کابل گره‌خورده است.</p>
<h2>ترتیب پیشنهادی</h2>
<ol><li>در بالای رک، پچ پنل و مرتب‌کننده افقی قرار می‌گیرد.</li><li>سوییچ‌ها بلافاصله زیر پچ پنل نصب می‌شوند تا پچ کوردها کوتاه بمانند.</li><li>تجهیزات سنگین‌تر در پایین رک قرار می‌گیرند.</li></ol>
<h2>مدیریت کابل</h2>
<p>استفاده از پچ کوردهای با طول درست، بست کابل و برچسب‌گذاری دو سر کابل، زمان عیب‌یابی را به شدت کاهش می‌دهد. برای مسیرهای عمودی از داکت یا حلقه نگه‌دارنده استفاده کنید.</p>
<h2>تهویه</h2>
<p>فاصله بین تجهیزات را حفظ کنید و از پنل‌های خالی با دریچه عبور هوا استفاده کنید. دمای بالای رک، عمر سوییچ و منبع تغذیه را کم می‌کند.</p>',
		),
	);

	$created = 0;

	foreach ( $posts as $index => $post ) {
		if ( netelec_demo_product_exists( $post['title'] ) ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_title'    => $post['title'],
				'post_name'     => $post['slug'],
				'post_content'  => $post['content'],
				'post_excerpt'  => $post['excerpt'],
				'post_status'   => 'publish',
				'post_type'     => 'post',
				'post_category' => $category_id ? array( $category_id ) : array(),
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		$image_id = netelec_demo_image( 20 + $index );
		if ( $image_id ) {
			set_post_thumbnail( $post_id, $image_id );
		}

		$created++;
	}

	return $created;
}

/**
 * Create the demo catalog.
 *
 * @return array|WP_Error
 */
function netelec_seed_demo_content() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return new WP_Error( 'netelec_demo', 'ووکامرس فعال نیست.' );
	}

	$categories = array(
		'کابل شبکه'        => 'کابل‌های Cat6 و Cat5e برای پروژه‌های شبکه',
		'سوکت و اتصالات'   => 'سوکت RJ45، کیستون و کوپلر',
		'تجهیزات اکتیو'    => 'سوییچ، روتر و اکسس‌پوینت',
		'تجهیزات پسیو'     => 'پچ پنل، رک و ترانک',
		'ابزار شبکه'       => 'آچار سوکت‌زن، تستر و ابزار نصب',
	);

	$term_ids = array();

	foreach ( $categories as $name => $description ) {
		$existing = term_exists( $name, 'product_cat' );
		if ( $existing ) {
			$term_ids[ $name ] = (int) $existing['term_id'];
			continue;
		}
		$term = wp_insert_term( $name, 'product_cat', array( 'description' => $description ) );
		if ( ! is_wp_error( $term ) ) {
			$term_ids[ $name ] = (int) $term['term_id'];
		}
	}

	$items = array(
		array( 'کابل شبکه Cat6 شیلددار — ۳۰۵ متر', 'کابل شبکه', 4850000, 0, 'کابل Cat6 با شیلد F/UTP مناسب شبکه‌های گیگابیتی و پروژه‌های صنعتی.', 'in', 'kabel-shabake-cat6-305m' ),
		array( 'سوکت RJ45 Cat6 — بسته ۱۰۰ عددی', 'سوکت و اتصالات', 1250000, 0, 'سوکت RJ45 سه‌شاخه با پوشش طلا، مناسب کابل Cat6.', 'in', 'soket-rj45-cat6-100' ),
		array( 'کیستون Cat6 ماژولار', 'سوکت و اتصالات', 185000, 145000, 'کیستون ماژولار Cat6 برای نصب در پچ پنل و پریز.', 'in', 'kiston-cat6-modular' ),
		array( 'سوییچ ۸ پورت گیگابیتی مدیریتی', 'تجهیزات اکتیو', 8950000, 0, 'سوییچ ۸ پورت Gigabit با پشتیبانی VLAN و مدیریت وب.', 'in', 'switch-8-port-gigabit' ),
		array( 'اکسس‌پوینت سقفی دو باند', 'تجهیزات اکتیو', 6750000, 5900000, 'اکسس‌پوینت Wi-Fi 6 دو باند با پشتیبانی PoE.', 'in', 'access-point-sefqi' ),
		array( 'پچ پنل ۲۴ پورت Cat6', 'تجهیزات پسیو', 3250000, 0, 'پچ پنل ۱۹ اینچی ۲۴ پورت با کابل‌کشی مرتب.', 'in', 'patch-panel-24-port' ),
		array( 'آچار سوکت‌زن حرفه‌ای RJ45', 'ابزار شبکه', 890000, 0, 'آچار سوکت‌زن با تیغه فولادی و بدنه ارگونومیک.', 'in', 'avare-soketzan-rj45' ),
		array( 'تستر کابل شبکه', 'ابزار شبکه', 1450000, 0, 'تستر کابل شبکه برای بررسی صحت سیم‌بندی.', 'out', 'tester-kabel-shabake' ),
	);

	$created = 0;

	foreach ( $items as $index => $item ) {
		if ( netelec_demo_product_exists( $item[0] ) ) {
			continue;
		}

		$product = new WC_Product_Simple();
		$product->set_name( $item[0] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_description( $item[4] . "\n\nاین محصول نمونه برای ارزیابی قالب ایجاد شده است." );
		$product->set_short_description( $item[4] );
		$product->set_regular_price( (string) $item[2] );

		if ( $item[3] ) {
			$product->set_sale_price( (string) $item[3] );
		}

		$product->set_manage_stock( true );
		$product->set_stock_quantity( 'out' === $item[5] ? 0 : 25 );
		$product->set_stock_status( 'out' === $item[5] ? 'outofstock' : 'instock' );
		$product->set_sku( 'NC-' . ( 1000 + $index ) );

		if ( ! empty( $item[6] ) ) {
			$product->set_slug( $item[6] );
		}

		if ( isset( $term_ids[ $item[1] ] ) ) {
			$product->set_category_ids( array( $term_ids[ $item[1] ] ) );
		}

		$image_id = netelec_demo_image( $index );
		if ( $image_id ) {
			$product->set_image_id( $image_id );
		}

		$product->save();
		$created++;
	}

	// One variable product.
	if ( ! netelec_demo_product_exists( 'پچ کورد Cat6 (طول‌های مختلف)' ) ) {
		$variable = new WC_Product_Variable();
		$variable->set_name( 'پچ کورد Cat6 (طول‌های مختلف)' );
		$variable->set_status( 'publish' );
		$variable->set_description( 'پچ کورد Cat6 با طول‌های مختلف برای اتصال تجهیزات شبکه.' );
		$variable->set_short_description( 'پچ کورد Cat6 آماده استفاده در رک و پچ پنل.' );
		$variable->set_sku( 'NC-2000' );
		$variable->set_slug( 'patch-cord-cat6' );

		$attribute = new WC_Product_Attribute();
		$attribute->set_id( 0 );
		$attribute->set_name( 'طول' );
		$attribute->set_options( array( '۱ متر', '۲ متر', '۵ متر' ) );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$variable->set_attributes( array( $attribute ) );

		if ( isset( $term_ids['کابل شبکه'] ) ) {
			$variable->set_category_ids( array( $term_ids['کابل شبکه'] ) );
		}

		$image_id = netelec_demo_image( 9 );
		if ( $image_id ) {
			$variable->set_image_id( $image_id );
		}

		$variable->save();

		$variations = array(
			'۱ متر' => 85000,
			'۲ متر' => 125000,
			'۵ متر' => 245000,
		);

		foreach ( $variations as $option => $price ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $variable->get_id() );
			$variation->set_attributes( array( 'طول' => $option ) );
			$variation->set_regular_price( (string) $price );
			$variation->set_manage_stock( true );
			$variation->set_stock_quantity( 40 );
			$variation->set_status( 'publish' );
			$variation->save();
		}

		$created++;
	}

	return array(
		'products'   => $created,
		'categories' => count( $term_ids ),
		'posts'      => netelec_seed_demo_posts(),
	);
}

/**
 * AJAX: seed demo content.
 */
function netelec_ajax_seed_demo() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'دسترسی مجاز نیست.' ), 403 );
	}

	check_ajax_referer( 'netelec_admin', 'nonce' );

	$result = netelec_seed_demo_content();

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success(
		array(
			'message' => sprintf(
				'%s محصول، %s دسته‌بندی و %s مقاله نمونه ساخته شد.',
				number_format_i18n( $result['products'] ),
				number_format_i18n( $result['categories'] ),
				number_format_i18n( $result['posts'] )
			),
		)
	);
}
add_action( 'wp_ajax_netelec_seed_demo', 'netelec_ajax_seed_demo' );
