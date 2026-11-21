<?php
/**
 * MOON store catalogue.
 *
 * Builds the storefront's category architecture, the mega menu and a full demo
 * catalogue on top of WooCommerce: seven main departments, every subcategory
 * from the store brief, brands, materials, colours, sizes, stock, SKU and
 * unique Persian copy per product.
 *
 * Idempotent. Run with `wp moon-demo store` or as part of
 * `wp moon-demo import --products --catalog`.
 *
 * @package Moon_Demo_Importer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Catalogue builder.
 */
class Moon_Store_Catalog {

	/**
	 * Bump when the structure changes so a re-run upgrades an older import.
	 */
	const VERSION = '3.1.0';

	/**
	 * Departments, their subcategories and the mega-menu column groups.
	 *
	 * @return array
	 */
	public static function tree() {
		return array(
			'men'        => array(
				'name'     => 'مردانه',
				'children' => array(
					'men-tshirt-polo' => 'تیشرت و پولوشرت',
					'men-shirt'       => 'پیراهن مردانه',
					'men-jeans'       => 'شلوار جین',
					'men-trousers'    => 'شلوار پارچه‌ای',
					'men-shorts'      => 'شلوارک',
					'men-hoodie'      => 'هودی و سویشرت',
					'men-sweatpants'  => 'دورس',
					'men-knit'        => 'بافت و پلیور',
					'men-coat'        => 'کاپشن و پالتو',
					'men-suit'        => 'کت و لباس رسمی',
					'men-sport'       => 'لباس ورزشی',
					'men-underwear'   => 'لباس زیر و راحتی',
					'men-accessories' => 'اکسسوری مردانه',
				),
				'groups'   => array(
					'بالاتنه'             => array( 'men-tshirt-polo', 'men-shirt', 'men-hoodie', 'men-knit' ),
					'پایین‌تنه'           => array( 'men-jeans', 'men-trousers', 'men-shorts', 'men-sweatpants' ),
					'پوشاک گرم و رسمی'    => array( 'men-coat', 'men-suit', 'men-sport' ),
					'راحتی و اکسسوری'     => array( 'men-underwear', 'men-accessories' ),
				),
			),
			'women'      => array(
				'name'     => 'زنانه',
				'children' => array(
					'women-manto'      => 'مانتو',
					'women-blouse'     => 'شومیز',
					'women-top'        => 'بلوز و تاپ',
					'women-tshirt'     => 'تیشرت',
					'women-trousers'   => 'شلوار زنانه',
					'women-jeans'      => 'شلوار جین',
					'women-skirt'      => 'دامن',
					'women-dress'      => 'پیراهن زنانه',
					'women-hoodie'     => 'هودی و سویشرت',
					'women-knit'       => 'بافت و پلیور',
					'women-coat'       => 'کت و پالتو',
					'women-evening'    => 'لباس مجلسی',
					'women-loungewear' => 'لباس راحتی',
					'women-sport'      => 'لباس ورزشی',
					'women-lingerie'   => 'لباس زیر',
				),
				'groups'   => array(
					'بالاتنه'          => array( 'women-manto', 'women-blouse', 'women-top', 'women-tshirt' ),
					'پایین‌تنه'        => array( 'women-trousers', 'women-jeans', 'women-skirt' ),
					'پیراهن و مجلسی'   => array( 'women-dress', 'women-evening', 'women-coat' ),
					'بافت و راحتی'     => array( 'women-hoodie', 'women-knit', 'women-loungewear', 'women-sport', 'women-lingerie' ),
				),
			),
			'kids'       => array(
				'name'     => 'بچگانه',
				'children' => array(
					'kids-baby'       => 'لباس نوزادی',
					'kids-girls'      => 'لباس دخترانه',
					'kids-boys'       => 'لباس پسرانه',
					'kids-tshirt'     => 'تیشرت و بلوز',
					'kids-pants'      => 'شلوار و شلوارک',
					'kids-shirt'      => 'پیراهن',
					'kids-hoodie'     => 'هودی و سویشرت',
					'kids-outerwear'  => 'کاپشن و لباس گرم',
					'kids-loungewear' => 'لباس راحتی',
					'kids-set'        => 'ست لباس کودک',
				),
				'groups'   => array(
					'بر اساس گروه سنی' => array( 'kids-baby', 'kids-girls', 'kids-boys' ),
					'بالاتنه'           => array( 'kids-tshirt', 'kids-shirt', 'kids-hoodie' ),
					'پایین‌تنه'         => array( 'kids-pants', 'kids-outerwear' ),
					'ست و راحتی'        => array( 'kids-loungewear', 'kids-set' ),
				),
			),
			'scarves'    => array(
				'name'     => 'شال و روسری',
				'children' => array(
					'scarf-plain'   => 'شال ساده',
					'scarf-pattern' => 'شال طرح‌دار',
					'hijab-cotton'  => 'روسری نخی',
					'hijab-silk'    => 'روسری ابریشمی',
					'hijab-party'   => 'روسری مجلسی',
					'mini-scarf'    => 'مینی اسکارف',
					'scarf-winter'  => 'شال پاییزه و زمستانه',
				),
				'groups'   => array(
					'شال'        => array( 'scarf-plain', 'scarf-pattern', 'scarf-winter' ),
					'روسری'      => array( 'hijab-cotton', 'hijab-silk', 'hijab-party' ),
					'مینی اسکارف' => array( 'mini-scarf' ),
				),
			),
			'bags-shoes' => array(
				'name'     => 'کیف و کفش',
				'children' => array(
					'bag-women'    => 'کیف زنانه',
					'bag-men'      => 'کیف مردانه',
					'bag-hand'     => 'کیف دستی',
					'bag-shoulder' => 'کیف دوشی',
					'shoes-women'  => 'کفش زنانه',
					'shoes-men'    => 'کفش مردانه',
					'sneakers'     => 'کتانی',
					'sandals'      => 'صندل و دمپایی',
				),
				'groups'   => array(
					'کیف'  => array( 'bag-women', 'bag-men', 'bag-hand', 'bag-shoulder' ),
					'کفش' => array( 'shoes-women', 'shoes-men', 'sneakers', 'sandals' ),
				),
			),
			'accessories' => array(
				'name'     => 'اکسسوری',
				'children' => array(
					'belt'             => 'کمربند',
					'hat'              => 'کلاه',
					'glasses'          => 'عینک',
					'watch'            => 'ساعت',
					'socks'            => 'جوراب',
					'jewelry'          => 'زیورآلات',
					'hair-accessories' => 'اکسسوری مو',
				),
				'groups'   => array(
					'اکسسوری' => array( 'belt', 'hat', 'glasses', 'watch' ),
					'جزئیات'   => array( 'socks', 'jewelry', 'hair-accessories' ),
				),
			),
		);
	}

	/**
	 * Brands carried by the store.
	 *
	 * @return array slug => name
	 */
	public static function brands() {
		return array(
			'moon'       => 'MOON',
			'atelier'    => 'MOON ATELIER',
			'studio'     => 'MOON STUDIO',
			'essentials' => 'MOON ESSENTIALS',
		);
	}

	/**
	 * Colour terms. `wine` used to be labelled «زغالی» (charcoal) by mistake.
	 *
	 * @return array slug => [name, hex]
	 */
	public static function palette() {
		return array(
			'black'    => array( 'مشکی', '#101010' ),
			'snow'     => array( 'استخوانی', '#F5F1E9' ),
			'cream'    => array( 'کرم', '#EFE7DA' ),
			'sand'     => array( 'شنی', '#D8C9B0' ),
			'camel'    => array( 'شتری', '#C8A97E' ),
			'brown'    => array( 'قهوه‌ای', '#5B4A3A' ),
			'charcoal' => array( 'ذغالی', '#3A3A3A' ),
			'grey'     => array( 'خاکستری', '#99958F' ),
			'blue'     => array( 'آبی روشن', '#CFD8E3' ),
			'olive'    => array( 'زیتونی', '#6B6B4F' ),
			'wine'     => array( 'زرشکی', '#6E1F2B' ),
			'navy'     => array( 'سرمه‌ای', '#1F2A44' ),
		);
	}

