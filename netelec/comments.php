<?php
/**
 * Comments.
 *
 * @package Netelec
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$netelec_count = get_comments_number();
			printf( '%s نظر', esc_html( number_format_i18n( $netelec_count ) ) );
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => 'قبلی',
				'next_text' => 'بعدی',
			)
		);
		?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => 'دیدگاه خود را بنویسید',
			'label_submit'       => 'ارسال دیدگاه',
			'comment_notes_before' => '<p class="comment-notes">ایمیل شما منتشر نمی‌شود.</p>',
		)
	);
	?>
</section>
