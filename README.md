# MOON — فروشگاه پوشاک لوکس (وردپرس + المنتور + ووکامرس)

فروشگاه اینترنتی **MOON** به‌صورت یک افزونه اختصاصی وردپرس (`Moon Core`) توسعه داده شده است که روی قالب **Hello Elementor**، **Elementor** و **WooCommerce** اجرا می‌شود. تمام قابلیت‌های برند (هدر شیشه‌ای شناور، هیرو سینمایی، حالت شب، فیلترهای واقعی ووکامرس، ویجت‌های المنتور) داخل افزونه پیاده‌سازی شده و هیچ فایلی از قالب یا افزونه‌های دیگر تغییر نکرده است.

## بسته‌های آماده نصب

| فایل | نوع | کاربرد |
| --- | --- | --- |
| [`dist/moon-core.zip`](dist/moon-core.zip) | افزونه | نصب از پیشخوان ← افزونه‌ها ← افزودن ← بارگذاری افزونه |
| [`dist/moon-elementor-templates.zip`](dist/moon-elementor-templates.zip) | تمپلیت المنتور | صفحه اصلی + قالب Header و Footer |
| [`templates/elementor/home.json`](templates/elementor/home.json) | تمپلیت المنتور | نسخه تک‌فایل صفحه اصلی |

راهنمای کامل نصب: [`docs/installation.md`](docs/installation.md) — راهنمای ویرایش و تنظیمات: [`docs/editing-guide.md`](docs/editing-guide.md) — وابستگی‌ها: [`docs/dependencies.md`](docs/dependencies.md) — گزارش تست و محدودیت‌ها: [`docs/test-report.md`](docs/test-report.md)

## ساختار مخزن

```
plugins/moon-core/            افزونه اختصاصی
  moon-core.php               بوت‌استرپ و هوک‌ها
  includes/                   تنظیمات، دارایی‌ها، حالت شب، هدر/فوتر، المنتور، ووکامرس، درون‌ریز، فرم‌ها
  widgets/                    هفت ویجت واقعی المنتور
  assets/                     CSS/JS، فونت Vazirmatn، تصاویر و ویدئوی هیرو، فایل‌های RTL
  languages/                  فایل POT برای ترجمه
templates/elementor/          تمپلیت‌های JSON المنتور (خروجی واقعی)
tools/                        ابزار ساخت تصاویر، خروجی تمپلیت و بسته نصبی
sandbox/                      فقط محیط توسعه Base44 (compose، mu-plugin پیش‌نمایش، اسکریپت نصب)
docs/                         مستندات فارسی
```

پوشه `sandbox/`، `docker-compose.base44.yml` و `tools/` بخشی از محصول نیستند و روی هاست واقعی لازم نمی‌شوند.

## اجرای محیط توسعه (Base44)

```sh
docker compose -f docker-compose.base44.yml up -d
# http://localhost:3000  — وردپرس فارسی، Hello Elementor، المنتور، ووکامرس و محتوای نمایشی
```

اسکریپت `sandbox/setup.sh` به‌صورت idempotent مضمون «هسته وردپرس + قالب + افزونه‌ها + درون‌ریزی ۱۲ محصول متغیر + ۱۲ صفحه + صفحه اصلی المنتور» را می‌سازد.

## بازسازی بسته‌ها

```sh
docker run --rm -v "$PWD:/repo" -w /repo wordpress:7.1.4-php8.2-apache php tools/build-plugin.php
```

پس از هر تغییر در `plugins/moon-core/` این دستور را اجرا کنید تا `dist/*.zip` به‌روز شود.
