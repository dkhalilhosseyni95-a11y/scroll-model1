<?php
/**
 * Base class shared by every MOON widget.
 *
 * @package MoonCore
 */

namespace MoonCore;

use Elementor\Widget_Base as Elementor_Widget_Base;

defined( 'ABSPATH' ) || exit;

abstract class Widget_Base extends Elementor_Widget_Base {

	use Controls;

	/**
	 * Widget slug — also the folder name under /widgets.
	 */
	abstract protected function slug(): string;

	/**
	 * Elementor category slug.
	 */
	protected function category(): string {
		return 'moon-brand';
	}

	public function get_name(): string {
		return 'moon-' . $this->slug();
	}

	public function get_categories(): array {
		return array( $this->category() );
	}

	public function get_style_depends(): array {
		$definitions = Widget_Registry::instance()->all();
		$slug        = $this->slug();

		$deps = array( 'moon-core-base' );

		// Chrome styling is global; header/footer widgets use it directly.
		if ( in_array( $slug, array( 'header', 'footer', 'mini-cart' ), true ) ) {
			$deps[] = 'moon-core-chrome';
		}

		if ( ! empty( $definitions[ $slug ]['css'] ) ) {
			$deps[] = 'moon-core-' . $slug;
		}

		return $deps;
	}

	public function get_script_depends(): array {
		$definitions = Widget_Registry::instance()->all();
		$slug        = $this->slug();

		return ! empty( $definitions[ $slug ]['js'] ) ? array( 'moon-core-' . $slug ) : array();
	}

	/**
	 * True inside the Elementor editor / preview iframe.
	 */
	protected function is_edit_mode(): bool {
		return \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| \Elementor\Plugin::$instance->preview->is_preview_mode();
	}

	/**
	 * Data attributes consumed by assets/js/base.js (reveal animation).
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 */
	protected function reveal_attributes( array $settings ): string {
		$reveal = $settings['reveal'] ?? 'fade-up';
		if ( 'none' === $reveal ) {
			return '';
		}

		$delay   = isset( $settings['reveal_delay']['size'] ) ? (int) $settings['reveal_delay']['size'] : 0;
		$stagger = isset( $settings['reveal_stagger']['size'] ) ? (int) $settings['reveal_stagger']['size'] : 0;

		return sprintf(
			' data-moon-reveal="%1$s" data-moon-reveal-delay="%2$d" data-moon-reveal-stagger="%3$d"',
			esc_attr( $reveal ),
			$delay,
			$stagger
		);
	}

	/**
	 * Standard section opener markup (eyebrow + title + description).
	 *
	 * @param array<string,mixed> $settings  Widget settings.
	 * @param string              $prefix    Control prefix.
	 * @param string              $extra     Extra classes.
	 */
	protected function render_heading( array $settings, string $prefix = 'heading', string $extra = '' ): void {
		$eyebrow = $settings[ $prefix . '_eyebrow' ] ?? '';
		$title   = $settings[ $prefix . '_title' ] ?? '';
		$desc    = $settings[ $prefix . '_description' ] ?? '';
		$tag     = $this->heading_tag( $settings[ $prefix . '_title_tag' ] ?? 'h2' );

		if ( '' === $eyebrow && '' === $title && '' === $desc ) {
			return;
		}

		echo '<div class="moon-section__head ' . esc_attr( $extra ) . '" data-moon-reveal-item>';
		if ( '' !== $eyebrow ) {
			echo '<p class="moon-eyebrow">' . esc_html( $eyebrow ) . '</p>';
		}
		if ( '' !== $title ) {
			printf(
				'<%1$s class="moon-section__title">%2$s</%1$s>',
				esc_html( $tag ),
				wp_kses_post( nl2br( $title ) )
			);
		}
		if ( '' !== $desc ) {
			echo '<p class="moon-section__desc moon-lede">' . wp_kses_post( $desc ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Whitelisted heading tag.
	 */
	protected function heading_tag( string $tag ): string {
		return in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'div', 'span' ), true ) ? $tag : 'h2';
	}

	/**
	 * Render a link control with escaped text.
	 *
	 * @param array<string,mixed> $link  Elementor URL value.
	 * @param string              $class Classes.
	 * @param string              $text  Link label.
	 * @param string              $icon  Icon slug appended after the label.
	 */
	protected function render_link( array $link, string $class, string $text, string $icon = '' ): void {
		if ( '' === trim( $text ) ) {
			return;
		}

		$inner = esc_html( $text );
		if ( '' !== $icon ) {
			$inner .= Helpers::svg( Helpers::icon( $icon, 16 ) );
		}

		echo Helpers::link( $link, $class, $inner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Helpers::link().
	}

	/**
	 * Editor-only hint shown when a widget needs context it does not have.
	 *
	 * @param string $message Hint text.
	 */
	protected function render_notice( string $message ): void {
		if ( ! $this->is_edit_mode() ) {
			return;
		}

		printf(
			'<p class="moon-widget-notice">%s</p>',
			esc_html( $message )
		);
	}
}
