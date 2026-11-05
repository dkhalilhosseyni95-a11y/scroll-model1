# DaniSite — قالب وردپرس + افزونه هسته

پروژه کامل سایت «دنی سایت» بر پایه خروجی طراحی Lovable: یک قالب والد، یک قالب فرزند و افزونه اختصاصی `DaniSite Core` (نوع‌های محتوا، ویجت‌های المنتور، تنظیمات، سئو، فرم تماس و درون‌ریز یک‌کلیکی).

## بسته‌های آماده نصب

سه فایل زیر را از پوشه [`dist/`](dist) بردارید. همین فایل‌ها را می‌توانید مستقیم در پیشخوان وردپرس آپلود کنید.

| فایل | نوع | توضیح |
| --- | --- | --- |
| `dist/danisite-parent.zip` | قالب | قالب اصلی (طراحی، ارجاع‌ها، استایل‌ها، فونت) |
| `dist/danisite-child.zip` | قالب | قالب فرزند برای شخصی‌سازی پایدار |
| `dist/danisite-core.zip` | افزونه | هسته سایت: محتوا، المنتور، سئو، فرم، درون‌ریز |

برای ساخت دوباره این بسته‌ها بعد از هر تغییر:

```sh
docker compose -f docker-compose.base44.yml run --rm -T --user 0:0 --entrypoint php -v "$PWD:/opt/repo" setup /opt/repo/tools/build-packages.php
```

## نصب روی هاست واقعی

خلاصه: قالب والد ← قالب فرزند ← افزونه ← فعال‌سازی Elementor ← منوی **DaniSite → راه‌اندازی و درون‌ریزی**.

راهنمای کامل گام‌به‌گام: [`docs/installation.md`](docs/installation.md)

## ساختار مخزن

```
themes/danisite-parent/   قالب اصلی (PHP، CSS، JS، فونت Vazirmatn)
themes/danisite-child/    قالب فرزند
plugins/danisite-core/    افزونه هسته (CPT ها، ویجت‌های المنتور، سئو، فرم، درون‌ریز)
sandbox/                  فقط محیط توسعه Base44 (compose، mu-plugin پیش‌نمایش، اسکریپت نصب)
tools/build-packages.php  ساخت فایل‌های نصب
docs/                     مستندات فارسی (نصب، مدیریت محتوا، گزارش تست)
dist/                     بسته‌های zip آماده نصب
```

پوشه `sandbox/` بخشی از محصول نیست و روی هاست واقعی لازم نمی‌شود.

## اجرای محیط توسعه

```sh
docker compose -f docker-compose.base44.yml up -d
# http://localhost:3000
```

## مستندات

- [راهنمای نصب و راه‌اندازی](docs/installation.md)
- [راهنمای مدیریت محتوا](docs/content-guide.md)
- [گزارش تست و وضعیت پروژه](docs/test-report.md)
