<?php
/**
 * Persian fallbacks for WooCommerce front-end strings.
 *
 * Only used when the site language is Persian and the installed translation
 * pack has no entry for a string (e.g. before the language pack downloads).
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array English => Persian.
 */
function moon_woo_fa_strings() {
	return array(
		'Add to cart'                            => 'افزودن به سبد خرید',
		'Read more'                              => 'مشاهده',
		'Select options'                         => 'انتخاب گزینه‌ها',
		'View cart'                              => 'مشاهده سبد خرید',
		'Checkout'                               => 'تسویه حساب',
		'Proceed to checkout'                    => 'ادامه و تسویه حساب',
		'Cart'                                   => 'سبد خرید',
		'Cart totals'                            => 'جمع سبد خرید',
		'Your cart is currently empty.'          => 'سبد خرید شما خالی است.',
		'No products in the cart.'               => 'سبد خرید شما خالی است.',
		'Return to shop'                         => 'بازگشت به فروشگاه',
		'Product'                                => 'محصول',
		'Price'                                  => 'قیمت',
		'Quantity'                               => 'تعداد',
		'Subtotal'                               => 'جمع جزء',
		'Subtotal:'                              => 'جمع جزء:',
		'Total'                                  => 'مبلغ نهایی',
		'Shipping'                               => 'ارسال',
		'Shipment'                               => 'ارسال',
		'Coupon:'                                => 'کد تخفیف:',
		'Coupon code'                            => 'کد تخفیف',
		'Apply coupon'                           => 'اعمال کد',
		'Update cart'                            => 'به‌روزرسانی سبد',
		'Remove item'                            => 'حذف محصول',
		'Calculate shipping'                     => 'محاسبه هزینه ارسال',
		'Enter your address to view shipping options.' => 'برای دیدن گزینه‌های ارسال، آدرس را وارد کنید.',
		'Description'                            => 'توضیحات',
		'Additional information'                 => 'اطلاعات بیشتر',
		'Reviews'                                => 'نظرات',
		'Reviews (%d)'                           => 'نظرات (%d)',
		'Reviews (%s)'                           => 'نظرات (%s)',
		'Be the first to review &ldquo;%s&rdquo;' => 'اولین نفری باشید که درباره «%s» نظر می‌دهد',
		'Your email address will not be published.' => '',
		'Related products'                       => 'محصولات مرتبط',
		'There are no reviews yet.'              => 'هنوز نظری ثبت نشده است.',
		'Your rating'                            => 'امتیاز شما',
		'Your review'                            => 'نظر شما',
		'Submit'                                 => 'ثبت نظر',
		'Cancel reply'                           => 'انصراف از پاسخ',
		'Leave a Reply'                          => 'ثبت نظر',
		'Category:'                              => 'دسته:',
		'Categories:'                            => 'دسته‌ها:',
		'SKU:'                                   => 'کد محصول:',
		'Choose an option'                       => 'انتخاب کنید',
		'Clear'                                  => 'پاک کردن',
		'Size'                                   => 'سایز',
		'Color'                                  => 'رنگ',
		'Home'                                   => 'خانه',
		'Billing details'                        => 'اطلاعات صورتحساب',
		'Additional information '                => 'توضیحات سفارش',
		'Order notes'                            => 'توضیحات سفارش',
		'Your order'                             => 'سفارش شما',
		'Place order'                            => 'ثبت سفارش و پرداخت',
		'First name'                             => 'نام',
		'Last name'                              => 'نام خانوادگی',
		'Country / Region'                       => 'کشور',
		'Street address'                         => 'آدرس',
		'Town / City'                            => 'شهر',
		'State / County'                         => 'استان',
		'Postcode / ZIP'                         => 'کد پستی',
		'Phone'                                  => 'شماره موبایل',
		'Email address'                          => 'ایمیل',
		'Username or email address'              => 'نام کاربری یا ایمیل',
		'Password'                               => 'رمز عبور',
		'Log in'                                 => 'ورود',
		'Register'                               => 'ثبت‌نام',
		'Login'                                  => 'ورود',
		'Dashboard'                              => 'پیشخوان',
		'Orders'                                 => 'سفارش‌ها',
		'Downloads'                              => 'دانلودها',
		'Addresses'                              => 'آدرس‌ها',
		'Account details'                        => 'اطلاعات حساب',
		'Logout'                                 => 'خروج',
		'My account'                             => 'حساب کاربری',
		'No order has been made yet.'            => 'هنوز سفارشی ثبت نشده است.',
		'Browse products'                        => 'مشاهده محصولات',
		'Order'                                  => 'سفارش',
		'Date'                                   => 'تاریخ',
		'Status'                                 => 'وضعیت',
		'Actions'                                => 'عملیات',
		'View'                                   => 'مشاهده',
		'Sale!'                                  => 'حراج!',
		'Out of stock'                           => 'ناموجود',
		'In stock'                               => 'موجود',
		'Showing the single result'              => 'نمایش یک نتیجه',
		'Default sorting'                        => 'مرتب‌سازی پیش‌فرض',
		'Sort by popularity'                     => 'محبوب‌ترین',
		'Sort by latest'                         => 'جدیدترین',
		'Sort by price: low to high'             => 'ارزان‌ترین',
		'Sort by price: high to low'             => 'گران‌ترین',
		'No products were found matching your selection.' => 'محصولی با این مشخصات پیدا نشد.',
		'Product quantity'                       => 'تعداد محصول',
		'Thumbnail image'                        => 'تصویر',
		'Pending payment'                        => 'در انتظار پرداخت',
		'Processing'                             => 'در حال پردازش',
		'On hold'                                => 'در انتظار بررسی',
		'Completed'                              => 'تکمیل شده',
		'Cancelled'                              => 'لغو شده',
		'Refunded'                               => 'مسترد شده',
		'Failed'                                 => 'ناموفق',
		'Shipping options will be updated during checkout.' => 'گزینه‌های ارسال هنگام تسویه حساب نمایش داده می‌شود.',
		'Select a country / region&hellip;'       => 'انتخاب کشور…',
		'Select a country / region…'             => 'انتخاب کشور…',
		'Select an option&hellip;'               => 'انتخاب کنید…',
		'Select an option…'                      => 'انتخاب کنید…',
		'Update'                                 => 'به‌روزرسانی',
		'Required'                               => 'الزامی',
		'optional'                               => 'اختیاری',
		'(optional)'                             => '(اختیاری)',
		'Have a coupon?'                         => 'کد تخفیف دارید؟',
		'Click here to enter your code'          => 'برای وارد کردن کد کلیک کنید',
		'Create an account?'                     => 'ایجاد حساب کاربری؟',
		'Ship to a different address?'           => 'ارسال به آدرس دیگر؟',
		'Apartment, suite, unit, etc.'           => 'پلاک، واحد، طبقه و …',
		'Apartment, suite, unit, etc. (optional)' => 'پلاک، واحد، طبقه و … (اختیاری)',
		'House number and street name'           => 'نام خیابان و پلاک',
		'Notes about your order, e.g. special notes for delivery.' => 'توضیحات سفارش، مثلاً نکات تحویل',
		'Show password'                          => 'نمایش رمز',
		'Search for:'                            => 'جستجو:',
		'Search &hellip;'                        => 'جستجو …',
		'Search …'                               => 'جستجو …',
		'&ldquo;%s&rdquo; has been added to your cart.' => '«%s» به سبد خرید اضافه شد.',
		'%s has been added to your cart.'        => '%s به سبد خرید اضافه شد.',
		'Lost your password?'                    => 'رمز را فراموش کرده‌اید؟',
		'Remember me'                            => 'مرا به خاطر بسپار',
		'Username or email'                      => 'نام کاربری یا ایمیل',
		'Order notes (optional)'                 => 'توضیحات سفارش (اختیاری)',
		'Payment methods'                        => 'روش‌های پرداخت',
		'Payment method'                         => 'روش پرداخت',
		'Cash on delivery'                       => 'پرداخت در محل',
		'Pay with cash upon delivery.'           => 'پرداخت وجه هنگام تحویل.',
		'Flat rate'                              => 'ارسال استاندارد',
		'Free shipping'                          => 'ارسال رایگان',
		'Thank you. Your order has been received.' => 'سپاس از شما؛ سفارشتان ثبت شد.',
		'Order number:'                          => 'شماره سفارش:',
		'Order details'                          => 'جزئیات سفارش',
		'Customer details'                       => 'اطلاعات مشتری',
		'Billing address'                        => 'آدرس صورتحساب',
		'Shipping address'                       => 'آدرس ارسال',
		'Save changes'                           => 'ذخیره تغییرات',
		'Edit'                                   => 'ویرایش',
		'Pay'                                    => 'پرداخت',
		'Cancel'                                 => 'لغو',
		'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.' => 'اطلاعات شما فقط برای پردازش سفارش و پشتیبانی استفاده می‌شود؛ جزئیات در %s آمده است.',
		'privacy policy'                         => 'سیاست حریم خصوصی',
	);
}

