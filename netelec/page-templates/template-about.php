<?php
/**
 * Template Name: درباره ما
 *
 * @package Netelec
 */

get_header();

$netelec_values = array(
	array( 'award', 'تخصص در تجهیزات شبکه', 'تمرکز بر کابل، سوکت، تجهیزات پسیو و اکتیو؛ نه فروشگاه عمومی.' ),
	array( 'shield', 'تأمین از منابع معتبر', 'انتخاب کالا بر اساس اصالت، استاندارد و کاربرد واقعی در پروژه.' ),
	array( 'headset', 'مشاوره پیش از خرید', 'کمک به انتخاب درست تجهیزات بر اساس نیاز پروژه و بودجه.' ),
	array( 'refresh', 'پشتیبانی پس از فروش', 'پیگیری سفارش، راهنمای نصب و پاسخ به سوالات فنی.' ),
);
?>
<?php netelec_page_header( get_the_title(), 'درباره NETELEC' ); ?>

<div class="container about-page">
	<div class="about-intro">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div class="entry-content"><?php the_content(); ?></div>
			<?php
		endwhile;
		?>
	</div>

	<ul class="about-values">
		<?php foreach ( $netelec_values as $netelec_value ) : ?>
			<li class="about-value">
				<span class="about-value__icon"><?php netelec_icon( $netelec_value[0] ); ?></span>
				<h2><?php echo esc_html( $netelec_value[1] ); ?></h2>
				<p><?php echo esc_html( $netelec_value[2] ); ?></p>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="about-cta">
		<div>
			<h2>برای انتخاب درست تجهیزات، کمک می‌خواهید؟</h2>
			<p>کارشناسان ما بر اساس نیاز پروژه، پیشنهاد دقیق می‌دهند.</p>
		</div>
		<div class="about-cta__actions">
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="btn btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">مشاهده محصولات</a>
			<?php endif; ?>
			<a class="btn btn--ghost" href="<?php echo esc_url( netelec_page_url( 'contact' ) ); ?>">تماس با ما</a>
		</div>
	</div>
</div>

<?php get_footer(); ?>
