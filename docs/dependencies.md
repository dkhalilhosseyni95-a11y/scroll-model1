# فهرست وابستگی‌ها

## الزامی

| مورد | نسخه تست‌شده در این مخزن | توضیح |
| --- | --- | --- |
| WordPress | 7.1.4 | حداقل نیاز افزونه: ۶.۴ · `Requires PHP: 8.0` |
| PHP | 8.2 | بدون وابستگی به اکستنشن خاص (GD فقط برای ابزار تولید تصویر نمایشی لازم است) |
| Hello Elementor | 3.5.2 | قالب فعال؛ فایل‌های آن دست‌نخورده می‌ماند |
| Elementor (رایگان) | 4.4.0 | ویجت‌ها از API رسمی `elementor/widgets/register` ثبت می‌شوند |
| WooCommerce | 11.2.2 | محصولات، Variations، سبد، تسویه، حساب کاربری، فیلترهای آرشیو |

## اختیاری / محیط مقصد

| مورد | وضعیت در Moon Core |
| --- | --- |
| Elementor Pro | قالب‌های Header/Footer به‌صورت JSON آماده است و در درون‌ریزی به‌عنوان `elementor_library` ساخته می‌شود. بدون Pro، هدر/فوتر افزونه جای آن را می‌گیرد. |
| YITH WooCommerce Wishlist | در صورت فعال بودن، دکمه‌های داخلی غیرفعال و لینک هدر به صفحه YITH هدایت می‌شود. |
| Gravity Forms | هیچ تداخلی ندارد؛ فرم تماس پیش‌فرض افزونه (`[moon_contact_form]`) قابل جایگزینی با شورت‌کد Gravity Forms است. |
| LiteSpeed Cache | CSS/JS با هندل استاندارد enqueue می‌شوند و با ترکیب/به‌تعویق‌انداختن سازگارند. |
| Polylang / WPML | متن‌ها با text domain ترجمه‌پذیرند و صفحات/محصولات با همین افزونه‌ها ترجمه می‌شوند. |

## کتابخانه‌های سمت کاربر

- **هیچ** کتابخانه خارجی (jQuery وابستگی نیست، Slick/Swiper/AOS استفاده نشده). کل رفتار با Vanilla JS در `assets/js/moon-core.js` نوشته شده است.
- فونت‌ها: Vazirmatn (OFL) و Jost (OFL) — self-host در `assets/fonts/` با `preload`.

## ابزارهای توسعه (بخشی از محصول نیستند)

- `tools/make-images.php` — تولید تصاویر نمایشی با GD
- `tools/export-templates.php` — خروجی JSON تمپلیت‌های المنتور از همان داده‌های درون‌ریز
- `tools/build-plugin.php` — ساخت `dist/moon-core.zip` و `dist/moon-elementor-templates.zip`
- تصویر ویدئوی هیرو با ffmpeg (H.264, 1280×720, CRF 30, ~226KB) تولید شده است.
