<?php
/**
 * Template Name: درباره ما — MOON
 *
 * The page editor content replaces the starter copy, so the owner can fully
 * customise this page without touching theme files.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$moon_custom = '' !== trim( (string) get_the_content() );
	?>
	<article <?php post_class( 'moon-page moon-about' ); ?>>
		<?php moon_page_header(); ?>

		<div class="moon-page-body">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="moon-about__cover"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?></figure>
			<?php endif; ?>

			<div class="moon-content">
				<?php if ( $moon_custom ) : ?>
					<?php the_content(); ?>
				<?php else : ?>
					<p class="moon-about__lead"><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php esc_html_e( 'فروشگاه پوشاک؛ جایی برای انتخاب آگاهانه‌تر و ساختن استایلی که به شما می‌آید.', 'moon-theme' ); ?></p>
					<h2><?php esc_html_e( 'انتخابی ساده‌تر، تجربه‌ای مطمئن‌تر', 'moon-theme' ); ?></h2>
					<p><?php esc_html_e( 'تلاش می‌کنیم مجموعه‌ای از پوشاک را با توجه به استایل، کاربرد و سلیقه‌های متفاوت گردآوری کنیم تا پیش از خرید بتوانید تصاویر، رنگ‌ها و سایزبندی را با دقت بررسی کنید.', 'moon-theme' ); ?></p>
					<h2><?php esc_html_e( 'نگاه ما به خرید آنلاین', 'moon-theme' ); ?></h2>
					<p><?php esc_html_e( 'خرید اینترنتی باید ساده باشد: از جستجو و مقایسه تا ثبت سفارش و پیگیری آن. اطلاعات محصولات را شفاف نگه می‌داریم و مسیر خرید را کوتاه.', 'moon-theme' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( moon_is_woo() ) : ?>
				<p><a class="moon-btn moon-btn--dark" href="<?php echo esc_url( moon_shop_url() ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'moon-theme' ); ?></a></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
