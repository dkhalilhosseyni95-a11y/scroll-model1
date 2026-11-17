<?php
/**
 * Legal page layout: title, last revised date, an index of the sections and the
 * content. The editor content wins; the shipped copy is used while the page is
 * still empty.
 *
 * @param array $args { kind: privacy|terms }
 *
 * @package Moon_Theme
 */

defined( 'ABSPATH' ) || exit;

$moon_kind    = isset( $args['kind'] ) && 'terms' === $args['kind'] ? 'terms' : 'privacy';
$moon_custom  = '' !== trim( (string) get_the_content() );
$moon_content = $moon_custom ? apply_filters( 'the_content', get_the_content() ) : moon_legal_content_html( $moon_kind );

/* Give every <h2> an id so the index can jump to it. */
$moon_toc = array();
$moon_i   = 0;

$moon_content = preg_replace_callback(
	'#<h2([^>]*)>(.*?)</h2>#is',
	function ( $matches ) use ( &$moon_toc, &$moon_i ) {
		$moon_i++;
		$attrs = $matches[1];
		$id    = 'moon-legal-' . $moon_i;

		if ( preg_match( '/id=(["\'])([^"\']+)\1/', $attrs, $found ) ) {
			$id = $found[2];
		} else {
			$attrs .= ' id="' . $id . '"';
		}

		$moon_toc[] = array(
			'id'    => $id,
			'title' => wp_strip_all_tags( $matches[2] ),
		);

		return '<h2' . $attrs . '>' . $matches[2] . '</h2>';
	},
	$moon_content
);
?>
<article <?php post_class( 'moon-page moon-legal' ); ?>>
	<header class="moon-legal__head">
		<?php moon_breadcrumb(); ?>
		<h1 class="moon-legal__title"><?php the_title(); ?></h1>
		<p class="moon-legal__updated">
			<?php
			printf(
				/* translators: %s: date. */
				esc_html__( 'آخرین بازنگری: %s', 'moon-theme' ),
				esc_html( get_the_modified_date() )
			);
			?>
		</p>
	</header>

	<div class="moon-legal__layout">
		<?php if ( $moon_toc ) : ?>
			<aside class="moon-legal__toc" aria-label="<?php esc_attr_e( 'فهرست این صفحه', 'moon-theme' ); ?>">
				<p class="moon-legal__toc-title"><?php esc_html_e( 'در این صفحه', 'moon-theme' ); ?></p>
				<ol class="moon-legal__toc-list">
					<?php foreach ( $moon_toc as $moon_item ) : ?>
						<li>
							<a href="#<?php echo esc_attr( $moon_item['id'] ); ?>"><?php echo esc_html( $moon_item['title'] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ol>
			</aside>
		<?php endif; ?>

		<div class="moon-legal__content moon-content">
			<?php echo wp_kses_post( $moon_content ); ?>
		</div>
	</div>
</article>
