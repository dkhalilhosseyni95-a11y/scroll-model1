<?php
/**
 * Template helpers used by the theme templates.
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Section heading used across the homepage ("eyebrow" + title).
 *
 * @param string $eyebrow Small uppercase label.
 * @param string $title   Section title.
 * @param string $class   Extra classes for the wrapper.
 */
function moon_section_title( $eyebrow, $title, $class = '' ) {
	?>
	<div class="moon-reveal moon-section-title <?php echo esc_attr( $class ); ?>">
		<p class="moon-eyebrow moon-section-title__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h2 class="moon-section-title__title"><?php echo wp_kses( $title, array( 'br' => array() ) ); ?></h2>
	</div>
	<?php
}

/**
 * Page title block for inner pages.
 *
 * @param string $subtitle Optional subtitle.
 */
function moon_page_header( $subtitle = '' ) {
	$title = '';

	if ( is_home() && ! is_front_page() ) {
		$title = single_post_title( '', false );
	} elseif ( is_search() ) {
		/* translators: %s: search query. */
		$title = sprintf( __( 'نتایج جستجو برای «%s»', 'moon-theme' ), get_search_query() );
	} elseif ( is_404() ) {
		$title = __( 'صفحه پیدا نشد', 'moon-theme' );
	} elseif ( is_archive() ) {
		$title = get_the_archive_title();
	} elseif ( is_singular() ) {
		$title = get_the_title();
	}

	if ( ! $title ) {
		return;
	}
	?>
	<header class="moon-page-header">
		<div class="moon-container">
			<?php moon_breadcrumb(); ?>
			<h1 class="moon-page-header__title"><?php echo wp_kses_post( $title ); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p class="moon-page-header__sub"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Lightweight breadcrumb trail. WooCommerce breadcrumbs are used on shop screens.
 */
function moon_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}

	$items = array(
		array(
			'label' => __( 'خانه', 'moon-theme' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular() ) {
		$post_type = get_post_type();

		if ( 'post' === $post_type ) {
			$blog = get_permalink( get_option( 'page_for_posts' ) );
			if ( $blog ) {
				$items[] = array(
					'label' => get_the_title( get_option( 'page_for_posts' ) ),
					'url'   => $blog,
				);
			}
		}

		$items[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_archive() ) {
		$items[] = array(
			'label' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'label' => __( 'جستجو', 'moon-theme' ),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'label' => __( 'صفحه پیدا نشد', 'moon-theme' ),
			'url'   => '',
		);
	}
	?>
	<nav class="moon-breadcrumb" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'moon-theme' ); ?>">
		<?php
		$last = count( $items ) - 1;
		foreach ( $items as $index => $item ) {
			if ( $index > 0 ) {
				echo '<span aria-hidden="true">/</span>';
			}

			if ( $item['url'] && $index !== $last ) {
				printf( '<a href="%s">%s</a>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
			} else {
				printf( '<span aria-current="page">%s</span>', esc_html( $item['label'] ) );
			}
		}
		?>
	</nav>
	<?php
}

/**
 * Post meta line.
 */
function moon_posted_on() {
	printf(
		'<span class="moon-posted-on"><time datetime="%1$s">%2$s</time></span>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}

/**
 * Numeric pagination.
 */
function moon_pagination() {
	$links = paginate_links( array(
		'type'      => 'array',
		'prev_text' => __( 'قبلی', 'moon-theme' ),
		'next_text' => __( 'بعدی', 'moon-theme' ),
	) );

	if ( empty( $links ) ) {
		return;
	}
	?>
	<nav class="moon-pagination" aria-label="<?php esc_attr_e( 'صفحه‌بندی', 'moon-theme' ); ?>">
		<?php foreach ( $links as $link ) : ?>
			<span class="moon-pagination__item"><?php echo wp_kses_post( $link ); ?></span>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * Render a homepage section when it is enabled and has a template part.
 *
 * @param string $slug Section slug.
 */
function moon_render_home_section( $slug ) {
	$file = MOON_THEME_DIR . '/template-parts/homepage/' . $slug . '.php';

	if ( file_exists( $file ) ) {
		get_template_part( 'template-parts/homepage/' . $slug );
	}
}

/**
 * Icon used as a fallback when WooCommerce has no product image.
 *
 * @param int $size Size in pixels.
 */
function moon_placeholder_icon( $size = 40 ) {
	echo '<span class="moon-placeholder" aria-hidden="true">' . moon_icon( 'shopping-bag', $size, 'moon-placeholder__icon' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Comment template.
 */
function moon_comments() {
	if ( post_password_required() || ( ! comments_open() && ! have_comments() ) ) {
		return;
	}
	?>
	<section class="moon-comments" id="comments">
		<?php if ( have_comments() ) : ?>
			<h2 class="moon-comments__title">
				<?php
				$count = get_comments_number();
				printf( esc_html( _n( '%s دیدگاه', '%s دیدگاه', $count, 'moon-theme' ) ), esc_html( number_format_i18n( $count ) ) );
				?>
			</h2>
			<ol class="moon-comments__list">
				<?php
				wp_list_comments( array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 56,
				) );
				?>
			</ol>
			<?php the_comments_pagination(); ?>
		<?php endif; ?>
		<?php comment_form(); ?>
	</section>
	<?php
}
