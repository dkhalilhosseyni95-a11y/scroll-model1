<?php
/**
 * Product comparison page.
 *
 * Rendered from the `[moon_compare]` shortcode so the page also works when the
 * owner points an existing page at this template.
 *
 * @package Moon_Theme
 */

get_header();
?>
<div class="moon-page-body">
	<div class="moon-container">
		<header class="moon-page-head">
			<p class="moon-eyebrow"><span dir="ltr">Compare</span></p>
			<h1 class="moon-page-title"><?php the_title(); ?></h1>
		</header>

		<div class="moon-content">
			<?php
			while ( have_posts() ) {
				the_post();
				the_content();
			}
			?>
		</div>
	</div>
</div>
<?php
get_footer();