	/**
	 * Materials used by the products.
	 *
	 * @return array slug => name
	 */
	public static function materials() {
		return array(
			'cotton'   => 'پنبه',
			'wool'     => 'پشم',
			'silk'     => 'ابریشم',
			'linen'    => 'کتان',
			'leather'  => 'چرم',
			'denim'    => 'جین',
			'viscose'  => 'ویسکوز',
			'satin'    => 'ساتن',
			'cashmere' => 'کشمیر',
			'knit'     => 'بافت',
		);
	}

	/**
	 * Adult size run.
	 *
	 * @return array
	 */
	public static function sizes() {
		return array( 'S', 'M', 'L', 'XL' );
	}

	/**
	 * Demo catalogue.
	 *
	 * @return array
	 */
	public static function products() {
		return array(
			// ---------------------------------------------------------- مردانه
			array(
				'name'     => 'پیراهن آکسفورد کلاسیک',
				'slug'     => 'oxford-classic-shirt',
				'cat'      => 'men-shirt',
				'price'    => 2650000,
				'image'    => 'g-shirt.webp',
				'gallery'  => array( 'outfit-3.jpg' ),
				'colors'   => array( 'snow', 'blue' ),
				'material' => 'cotton',
				'fit'      => 'رگولار',
				'brand'    => 'moon',
				'desc'     => 'پیراهن آکسفورد با یقه دکمه‌دار و بافت مقاوم؛ از جلسه صبح تا قرار شب بدون چین و شکن می‌ماند.',
				'tags'     => array( 'پیراهن', 'کلاسیک', 'پنبه' ),
				'featured' => true,
			),
			array(
				'name'     => 'پیراهن فلانل زمستانی',
				'slug'     => 'flannel-winter-shirt',
				'cat'      => 'men-shirt',
				'price'    => 3150000,
				'sale'     => 2520000,
				'image'    => 'outfit-3.jpg',
				'gallery'  => array( 'g-shirt.webp' ),
				'colors'   => array( 'charcoal', 'olive' ),
				'material' => 'cotton',
				'fit'      => 'راحت',
				'brand'    => 'essentials',
				'desc'     => 'فلانل نرم با پرز کوتاه و دوخت دوبل در سرشانه؛ لایه‌ی گرمی که با تیشرت هم ست می‌شود.',
				'tags'     => array( 'پیراهن', 'زمستانی', 'فلانل' ),
			),
			array(
				'name'     => 'پیراهن جین کژوال',
				'slug'     => 'denim-casual-shirt',
				'cat'      => 'men-shirt',
				'price'    => 3250000,
				'image'    => 'outfit-5.jpg',
				'gallery'  => array( 'g-shirt.webp' ),
				'colors'   => array( 'blue', 'sand' ),
				'material' => 'denim',
				'fit'      => 'اورسایز',
				'brand'    => 'studio',
				'desc'     => 'پیراهن جین سبک با دکمه‌های مات و جیب سینه؛ برش آزاد برای استایل لایه‌ای آخر هفته.',
				'tags'     => array( 'پیراهن', 'جین', 'کژوال' ),
			),
			array(
				'name'     => 'شلوار جین اسلیم مشکی',
				'slug'     => 'slim-black-jeans',
				'cat'      => 'men-jeans',
				'price'    => 3450000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'outfit-5.jpg' ),
				'colors'   => array( 'black', 'navy' ),
				'material' => 'denim',
				'fit'      => 'اسلیم',
				'brand'    => 'moon',
				'desc'     => 'جین اسلیم با کشسانی کم و رنگ ثابت؛ فرم پا را نگه می‌دارد و با کتانی و بوت یکسان می‌نشیند.',
				'tags'     => array( 'جین', 'اسلیم', 'مشکی' ),
			),
			array(
				'name'     => 'شلوار جین راست آبی',
				'slug'     => 'straight-blue-jeans',
				'cat'      => 'men-jeans',
				'price'    => 3650000,
				'sale'     => 2920000,
				'image'    => 'outfit-5.jpg',
				'gallery'  => array( 'outfit-1.jpg' ),
				'colors'   => array( 'blue', 'navy' ),
				'material' => 'denim',
				'fit'      => 'راست',
				'brand'    => 'essentials',
				'desc'     => 'جین راست‌برش با شست‌وشوی متوسط و دوخت تقویت‌شده؛ همان شلوار جینی که سال‌ها می‌ماند.',
				'tags'     => array( 'جین', 'راست', 'روزمره' ),
			),
			array(
				'name'     => 'شلوار پارچه‌ای فاخر',
				'slug'     => 'tailored-wool-trousers',
				'cat'      => 'men-trousers',
				'price'    => 4250000,
				'image'    => 'outfit-3.jpg',
				'gallery'  => array( 'outfit-5.jpg' ),
				'colors'   => array( 'charcoal', 'navy' ),
				'material' => 'wool',
				'fit'      => 'دم‌پا',
				'brand'    => 'atelier',
				'desc'     => 'شلوار پشمی با پیلی تیز و کمر تنظیم‌شو؛ خط پا را بلند می‌کند و برای مجالس رسمی ساخته شده.',
				'tags'     => array( 'شلوار', 'رسمی', 'پشم' ),
			),
			array(
				'name'     => 'شلوار کتان اسلیم',
				'slug'     => 'slim-cotton-chinos',
				'cat'      => 'men-trousers',
				'price'    => 2950000,
				'image'    => 'outfit-5.jpg',
				'gallery'  => array( 'outfit-3.jpg' ),
				'colors'   => array( 'sand', 'olive' ),
				'material' => 'cotton',
				'fit'      => 'اسلیم',
				'brand'    => 'moon',
				'desc'     => 'چینوی کتان با کمر کشی پنهان و جیب‌های مورب؛ راحتی روزمره با ظاهر مرتب.',
				'tags'     => array( 'شلوار', 'کتان', 'چینو' ),
			),
			array(
				'name'     => 'هودی زیپ‌دار کربن',
				'slug'     => 'carbon-zip-hoodie',
				'cat'      => 'men-hoodie',
				'price'    => 3950000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'g-hoodie.webp' ),
				'colors'   => array( 'black', 'grey' ),
				'material' => 'cotton',
				'fit'      => 'اورسایز',
				'brand'    => 'studio',
				'desc'     => 'هودی زیپ‌دار با پارچه سنگین ۳۲۰ گرم و کلاه دو لایه؛ فرم اورسایز بدون افتادگی.',
				'tags'     => array( 'هودی', 'زیپ‌دار', 'اورسایز' ),
				'featured' => true,
			),
			array(
				'name'     => 'هودی پنبه‌ای ابر',
				'slug'     => 'cloud-cotton-hoodie',
				'cat'      => 'men-hoodie',
				'price'    => 3450000,
				'image'    => 'outfit-2.jpg',
				'gallery'  => array( 'g-hoodie.webp' ),
				'colors'   => array( 'sand', 'snow' ),
				'material' => 'cotton',
				'fit'      => 'راحت',
				'brand'    => 'essentials',
				'desc'     => 'هودی پنبه‌ای نرم با آستر بدنه و کشسانی ملایم؛ سبک، گرم و بی‌دردسر.',
				'tags'     => array( 'هودی', 'پنبه', 'روزمره' ),
			),
			array(
				'name'     => 'دورس اسلیم مشکی',
				'slug'     => 'slim-jogger',
				'cat'      => 'men-sweatpants',
				'price'    => 2250000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'outfit-2.jpg' ),
				'colors'   => array( 'black', 'charcoal' ),
				'material' => 'cotton',
				'fit'      => 'اسلیم',
				'brand'    => 'moon',
				'desc'     => 'دورس اسلیم با مچ کشباف و جیب زیپ‌دار؛ برای سفر و روزهای خانه.',
				'tags'     => array( 'دورس', 'راحتی', 'اسلیم' ),
			),
			array(
				'name'     => 'دورس کتان راحتی',
				'slug'     => 'relaxed-jogger',
				'cat'      => 'men-sweatpants',
				'price'    => 2150000,
				'sale'     => 1720000,
				'image'    => 'outfit-2.jpg',
				'gallery'  => array( 'outfit-1.jpg' ),
				'colors'   => array( 'grey', 'sand' ),
				'material' => 'cotton',
				'fit'      => 'راحت',
				'brand'    => 'essentials',
				'desc'     => 'دورس با کمر کشی و بند پارچه‌ای؛ برش راحت و پارچه‌ی خنک برای تمام روز.',
				'tags'     => array( 'دورس', 'راحتی', 'کتان' ),
			),
			array(
				'name'     => 'بافت مرینوس گردن‌اسکی',
				'slug'     => 'merino-turtleneck',
				'cat'      => 'men-knit',
				'price'    => 4850000,
				'image'    => 'g-sweat.webp',
				'gallery'  => array( 'men.jpg' ),
				'colors'   => array( 'charcoal', 'black' ),
				'material' => 'wool',
				'fit'      => 'اسلیم',
				'brand'    => 'atelier',
				'desc'     => 'بافت مرینوس نرم با یقه گردن‌اسکی و بافت ریز؛ زیر پالتو بی‌حجم و تنها هم شیک.',
				'tags'     => array( 'بافت', 'مرینوس', 'گردن‌اسکی' ),
			),
			array(
				'name'     => 'پلیور شنی',
				'slug'     => 'sand-wool-sweater',
				'cat'      => 'men-knit',
				'price'    => 3950000,
				'image'    => 'outfit-2.jpg',
				'gallery'  => array( 'g-sweat.webp' ),
				'colors'   => array( 'sand', 'cream' ),
				'material' => 'wool',
				'fit'      => 'راحت',
				'brand'    => 'studio',
				'desc'     => 'پلیور پشمی با بافت درشت و یقه گرد؛ رنگ شنی خنثی که با هر شلواری هم‌نشین می‌شود.',
				'tags'     => array( 'پلیور', 'پشم', 'شنی' ),
			),
			array(
				'name'     => 'بافت یقه‌گرد کرم',
				'slug'     => 'cream-crewneck',
				'cat'      => 'men-knit',
				'price'    => 3750000,
				'image'    => 'g-sweat.webp',
				'gallery'  => array( 'outfit-2.jpg' ),
				'colors'   => array( 'cream', 'sand' ),
				'material' => 'cashmere',
				'fit'      => 'رگولار',
				'brand'    => 'atelier',
				'desc'     => 'بافت کشمیر با سطح صاف و درز راجلان؛ سبک‌تر از پشم، گرم‌تر از پنبه.',
				'tags'     => array( 'بافت', 'کشمیر', 'کرم' ),
			),
			array(
				'name'     => 'جلیقه بافت مردانه',
				'slug'     => 'men-knit-vest',
				'cat'      => 'men-knit',
				'price'    => 2950000,
				'image'    => 'g-sweat.webp',
				'gallery'  => array( 'outfit-2.jpg' ),
				'colors'   => array( 'charcoal', 'olive' ),
				'material' => 'knit',
				'fit'      => 'رگولار',
				'brand'    => 'essentials',
				'desc'     => 'جلیقه بافت با یقه هفت و حلقه‌های ریز؛ لایه‌ی میانی برای روزهای بین دو فصل.',
				'tags'     => array( 'جلیقه', 'بافت', 'لایه‌ای' ),
			),
			array(
				'name'     => 'پالتو پشمی مردانه',
				'slug'     => 'wool-overcoat-men',
				'cat'      => 'men-coat',
				'price'    => 14800000,
				'image'    => 'men.jpg',
				'gallery'  => array( 'g-coat.webp' ),
				'colors'   => array( 'charcoal', 'navy' ),
				'material' => 'wool',
				'fit'      => 'رگولار',
				'brand'    => 'atelier',
				'desc'     => 'پالتوی پشمی با یقه انگلیسی و آستر ساتن؛ برش صاف و خط شانه‌ی تمیز برای شهر.',
				'tags'     => array( 'پالتو', 'پشم', 'زمستانی' ),
				'featured' => true,
			),
			array(
				'name'     => 'بارانی مینیمال',
				'slug'     => 'minimal-trench',
				'cat'      => 'men-coat',
				'price'    => 9600000,
				'sale'     => 7680000,
				'image'    => 'g-coat.webp',
				'gallery'  => array( 'men.jpg' ),
				'colors'   => array( 'black', 'olive' ),
				'material' => 'cotton',
				'fit'      => 'راحت',
				'brand'    => 'moon',
				'desc'     => 'بارانی بدون جزئیات اضافه، با دکمه‌های پنهان و کمربند پارچه‌ای؛ ضدآب سبک.',
				'tags'     => array( 'بارانی', 'مینیمال', 'پاییزه' ),
			),
			array(
				'name'     => 'کاپشن شاکت جین',
				'slug'     => 'denim-shacket',
				'cat'      => 'men-coat',
				'price'    => 4950000,
				'image'    => 'outfit-5.jpg',
				'gallery'  => array( 'g-jacket.webp' ),
				'colors'   => array( 'sand', 'blue' ),
				'material' => 'denim',
				'fit'      => 'راحت',
				'brand'    => 'studio',
				'desc'     => 'شاکت جین با دکمه‌های فلزی مات و جیب‌های بزرگ؛ جای کاپشن سبک را می‌گیرد.',
				'tags'     => array( 'کاپشن', 'جین', 'شاکت' ),
			),
			array(
				'name'     => 'پالتو تک‌دکمه پشمی',
				'slug'     => 'single-button-coat',
				'cat'      => 'men-coat',
				'price'    => 11800000,
				'image'    => 'men.jpg',
				'gallery'  => array( 'g-coat.webp' ),
				'colors'   => array( 'charcoal', 'camel' ),
				'material' => 'wool',
				'fit'      => 'رگولار',
				'brand'    => 'atelier',
				'desc'     => 'پالتوی تک‌دکمه با یقه‌ی بسته و جیب‌های فیله؛ کمترین جزئیات، بیشترین تأثیر.',
				'tags'     => array( 'پالتو', 'رسمی', 'پشم' ),
			),
			array(
				'name'     => 'کت تک‌خوردگی رسمی',
				'slug'     => 'single-breasted-blazer',
				'cat'      => 'men-suit',
				'price'    => 12400000,
				'image'    => 'men.jpg',
				'gallery'  => array( 'g-shirt.webp' ),
				'colors'   => array( 'charcoal', 'navy' ),
				'material' => 'wool',
				'fit'      => 'اسلیم',
				'brand'    => 'atelier',
				'desc'     => 'کت تک‌خوردگی با ساختار سبک و دو چاک پشت؛ برای مراسم و جلسه‌های مهم.',
				'tags'     => array( 'کت', 'رسمی', 'مجلس' ),
			),
			array(
				'name'     => 'ست کت و شلوار اسلیم',
				'slug'     => 'slim-suit-set',
				'cat'      => 'men-suit',
				'price'    => 18900000,
				'image'    => 'men.jpg',
				'gallery'  => array( 'g-shirt.webp', 'outfit-3.jpg' ),
				'colors'   => array( 'navy', 'black' ),
				'material' => 'wool',
				'fit'      => 'اسلیم',
				'brand'    => 'atelier',
				'desc'     => 'ست دو تکه با پارچه‌ی پشمی سبک و آستر خنک؛ هماهنگی کامل کت و شلوار از یک جنس.',
				'tags'     => array( 'ست', 'کت و شلوار', 'رسمی' ),
			),
			array(
				'name'     => 'شلوار جین تیره اسلیم',
				'slug'     => 'dark-slim-jeans',
				'cat'      => 'men-jeans',
				'price'    => 3550000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'outfit-5.jpg' ),
				'colors'   => array( 'black', 'navy' ),
				'material' => 'denim',
				'fit'      => 'اسلیم',
				'brand'    => 'moon',
				'desc'     => 'جین تیره با رنگ‌ریزی کنترل‌شده و دوخت زنجیره‌ای؛ گزینه‌ی امن شب‌های شهری.',
				'tags'     => array( 'جین', 'تیره', 'اسلیم' ),
			),

			// ---------------------------------------------------------- زنانه
			array(
				'name'     => 'مانتو بلند لونا',
				'slug'     => 'luna-long-manto',
				'cat'      => 'women-manto',
				'price'    => 8900000,
				'image'    => 'hero.jpg',
				'gallery'  => array( 'g-coat.webp' ),
				'colors'   => array( 'black', 'camel' ),
				'material' => 'wool',
				'fit'      => 'راحت',
				'brand'    => 'moon',
				'desc'     => 'مانتوی بلند با یقه‌ی ایستاده و برش A؛ پارچه‌ی پشمی سبک که فرم را نگه می‌دارد.',
				'tags'     => array( 'مانتو', 'بلند', 'پشم' ),
				'featured' => true,
			),
			array(
				'name'     => 'مانتو کتان روزمره',
				'slug'     => 'everyday-linen-manto',
				'cat'      => 'women-manto',
				'price'    => 5400000,
				'sale'     => 4320000,
				'image'    => 'women.jpg',
				'gallery'  => array( 'g-coat.webp' ),
				'colors'   => array( 'cream', 'sand' ),
				'material' => 'linen',
				'fit'      => 'راحت',
				'brand'    => 'essentials',
				'desc'     => 'مانتوی کتان با آستر نخی و جیب‌های کناری؛ خنک برای روزهای گرم و ساده برای هر روز.',
				'tags'     => array( 'مانتو', 'کتان', 'تابستانی' ),
			),
			array(
				'name'     => 'مانتو کوتاه کتان',
				'slug'     => 'short-linen-manto',
				'cat'      => 'women-manto',
				'price'    => 4950000,
				'sale'     => 3960000,
				'image'    => 'women.jpg',
				'gallery'  => array( 'g-coat.webp' ),
				'colors'   => array( 'sand', 'cream' ),
				'material' => 'linen',
				'fit'      => 'رگولار',
				'brand'    => 'studio',
				'desc'     => 'مانتوی کوتاه با برش مستقیم و آستین سه‌ربع؛ لایه‌ی سبک روی شلوار یا دامن.',
				'tags'     => array( 'مانتو', 'کوتاه', 'کتان' ),
			),
			array(
				'name'     => 'شومیز ابریشم کرم',
				'slug'     => 'cream-silk-blouse',
				'cat'      => 'women-blouse',
				'price'    => 6800000,
				'image'    => 'women.jpg',
				'gallery'  => array( 'g-shirt.webp' ),
				'colors'   => array( 'cream', 'snow' ),
				'material' => 'silk',
				'fit'      => 'راحت',
				'brand'    => 'atelier',
				'desc'     => 'شومیز ابریشم با یقه‌ی باز و دکمه‌های مرواریدی؛ درخشش ملایم و افتادگی روان.',
				'tags'     => array( 'شومیز', 'ابریشم', 'کرم' ),
				'featured' => true,
			),
			array(
				'name'     => 'شومیز ساتن مشکی',
				'slug'     => 'black-satin-blouse',
				'cat'      => 'women-blouse',
				'price'    => 5900000,
				'image'    => 'hero.jpg',
				'gallery'  => array( 'g-shirt.webp' ),
				'colors'   => array( 'black', 'wine' ),
				'material' => 'satin',
				'fit'      => 'راحت',
				'brand'    => 'atelier',
				'desc'     => 'شومیز ساتن با برش آزاد و آستین بلند؛ برای شب، با شلوار دم‌پا یا دامن ساده.',
				'tags'     => array( 'شومیز', 'ساتن', 'مجلسی' ),
			),
			array(
				'name'     => 'شومیز پاپلین سفید',
				'slug'     => 'white-poplin-blouse',
				'cat'      => 'women-blouse',
				'price'    => 4650000,
				'image'    => 'outfit-3.jpg',
				'gallery'  => array( 'g-shirt.webp' ),
				'colors'   => array( 'snow', 'blue' ),
				'material' => 'cotton',
				'fit'      => 'رگولار',
				'brand'    => 'moon',
				'desc'     => 'پاپلین پنبه‌ای با یقه‌ی کلاسیک و مچ دکمه‌دار؛ سفیدِ همیشگی که هر استایلی را نجات می‌دهد.',
				'tags'     => array( 'شومیز', 'سفید', 'پنبه' ),
			),
			array(
				'name'     => 'تاپ بافت ریب',
				'slug'     => 'rib-knit-top',
				'cat'      => 'women-top',
				'price'    => 1950000,
				'image'    => 'g-sweat.webp',
				'gallery'  => array( 'outfit-2.jpg' ),
				'colors'   => array( 'charcoal', 'cream' ),
				'material' => 'knit',
				'fit'      => 'اسلیم',
				'brand'    => 'essentials',
				'desc'     => 'تاپ ریب با آستین بلند و یقه‌ی گرد؛ کشسانی بالا و بدون شفافیت.',
				'tags'     => array( 'تاپ', 'بافت', 'اسلیم' ),
			),
			array(
				'name'     => 'تاپ ساتن مجلسی',
				'slug'     => 'satin-evening-top',
				'cat'      => 'women-top',
				'price'    => 2450000,
				'sale'     => 1960000,
				'image'    => 'hero.jpg',
				'gallery'  => array( 'g-shirt.webp' ),
				'colors'   => array( 'black', 'wine' ),
				'material' => 'satin',
				'fit'      => 'رگولار',
				'brand'    => 'studio',
				'desc'     => 'تاپ ساتن با بند نازک و لبه‌ی اریب؛ زیر کت یا تنها، برای مهمانی‌های کوچک.',
				'tags'     => array( 'تاپ', 'ساتن', 'مجلسی' ),
			),
			array(
				'name'     => 'شلوار کتان زنانه',
				'slug'     => 'women-linen-trousers',
				'cat'      => 'women-trousers',
				'price'    => 3250000,
				'image'    => 'women.jpg',
				'gallery'  => array( 'outfit-3.jpg' ),
				'colors'   => array( 'cream', 'sand' ),
				'material' => 'linen',
				'fit'      => 'دم‌پا',
				'brand'    => 'moon',
				'desc'     => 'شلوار کتان با کمر بلند و پیلی مرکزی؛ پا را بلند نشان می‌دهد و خنک می‌ماند.',
				'tags'     => array( 'شلوار', 'کتان', 'دم‌پا' ),
			),
			array(
				'name'     => 'شلوار پارچه‌ای دم‌پا',
				'slug'     => 'wide-leg-trousers',
				'cat'      => 'women-trousers',
				'price'    => 3850000,
				'image'    => 'outfit-3.jpg',
				'gallery'  => array( 'women.jpg' ),
				'colors'   => array( 'black', 'charcoal' ),
				'material' => 'viscose',
				'fit'      => 'دم‌پا',
				'brand'    => 'atelier',
				'desc'     => 'شلوار ویسکوز با افتادگی نرم و کمر کشی پنهان؛ حرکت آزاد و ظاهر رسمی.',
				'tags'     => array( 'شلوار', 'دم‌پا', 'رسمی' ),
			),
			array(
				'name'     => 'جین مام‌استایل',
				'slug'     => 'mom-fit-jeans',
				'cat'      => 'women-jeans',
				'price'    => 3450000,
				'image'    => 'outfit-5.jpg',
				'gallery'  => array( 'outfit-1.jpg' ),
				'colors'   => array( 'blue', 'black' ),
				'material' => 'denim',
				'fit'      => 'مام‌استایل',
				'brand'    => 'essentials',
				'desc'     => 'جین مام‌فیت با کمر بلند و پاچه‌ی باریک؛ تعادل راحتی و فرم دهه‌ی نود.',
				'tags'     => array( 'جین', 'مام‌استایل', 'روزمره' ),
			),
			array(
				'name'     => 'جین بوت‌کات مشکی',
				'slug'     => 'bootcut-black-jeans',
				'cat'      => 'women-jeans',
				'price'    => 3650000,
				'sale'     => 2920000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'outfit-5.jpg' ),
				'colors'   => array( 'black', 'navy' ),
				'material' => 'denim',
				'fit'      => 'بوت‌کات',
				'brand'    => 'moon',
				'desc'     => 'بوت‌کات با بازشدگی ملایم پاچه؛ با بوت و کفش پاشنه‌دار بهترین فرم را می‌دهد.',
				'tags'     => array( 'جین', 'بوت‌کات', 'مشکی' ),
			),
			array(
				'name'     => 'شلوار جین‌نما کشی',
				'slug'     => 'stretch-jeggings',
				'cat'      => 'women-trousers',
				'price'    => 2850000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'outfit-5.jpg' ),
				'colors'   => array( 'black', 'navy' ),
				'material' => 'denim',
				'fit'      => 'اسلیم',
				'brand'    => 'essentials',
				'desc'     => 'جین‌نمای کشی با کمر فنری و سطح مات؛ راحتی لگینگ با ظاهر جین.',
				'tags'     => array( 'شلوار', 'کشی', 'راحتی' ),
			),
			array(
				'name'     => 'هودی زنانه ابر',
				'slug'     => 'women-cloud-hoodie',
				'cat'      => 'women-hoodie',
				'price'    => 3350000,
				'image'    => 'outfit-2.jpg',
				'gallery'  => array( 'g-hoodie.webp' ),
				'colors'   => array( 'sand', 'snow' ),
				'material' => 'cotton',
				'fit'      => 'راحت',
				'brand'    => 'moon',
				'desc'     => 'هودی نرم با کلاه دو لایه و جیب کانگورویی؛ برش کمی کوتاه‌تر برای فرم زنانه.',
				'tags'     => array( 'هودی', 'زنانه', 'پنبه' ),
			),
			array(
				'name'     => 'هودی کراپ بافت',
				'slug'     => 'cropped-knit-hoodie',
				'cat'      => 'women-hoodie',
				'price'    => 3150000,
				'image'    => 'g-hoodie.webp',
				'gallery'  => array( 'outfit-2.jpg' ),
				'colors'   => array( 'sand', 'grey' ),
				'material' => 'knit',
				'fit'      => 'کراپ',
				'brand'    => 'studio',
				'desc'     => 'هودی کراپ بافت با کشسانی نرم و لبه‌ی جمع؛ روی شلوار کمر بلند.',
				'tags'     => array( 'هودی', 'کراپ', 'بافت' ),
			),
			array(
				'name'     => 'بافت یقه‌اسکی',
				'slug'     => 'turtleneck-knit',
				'cat'      => 'women-knit',
				'price'    => 4250000,
				'image'    => 'g-sweat.webp',
				'gallery'  => array( 'hero.jpg' ),
				'colors'   => array( 'charcoal', 'cream' ),
				'material' => 'wool',
				'fit'      => 'اسلیم',
				'brand'    => 'atelier',
				'desc'     => 'بافت یقه‌اسکی با پشم نرم و بافت ریز؛ گرمای بی‌حجم زیر مانتو و کت.',
				'tags'     => array( 'بافت', 'یقه‌اسکی', 'پشم' ),
			),
			array(
				'name'     => 'پلیور پشمی نرم',
				'slug'     => 'soft-wool-sweater',
				'cat'      => 'women-knit',
				'price'    => 3950000,
				'image'    => 'outfit-2.jpg',
				'gallery'  => array( 'g-sweat.webp' ),
				'colors'   => array( 'cream', 'camel' ),
				'material' => 'wool',
				'fit'      => 'راحت',
				'brand'    => 'studio',
				'desc'     => 'پلیور پشمی با آستین رگلان و بافت درشت؛ سبک، گرم و بی‌دردسر.',
				'tags'     => array( 'پلیور', 'پشم', 'نرم' ),
			),
			array(
				'name'     => 'بافت کراپ کرم',
				'slug'     => 'cropped-cream-knit',
				'cat'      => 'women-knit',
				'price'    => 3250000,
				'image'    => 'outfit-2.jpg',
				'gallery'  => array( 'g-sweat.webp' ),
				'colors'   => array( 'cream', 'camel' ),
				'material' => 'knit',
				'fit'      => 'کراپ',
				'brand'    => 'essentials',
				'desc'     => 'بافت کراپ با یقه‌ی گرد و بافت ریب؛ با شلوار کمر بلند یا دامن بلند.',
				'tags'     => array( 'بافت', 'کراپ', 'کرم' ),
			),
			array(
				'name'     => 'پالتو چرم زنانه',
				'slug'     => 'women-leather-coat',
				'cat'      => 'women-coat',
				'price'    => 16500000,
				'image'    => 'outfit-4.jpg',
				'gallery'  => array( 'g-jacket.webp' ),
				'colors'   => array( 'black', 'brown' ),
				'material' => 'leather',
				'fit'      => 'رگولار',
				'brand'    => 'atelier',
				'desc'     => 'پالتوی چرم طبیعی با آستر گرم و زیپ مات؛ سنگینی دلپذیر و فرم ماندگار.',
				'tags'     => array( 'پالتو', 'چرم', 'زمستانی' ),
				'featured' => true,
			),
			array(
				'name'     => 'پالتو بلند شب',
				'slug'     => 'midnight-long-coat',
				'cat'      => 'women-coat',
				'price'    => 12900000,
				'image'    => 'hero.jpg',
				'gallery'  => array( 'g-coat.webp' ),
				'colors'   => array( 'black', 'charcoal' ),
				'material' => 'wool',
				'fit'      => 'بلند',
				'brand'    => 'moon',
				'desc'     => 'پالتوی بلند با یقه‌ی شال و جیب‌های پنهان؛ برای شب‌های سرد شهری.',
				'tags'     => array( 'پالتو', 'بلند', 'شب' ),
			),
			array(
				'name'     => 'کاپشن چرم کوتاه',
				'slug'     => 'cropped-leather-jacket',
				'cat'      => 'women-coat',
				'price'    => 12500000,
				'image'    => 'outfit-4.jpg',
				'gallery'  => array( 'g-jacket.webp' ),
				'colors'   => array( 'black', 'brown' ),
				'material' => 'leather',
				'fit'      => 'کراپ',
				'brand'    => 'studio',
				'desc'     => 'کاپشن چرم کوتاه با یقه‌ی ایستاده و زیپ مورب؛ روی پیراهن یا لباس بلند.',
				'tags'     => array( 'کاپشن', 'چرم', 'کوتاه' ),
			),
			array(
				'name'     => 'هودی پنبه‌ای مشکی',
				'slug'     => 'black-cotton-hoodie',
				'cat'      => 'men-hoodie',
				'price'    => 3250000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'g-hoodie.webp' ),
				'colors'   => array( 'black', 'charcoal' ),
				'material' => 'cotton',
				'fit'      => 'رگولار',
				'brand'    => 'moon',
				'desc'     => 'هودی پنبه‌ای با بند پنبه‌ای و کش مچ؛ مشکیِ مات که رنگ نمی‌دهد.',
				'tags'     => array( 'هودی', 'مشکی', 'پنبه' ),
			),
			array(
				'name'     => 'بافت یقه‌هفت',
				'slug'     => 'v-neck-knit',
				'cat'      => 'men-knit',
				'price'    => 3450000,
				'image'    => 'g-sweat.webp',
				'gallery'  => array( 'outfit-2.jpg' ),
				'colors'   => array( 'charcoal', 'navy' ),
				'material' => 'wool',
				'fit'      => 'رگولار',
				'brand'    => 'essentials',
				'desc'     => 'بافت یقه‌هفت با پشم نرم و لبه‌ی ریب؛ زیر پیراهن رسمی یا روی تیشرت.',
				'tags'     => array( 'بافت', 'یقه‌هفت', 'پشم' ),
			),
			array(
				'name'     => 'شلوار کتان راست',
				'slug'     => 'straight-cotton-trousers',
				'cat'      => 'men-trousers',
				'price'    => 2850000,
				'image'    => 'outfit-5.jpg',
				'gallery'  => array( 'outfit-3.jpg' ),
				'colors'   => array( 'sand', 'charcoal' ),
				'material' => 'cotton',
				'fit'      => 'راست',
				'brand'    => 'moon',
				'desc'     => 'شلوار کتان راست‌برش با کمر دکمه‌دار و جیب‌های عمیق؛ ساده و مقاوم.',
				'tags'     => array( 'شلوار', 'کتان', 'راست' ),
			),
			array(
				'name'     => 'شومیز آستین‌پفی',
				'slug'     => 'puff-sleeve-blouse',
				'cat'      => 'women-blouse',
				'price'    => 4850000,
				'image'    => 'g-shirt.webp',
				'gallery'  => array( 'women.jpg' ),
				'colors'   => array( 'snow', 'cream' ),
				'material' => 'cotton',
				'fit'      => 'راحت',
				'brand'    => 'studio',
				'desc'     => 'شومیز با آستین پفی و مچ کشی؛ حجم ملایم روی شانه و برش ساده‌ی بدنه.',
				'tags'     => array( 'شومیز', 'آستین‌پفی', 'پنبه' ),
			),
			array(
				'name'     => 'بافت بلند پشمی',
				'slug'     => 'long-wool-knit',
				'cat'      => 'women-knit',
				'price'    => 4650000,
				'image'    => 'g-sweat.webp',
				'gallery'  => array( 'hero.jpg' ),
				'colors'   => array( 'charcoal', 'camel' ),
				'material' => 'wool',
				'fit'      => 'بلند',
				'brand'    => 'moon',
				'desc'     => 'بافت بلند با دو چاک کناری و یقه‌ی گرد؛ مثل یک لایه‌ی گرم روی شلوار باریک.',
				'tags'     => array( 'بافت', 'بلند', 'پشم' ),
			),
			array(
				'name'     => 'مانتو پشمی کوتاه',
				'slug'     => 'short-wool-manto',
				'cat'      => 'women-coat',
				'price'    => 7900000,
				'image'    => 'g-coat.webp',
				'gallery'  => array( 'women.jpg' ),
				'colors'   => array( 'charcoal', 'camel' ),
				'material' => 'wool',
				'fit'      => 'رگولار',
				'brand'    => 'atelier',
				'desc'     => 'مانتوی پشمی کوتاه با یقه‌ی برگردان و دکمه‌های چوبی؛ گرمای مرتب روزهای سرد.',
				'tags'     => array( 'مانتو', 'پشم', 'کوتاه' ),
			),
			array(
				'name'     => 'جین اسلیم روشن',
				'slug'     => 'light-slim-jeans',
				'cat'      => 'women-jeans',
				'price'    => 3350000,
				'image'    => 'outfit-1.jpg',
				'gallery'  => array( 'outfit-5.jpg' ),
				'colors'   => array( 'blue', 'snow' ),
				'material' => 'denim',
				'fit'      => 'اسلیم',
				'brand'    => 'studio',
				'desc'     => 'جین اسلیم با شست‌وشوی روشن و کشسانی ملایم؛ برای روزهای آفتابی و استایل کوتاه.',
				'tags'     => array( 'جین', 'روشن', 'اسلیم' ),
			),
		);
	}

	/* ---------------------------------------------------------------------
	 * Runner
	 * ------------------------------------------------------------------- */

	/**
	 * Build everything.
	 *
	 * @return array Report with messages and errors.
	 */
	public static function run() {
		$report = array(
			'messages' => array(),
			'errors'   => array(),
		);

		if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_cat' ) ) {
			$report['errors'][] = __( 'ووکامرس فعال نیست؛ فروشگاه ساخته نشد.', 'moon-theme' );

			return $report;
		}

		self::ensure_terms();
		$colors    = self::ensure_colors();
		$brands    = self::ensure_attribute( 'brand', 'برند', 'select', self::brands() );
		$materials = self::ensure_attribute( 'material', 'جنس', 'select', self::materials() );
		$sizes     = self::ensure_sizes();

		$made = self::create_products( $colors, $brands, $materials, $sizes );
		$report['messages'][] = sprintf(
			/* translators: %s: number of products. */
			__( '%s محصول در کاتالوگ فروشگاه آماده شد.', 'moon-theme' ),
			number_format_i18n( $made )
		);

		self::assign_existing();
		self::category_images();
		self::build_mega();
		self::build_menu();
		self::store_settings();

		$report['messages'][] = __( 'دسته‌بندی‌ها، مگامنو و منوی اصلی به‌روزرسانی شد.', 'moon-theme' );

		update_option( 'moon_store_catalog_version', self::VERSION, false );

		return $report;
	}

	/* ---------------------------------------------------------------------
	 * Terms and attributes
	 * ------------------------------------------------------------------- */

	/**
	 * Departments and subcategories.
	 */
	private static function ensure_terms() {
		$tree = self::tree();

		foreach ( $tree as $slug => $dept ) {
			$parent = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $parent || is_wp_error( $parent ) ) {
				$created = wp_insert_term( $dept['name'], 'product_cat', array( 'slug' => $slug ) );

				if ( is_wp_error( $created ) ) {
					continue;
				}

				$parent_id = (int) $created['term_id'];
			} else {
				$parent_id = (int) $parent->term_id;

				if ( $parent->name !== $dept['name'] ) {
					wp_update_term( $parent_id, 'product_cat', array( 'name' => $dept['name'] ) );
				}
			}

			foreach ( $dept['children'] as $child_slug => $child_name ) {
				$child = get_term_by( 'slug', $child_slug, 'product_cat' );

				if ( $child && ! is_wp_error( $child ) ) {
					if ( (int) $child->parent !== $parent_id || $child->name !== $child_name ) {
						wp_update_term( (int) $child->term_id, 'product_cat', array(
							'name'   => $child_name,
							'parent' => $parent_id,
						) );
					}

					continue;
				}

				wp_insert_term( $child_name, 'product_cat', array(
					'slug'   => $child_slug,
					'parent' => $parent_id,
				) );
			}
		}

		// The old demo left «اکسسوری» and «کودکان» as separate top departments;
		// they now live inside the structure above.
		foreach ( array( 'uncategorized' ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( $term && ! is_wp_error( $term ) ) {
				wp_update_term( (int) $term->term_id, 'product_cat', array( 'name' => 'سایر' ) );
			}
		}

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients();
		}
	}

	/**
	 * Colour attribute + terms, repairing the mislabelled wine term.
	 *
	 * @return array slug => term id
	 */
	private static function ensure_colors() {
		self::ensure_attribute( 'color', 'رنگ', 'color', self::palette() );

		$ids = array();

		foreach ( self::palette() as $slug => $row ) {
			$term = get_term_by( 'slug', $slug, 'pa_color' );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			if ( $term->name !== $row[0] ) {
				wp_update_term( (int) $term->term_id, 'pa_color', array( 'name' => $row[0] ) );
			}

			update_term_meta( (int) $term->term_id, 'moon_hex', $row[1] );
			$ids[ $slug ] = (int) $term->term_id;
		}

		return $ids;
	}

	/**
	 * Create a global product attribute and its terms.
	 *
	 * @param string $slug     Attribute slug without the pa_ prefix.
	 * @param string $label    Admin label.
	 * @param string $type     Attribute type.
	 * @param array  $terms    slug => name.
	 * @return array slug => term id
	 */
	private static function ensure_attribute( $slug, $label, $type, $terms ) {
		$taxonomy = 'pa_' . $slug;

		if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
			wc_create_attribute( array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => $type,
				'order_by'     => 'menu_order',
				'has_archives' => false,
			) );

			delete_transient( 'wc_attribute_taxonomies' );
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false ) );
		}

		$ids = array();

		foreach ( $terms as $term_slug => $term_name ) {
			$term = get_term_by( 'slug', (string) $term_slug, $taxonomy );

			if ( $term && ! is_wp_error( $term ) ) {
				$ids[ $term_slug ] = (int) $term->term_id;

				continue;
			}

			$created = wp_insert_term( (string) $term_name, $taxonomy, array( 'slug' => (string) $term_slug ) );

			if ( ! is_wp_error( $created ) ) {
				$ids[ $term_slug ] = (int) $created['term_id'];
			}
		}

		return $ids;
	}

	/**
	 * Size terms, keyed by the label shown to customers.
	 *
	 * @return array name => [id, slug]
	 */
	private static function ensure_sizes() {
		self::ensure_attribute( 'size', 'سایز', 'select', array() );

		$map = array();

		foreach ( self::sizes() as $size ) {
			$term = get_term_by( 'name', $size, 'pa_size' );

			if ( ! $term || is_wp_error( $term ) ) {
				$created = wp_insert_term( $size, 'pa_size', array( 'slug' => strtolower( $size ) ) );

				if ( is_wp_error( $created ) ) {
					continue;
				}

				$map[ $size ] = array(
					'id'   => (int) $created['term_id'],
					'slug' => strtolower( $size ),
				);

				continue;
			}

			$map[ $size ] = array(
				'id'   => (int) $term->term_id,
				'slug' => $term->slug,
			);
		}

		return $map;
	}

	/* ---------------------------------------------------------------------
	 * Products
	 * ------------------------------------------------------------------- */

	/**
	 * Create the demo products.
	 *
	 * @param array $colors    Colour terms.
	 * @param array $brands    Brand terms.
	 * @param array $materials Material terms.
	 * @param array $sizes     Size terms.
	 * @return int Number of products present.
	 */
	private static function create_products( $colors, $brands, $materials, $sizes ) {
		$media = self::media_map();
		$count = 0;
		$index = 0;

		foreach ( self::products() as $item ) {
			$index++;
			$existing = get_page_by_path( $item['slug'], OBJECT, 'product' );
			$product  = $existing ? wc_get_product( $existing->ID ) : null;

			if ( ! $product ) {
				$product = new WC_Product_Variable();
				$product->set_name( $item['name'] );
				$product->set_slug( $item['slug'] );
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );
			}

			$product->set_description( $item['desc'] );
			$product->set_short_description( $item['desc'] );
			$product->set_regular_price( (string) $item['price'] );

			if ( ! empty( $item['sale'] ) ) {
				$product->set_sale_price( (string) $item['sale'] );
			}

			$product->set_featured( ! empty( $item['featured'] ) );
			$sku      = 'MOON-' . strtoupper( str_replace( '-', '', substr( $item['slug'], 0, 10 ) ) ) . '-' . sprintf( '%03d', $index );
			$sku_owner = (int) wc_get_product_id_by_sku( $sku );

			if ( ! $sku_owner || $sku_owner === (int) $product->get_id() ) {
				$product->set_sku( $sku );
			}

			$term = get_term_by( 'slug', $item['cat'], 'product_cat' );

			if ( $term && ! is_wp_error( $term ) ) {
				$product->set_category_ids( array( (int) $term->term_id ) );
			}

			$image_id = isset( $media[ $item['image'] ] ) ? (int) $media[ $item['image'] ] : 0;

			if ( $image_id ) {
				$product->set_image_id( $image_id );
			}

			$gallery = array();

			foreach ( (array) $item['gallery'] as $file ) {
				if ( isset( $media[ $file ] ) && (int) $media[ $file ] !== $image_id ) {
					$gallery[] = (int) $media[ $file ];
				}
			}

			if ( $gallery ) {
				$product->set_gallery_image_ids( array_values( array_unique( $gallery ) ) );
			}

			$product->set_attributes( self::attributes( $item, $colors, $brands, $materials, $sizes ) );
			$product->set_stock_status( 'instock' );
			$product->save();

			$product_id = $product->get_id();

			update_post_meta( $product_id, '_moon_demo', 1 );
			update_post_meta( $product_id, '_moon_fit', $item['fit'] );
			update_post_meta( $product_id, '_moon_material_note', self::materials()[ $item['material'] ] );

			wp_set_object_terms( $product_id, (array) $item['tags'], 'product_tag', false );

			// Build the colour × size grid once.
			if ( ! $existing ) {
				self::build_variations( $product_id, $item, $colors, $sizes );
			}

			$count++;
		}

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients();
		}

		return $count;
	}

	/**
	 * Attribute objects for one product.
	 *
	 * @param array $item      Product definition.
	 * @param array $colors    Colour terms.
	 * @param array $brands    Brand terms.
	 * @param array $materials Material terms.
	 * @param array $sizes     Size terms.
	 * @return array
	 */
	private static function attributes( $item, $colors, $brands, $materials, $sizes ) {
		$attributes = array();

		$color_ids = array();

		foreach ( $item['colors'] as $color_slug ) {
			if ( ! empty( $colors[ $color_slug ] ) ) {
				$color_ids[] = (int) $colors[ $color_slug ];
			}
		}

		$size_ids = array();

		foreach ( self::sizes() as $size_name ) {
			if ( ! empty( $sizes[ $size_name ]['id'] ) ) {
				$size_ids[] = (int) $sizes[ $size_name ]['id'];
			}
		}

		$map = array(
			'pa_color'    => array( $color_ids, 0, true ),
			'pa_size'     => array( $size_ids, 1, true ),
			'pa_brand'    => array( array( (int) ( $brands[ $item['brand'] ] ?? 0 ) ), 2, false ),
			'pa_material' => array( array( (int) ( $materials[ $item['material'] ] ?? 0 ) ), 3, false ),
		);

		foreach ( $map as $name => $config ) {
			$terms = array_values( array_filter( array_map( 'intval', (array) $config[0] ) ) );

			if ( ! $terms ) {
				continue;
			}

			$attribute = new WC_Product_Attribute();
			$attribute->set_id( (int) wc_attribute_taxonomy_id_by_name( substr( $name, 3 ) ) );
			$attribute->set_name( $name );
			$attribute->set_options( $terms );
			$attribute->set_position( (int) $config[1] );
			$attribute->set_visible( true );
			$attribute->set_variation( (bool) $config[2] );

			$attributes[] = $attribute;
		}

		return $attributes;
	}

	/**
	 * Colour × size variations for a product.
	 *
	 * @param int   $product_id Product id.
	 * @param array $item       Product definition.
	 * @param array $colors     Colour terms.
	 * @param array $sizes      Size terms.
	 */
	private static function build_variations( $product_id, $item, $colors, $sizes ) {
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return;
		}

		$regular = (string) $item['price'];
		$sale    = ! empty( $item['sale'] ) ? (string) $item['sale'] : '';
		$media   = self::media_map();
		$count   = 0;

		foreach ( $item['colors'] as $color_slug ) {
			if ( empty( $colors[ $color_slug ] ) ) {
				continue;
			}

			$image_id = isset( $media[ $item['image'] ] ) ? (int) $media[ $item['image'] ] : 0;

			foreach ( self::sizes() as $size ) {
				if ( empty( $sizes[ $size ]['slug'] ) ) {
					continue;
				}

				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $product_id );
				$variation->set_attributes( array(
					'pa_color' => $color_slug,
					'pa_size'  => $sizes[ $size ]['slug'],
				) );
				$variation->set_regular_price( $regular );

				if ( $sale ) {
					$variation->set_sale_price( $sale );
				}

				$variation->set_manage_stock( true );
				$variation->set_stock_quantity( 8 );
				$variation->set_stock_status( 'instock' );

				if ( $image_id ) {
					$variation->set_image_id( $image_id );
				}

				$variation->save();
				$count++;
			}
		}

		if ( $count ) {
			WC_Product_Variable::sync( $product_id );
			wc_delete_product_transients( $product_id );
		}
	}

	/**
	 * Put the original demo products into the new subcategories.
	 */
	private static function assign_existing() {
		$map = array(
			'luna-wool-coat'        => 'women-coat',
			'dune-hoodie'           => 'women-hoodie',
			'eclipse-leather'       => 'men-coat',
			'nova-poplin-shirt'     => 'men-shirt',
			'carbon-crewneck'       => 'men-knit',
			'ivory-silk-set'        => 'women-blouse',
			'charcoal-overcoat'     => 'men-coat',
			'slate-cotton-trousers' => 'men-trousers',
			'moon-wool-scarf'       => 'scarf-winter',
			'ava-silk-scarf'        => 'hijab-silk',
			'nova-kids-coat'        => 'kids-outerwear',
			'abr-kids-hoodie'       => 'kids-hoodie',
			'linen-shirt-atlas'     => 'men-shirt',
			'dune-knit-vest'        => 'women-knit',
		);

		foreach ( $map as $slug => $cat ) {
			$post = get_page_by_path( $slug, OBJECT, 'product' );

			if ( ! $post ) {
				continue;
			}

			$term = get_term_by( 'slug', $cat, 'product_cat' );

			if ( $term && ! is_wp_error( $term ) ) {
				wp_set_object_terms( $post->ID, array( (int) $term->term_id ), 'product_cat', false );
			}
		}
	}

	/**
	 * Media library map: file name => attachment id.
	 *
	 * @return array
	 */
	private static function media_map() {
		$media = get_option( 'moon_core_media', array() );

		if ( is_array( $media ) && $media ) {
			return $media;
		}

		$media       = array();
		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 200,
			'fields'         => 'ids',
		) );

		foreach ( $attachments as $attachment_id ) {
			$file = get_attached_file( (int) $attachment_id );

			if ( $file ) {
				$media[ basename( $file ) ] = (int) $attachment_id;
			}
		}

		if ( $media ) {
			update_option( 'moon_core_media', $media, false );
		}

		return $media;
	}

	/* ---------------------------------------------------------------------
	 * Navigation
	 * ------------------------------------------------------------------- */

	/**
	 * Category thumbnails used by the mega menu and the category tiles.
	 */
	private static function category_images() {
		$media = self::media_map();
		$map   = array(
			'men'   => 'men.jpg',
			'women' => 'women.jpg',
		);

		foreach ( $map as $slug => $file ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $term || is_wp_error( $term ) || empty( $media[ $file ] ) ) {
				continue;
			}

			update_term_meta( (int) $term->term_id, 'thumbnail_id', (int) $media[ $file ] );
		}
	}

	/**
	 * A store that can actually take an order: cash on delivery plus an Iran
	 * shipping zone whose free-shipping threshold matches the theme's progress
	 * bar. Card payments still come from the Zibal gateway (needs a merchant
	 * code), so nothing here pretends an online payment succeeded.
	 */
	private static function store_settings() {
		$cod = get_option( 'woocommerce_cod_settings', array() );
		$cod = is_array( $cod ) ? $cod : array();

		$cod['enabled']     = 'yes';
		$cod['title']       = 'پرداخت در محل';
		$cod['description'] = 'مبلغ سفارش را هنگام تحویل، به مأمور ارسال پرداخت کنید.';

		update_option( 'woocommerce_cod_settings', $cod );

		if ( ! class_exists( 'WC_Shipping_Zone' ) ) {
			return;
		}

		$threshold = 2000000;

		if ( class_exists( 'Moon_Settings' ) ) {
			$threshold = (int) Moon_Settings::get( 'free_ship_threshold', '2000000' );
		}

		foreach ( WC_Shipping_Zones::get_zones() as $zone_data ) {
			foreach ( (array) $zone_data['zone_locations'] as $location ) {
				if ( 'IR' === $location->code ) {
					return;
				}
			}
		}

		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'ایران' );
		$zone->add_location( 'IR', 'country' );
		$zone->save();

		$flat_id = $zone->add_shipping_method( 'flat_rate' );
		update_option( 'woocommerce_flat_rate_' . $flat_id . '_settings', array(
			'title'      => 'ارسال استاندارد',
			'cost'       => '65000',
			'tax_status' => 'none',
		) );

		$free_id = $zone->add_shipping_method( 'free_shipping' );
		update_option( 'woocommerce_free_shipping_' . $free_id . '_settings', array(
			'title'      => 'ارسال رایگان',
			'requires'   => 'min_amount',
			'min_amount' => (string) max( 0, $threshold ),
		) );

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients();
		}
	}

	/**
	 * Mega menu: one rail item per department with named columns.
	 */
	private static function build_mega() {
		if ( ! function_exists( 'moon_mega_sanitize' ) ) {
			return;
		}

		$media = self::media_map();
		$items = array();

		foreach ( self::tree() as $slug => $dept ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$image = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			$cols  = array();

			foreach ( $dept['groups'] as $group_title => $child_slugs ) {
				$links = array();

				foreach ( $child_slugs as $child_slug ) {
					$child = get_term_by( 'slug', $child_slug, 'product_cat' );

					if ( ! $child || is_wp_error( $child ) ) {
						continue;
					}

					$links[] = array(
						'title' => $child->name,
						'url'   => get_term_link( $child ),
						'image' => 0,
					);
				}

				if ( $links ) {
					$cols[] = array(
						'title' => $group_title,
						'url'   => '',
						'links' => $links,
					);
				}
			}

			$items[] = array(
				'title'  => $dept['name'],
				'url'    => get_term_link( $term ),
				'image'  => $image,
				'cols'   => $cols,
				'banner' => array( 'image' => 0, 'url' => '', 'title' => '' ),
			);
		}

		// Seventh rail item: the curated shortcuts.
		$shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		$items[] = array(
			'title'  => 'دسته‌بندی‌های ویژه',
			'url'    => $shop,
			'image'  => 0,
			'cols'   => array(
				array(
					'title' => 'منتخب MOON',
					'url'   => '',
					'links' => array(
						array( 'title' => 'جدیدترین محصولات', 'url' => add_query_arg( 'orderby', 'date', $shop ), 'image' => 0 ),
						array( 'title' => 'پرفروش‌ترین‌ها', 'url' => add_query_arg( 'orderby', 'popularity', $shop ), 'image' => 0 ),
						array( 'title' => 'تخفیف‌ها', 'url' => add_query_arg( 'sale', '1', $shop ), 'image' => 0 ),
						array( 'title' => 'پیشنهادهای ویژه', 'url' => add_query_arg( 'featured', '1', $shop ), 'image' => 0 ),
						array( 'title' => 'کالکشن‌های MOON', 'url' => get_post_type_archive_link( 'moon_outfit' ), 'image' => 0 ),
					),
				),
			),
			'banner' => array( 'image' => 0, 'url' => '', 'title' => '' ),
		);

		update_option( 'moon_megamenu', moon_mega_sanitize( array(
			'enabled' => 1,
			'label'   => 'دسته‌بندی کالاها',
			'items'   => $items,
		) ) );
	}

	/**
	 * Primary menu: the seven departments plus the brand pages.
	 */
	private static function build_menu() {
		$menu = wp_get_nav_menu_object( 'منوی اصلی' );

		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( 'منوی اصلی' );

			if ( is_wp_error( $menu_id ) ) {
				return;
			}
		} else {
			$menu_id = (int) $menu->term_id;

			foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
				wp_delete_post( (int) $item->ID, true );
			}
		}

		$position = 1;

		foreach ( self::tree() as $slug => $dept ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $dept['name'],
				'menu-item-object'    => 'product_cat',
				'menu-item-object-id' => (int) $term->term_id,
				'menu-item-type'      => 'taxonomy',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
		}

		$about = get_page_by_path( 'about' );

		if ( $about ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => 'داستان ما',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => (int) $about->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
		}

		$contact = get_page_by_path( 'contact' );

		if ( $contact ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => 'تماس با ما',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => (int) $contact->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
		}

		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
