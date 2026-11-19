<?php
/**
 * Blog index / fallback template.
 *
 * @package Netelec
 */

get_header();

$netelec_cols = max( 1, (int) netelec_opt( 'blog_columns' ) );
?>
<?php netelec_page_header( 'وبلاگ', 'مقالات، راهنما و نکات تخصصی دنیای شبکه' ); ?>

<div class="container blog-layout<?php echo netelec_opt( 'blog_sidebar' ) ? ' blog-layout--sidebar' : ''; ?>">
	<div class="blog-main">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid" style="--nc-cols:<?php echo esc_attr( $netelec_cols ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content-card' );
				endwhile;
				?>
			</div>
			<?php netelec_pagination(); ?>
		<?php else : ?>
			<div class="shop-empty">
				<?php netelec_icon( 'file' ); ?>
				<h2>مطلبی پیدا نشد</h2>
				<p>هنوز مقاله‌ای منتشر نشده است.</p>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( netelec_opt( 'blog_sidebar' ) && is_active_sidebar( 'blog-sidebar' ) ) : ?>
		<aside class="blog-sidebar">
			<?php dynamic_sidebar( 'blog-sidebar' ); ?>
		</aside>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
