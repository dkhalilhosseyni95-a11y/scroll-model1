<?php
/**
 * Document footer.
 *
 * @package Moon_Theme
 */

?>
</main><!-- #main -->

<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) : ?>
<?php if ( moon_opt( 'footer_newsletter', '1' ) ) : ?>
<?php get_template_part( 'template-parts/footer/newsletter' ); ?>
<?php endif; ?>
<?php get_template_part( 'template-parts/footer/site-footer' ); ?>
<?php endif; ?>
<?php get_template_part( 'template-parts/footer/quick-view' ); ?>
<?php get_template_part( 'template-parts/header/bottom-nav' ); ?>

<?php wp_footer(); ?>
</body>
</html>