/**
 * Fill in missing translations.
 *
 * @param string $translated Translated text.
 * @param string $text       Original text.
 * @param string $domain     Text domain.
 * @return string
 */
function moon_woo_fa_gettext( $translated, $text, $domain ) {
	static $map = null;

	if ( $translated !== $text || ( 'woocommerce' !== $domain && 'default' !== $domain ) ) {
		return $translated;
	}

	if ( null === $map ) {
		$map = ( 0 === strpos( determine_locale(), 'fa' ) ) ? moon_woo_fa_strings() : array();
	}

	return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
}
add_filter( 'gettext', 'moon_woo_fa_gettext', 20, 3 );

/**
 * Common attribute labels.
 *
 * @param string $label Attribute label.
 * @return string
 */
function moon_woo_fa_attribute_label( $label ) {
	static $map = array(
		'Size'  => 'سایز',
		'Color' => 'رنگ',
		'Colour' => 'رنگ',
	);

	return ( 0 === strpos( determine_locale(), 'fa' ) && isset( $map[ $label ] ) ) ? $map[ $label ] : $label;
}
add_filter( 'woocommerce_attribute_label', 'moon_woo_fa_attribute_label' );

/**
 * Country names shown on the cart/checkout.
 *
 * @param array $countries Countries.
 * @return array
 */
function moon_woo_fa_countries( $countries ) {
	if ( 0 === strpos( determine_locale(), 'fa' ) && isset( $countries['IR'] ) ) {
		$countries['IR'] = 'ایران';
	}

	return $countries;
}
add_filter( 'woocommerce_countries', 'moon_woo_fa_countries' );

add_filter( 'gettext_with_context', function ( $translated, $text, $context, $domain ) {
	return moon_woo_fa_gettext( $translated, $text, $domain );
}, 20, 4 );

/**
 * Plural strings (e.g. "“%s” has been added to your cart").
 */
add_filter( 'ngettext', function ( $translation, $single, $plural, $number, $domain ) {
	if ( 'woocommerce' !== $domain || 0 !== strpos( determine_locale(), 'fa' ) ) {
		return $translation;
	}

	$map = array(
		'%s has been added to your cart.' => '%s به سبد خرید اضافه شد.',
		'%s item'                         => '%s محصول',
		'%s review'                       => '%s نظر',
	);

	return ( $translation === $single || $translation === $plural ) && isset( $map[ $single ] ) ? $map[ $single ] : $translation;
}, 20, 5 );
