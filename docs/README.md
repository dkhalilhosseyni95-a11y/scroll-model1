# MOON ULTIMATE 3.0.0

قالب فروشگاهی وردپرس/ووکامرس MOON ULTIMATE، حاصل ادغام MOON 2.2.0 (هسته) و MOON 2.1.2
(تنظیمات، سازنده‌ها و Design Tokens) به‌همراه دو افزونه همراه.

## محتویات بسته

| فایل | توضیح |
| --- | --- |
| `moon-ultimate-3.0.0.zip` | قالب (پوشه `moon-ultimate/`) |
| `moon-companion-1.0.0.zip` | افزونه قابلیت‌های حیاتی فروشگاه و مشتری (درگاه زیبال، ورود با موبایل، قفل تسویه‌حساب، همگام‌سازی علاقه‌مندی‌ها) |
| `moon-demo-importer-3.0.0.zip` | افزونه درون‌ریز محتوای نمونه با گزارش پیشرفت |
| `moon-ultimate-3.0.0-package.zip` | هر سه مورد بالا + مستندات |

## نصب

1. وردپرس ۶.۴+ و ووکامرس را نصب کنید.
2. قالب `moon-ultimate` و افزونه‌های `moon-companion` و `moon-demo-importer` را نصب و فعال کنید.
3. از منوی «MOON» در پیشخوان، دمو را وارد کنید (یا با WP-CLI: `wp moon-demo import --products --catalog`).
4. زبان سایت را فارسی (`fa_IR`) بگذارید تا ترجمه‌های قالب برای رشته‌های ووکامرس اعمال شود.

## محیط توسعه

```bash
docker compose -f docker-compose.base44.yml up -d --build
tools/setup-wordpress.sh --with-demo --with-products   # نصب وردپرس، ووکامرس، فعال‌سازی و ایمپورت دمو
```

سایت روی `http://localhost:3000` بالا می‌آید. ساخت بسته‌های نصب با `tools/build-zips.sh`.
