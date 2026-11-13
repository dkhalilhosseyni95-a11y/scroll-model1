<?php
/**
 * MOON Newsletter — signup strip or panel.
 *
 * Signups go to the endpoint configured in Moon Core settings: the private
 * WordPress subscriber list by default, or the configured email-provider URL.
 *
 * @package MoonCore
 */

namespace MoonCore\Widgets;

use Elementor\Controls_Manager;
use MoonCore\Helpers;
use MoonCore\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Newsletter extends Widget_Base {

	protected function slug(): string {
		return 'newsletter';
	}

	public function get_title(): string {
		return __( 'MOON Newsletter', 'moon-core' );
	}

	public function get_icon(): string {
		return 'eicon-email-field';
	}

	public function get_keywords(): array {
		return array( 'newsletter', 'email', 'subscribe', 'moon' );
	}

	protected function register_controls(): void {
		$this->moon_heading_controls(
			'heading',
			__( 'Copy', 'moon-core' ),
			array(
				'eyebrow'     => __( 'The list', 'moon-core' ),
				'title'       => __( 'Be first to the cutting table', 'moon-core' ),
				'description' => __( 'New drops, restocks and studio notes. One considered email a month.', 'moon-core' ),
			)
		);

		$this->start_controls_section( 'section_form', array( 'label' => __( 'Form', 'moon-core' ) ) );

		$this->add_control(
			'placeholder',
			array(
				'label'   => __( 'Input placeholder', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'you@example.com', 'moon-core' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Button text', 'moon-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Subscribe', 'moon-core' ),
			)
		);

		$this->add_control(
			'require_consent',
			array(
				'label'        => __( 'Require privacy consent', 'moon-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'consent_text',
			array(
				'label'       => __( 'Consent text', 'moon-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'I agree to receive MOON emails and accept the privacy policy.', 'moon-core' ),
				'label_block' => true,
				'condition'   => array( 'require_consent' => 'yes' ),
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => __( 'Small print', 'moon-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'No noise. Unsubscribe in one click.', 'moon-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => __( 'Layout', 'moon-core' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'moon-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'panel',
				'options' => array(
					'panel'    => __( 'Panel with background', 'moon-core' ),
					'inline'   => __( 'Inline strip', 'moon-core' ),
					'centered' => __( 'Centred, no panel', 'moon-core' ),
				),
			)
		);

		$this->add_control(
			'background_image',
			array(
				'label'     => __( 'Background image', 'moon-core' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'layout' => 'panel' ),
			)
		);

		$this->add_control(
			'overlay_strength',
			array(
				'label'      => __( 'Image scrim', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'    => array( 'size' => 62 ),
				'condition'  => array( 'layout' => 'panel' ),
				'selectors'  => array( '{{WRAPPER}} .moon-news__scrim' => 'opacity: calc({{SIZE}} / 100);' ),
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Panel padding', 'moon-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem' ),
				'default'    => array( 'top' => '3.5', 'right' => '3', 'bottom' => '3.5', 'left' => '3', 'unit' => 'rem', 'isLinked' => false ),
				'selectors'  => array( '{{WRAPPER}} .moon-news__inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'      => __( 'Corner radius', 'moon-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'default'    => array( 'size' => 12 ),
				'selectors'  => array( '{{WRAPPER}} .moon-news__inner' => 'border-radius: {{SIZE}}px;' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_panel',
			array(
				'label' => __( 'Panel', 'moon-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'panel_bg',
			array(
				'label'     => __( 'Background', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-news__inner' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'panel_color',
			array(
				'label'     => __( 'Text colour', 'moon-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .moon-news__copy' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->moon_text_style( 'title', __( 'Title', 'moon-core' ), '.moon-news .moon-section__title' );
		$this->moon_text_style( 'desc', __( 'Description', 'moon-core' ), '.moon-news .moon-section__desc' );
		$this->moon_button_style( 'button', __( 'Button', 'moon-core' ), '.moon-news__submit' );

		$this->moon_motion_controls();

		$this->moon_visibility_controls(
			array(
				'eyebrow' => array( __( 'Eyebrow', 'moon-core' ), '.moon-news .moon-eyebrow' ),
				'desc'    => array( __( 'Description', 'moon-core' ), '.moon-news .moon-section__desc' ),
				'note'    => array( __( 'Small print', 'moon-core' ), '.moon-news__note' ),
				'consent' => array( __( 'Consent line', 'moon-core' ), '.moon-newsletter-form__consent' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$layout   = in_array( $settings['layout'] ?? 'panel', array( 'panel', 'inline', 'centered' ), true ) ? $settings['layout'] : 'panel';
		$image    = (array) ( $settings['background_image'] ?? array() );
		$consent  = 'yes' === ( $settings['require_consent'] ?? 'yes' );
		$consent_message = __( 'Please accept the privacy note to subscribe.', 'moon-core' );
		?>
		<section class="moon-widget moon-news moon-news--<?php echo esc_attr( $layout ); ?>"<?php echo $this->reveal_attributes( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="moon-news__inner">
				<?php if ( 'panel' === $layout && $image ) : ?>
					<span class="moon-news__media" aria-hidden="true">
						<?php echo wp_kses_post( Helpers::image( $image, 'moon-editorial', array( 'alt' => '' ) ) ); ?>
					</span>
					<span class="moon-news__scrim" aria-hidden="true"></span>
				<?php endif; ?>

				<div class="moon-news__copy" data-moon-reveal-item>
					<?php
					$this->render_heading(
						array(
							'heading_eyebrow'     => $settings['heading_eyebrow'] ?? '',
							'heading_title'       => $settings['heading_title'] ?? '',
							'heading_description' => $settings['heading_description'] ?? '',
							'heading_title_tag'   => $settings['heading_title_tag'] ?? 'h2',
						)
					);
					?>
					<?php if ( '' !== trim( (string) ( $settings['note'] ?? '' ) ) ) : ?>
						<p class="moon-news__note"><?php echo esc_html( $settings['note'] ); ?></p>
					<?php endif; ?>
				</div>

				<form class="moon-newsletter-form moon-news__form" data-moon-newsletter data-consent-message="<?php echo esc_attr( $consent_message ); ?>" novalidate>
					<label class="screen-reader-text" for="moon-news-email-<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Email address', 'moon-core' ); ?></label>
					<input
						id="moon-news-email-<?php echo esc_attr( $this->get_id() ); ?>"
						type="email"
						name="email"
						required
						autocomplete="email"
						placeholder="<?php echo esc_attr( (string) ( $settings['placeholder'] ?? '' ) ); ?>"
					>
					<input type="text" name="moon_company" class="moon-hp" tabindex="-1" autocomplete="off" aria-hidden="true">

					<?php if ( $consent ) : ?>
						<label class="moon-newsletter-form__consent">
							<input type="checkbox" name="consent" value="1" required>
							<span><?php echo esc_html( (string) ( $settings['consent_text'] ?? '' ) ); ?></span>
						</label>
					<?php endif; ?>

					<button type="submit" class="moon-btn moon-btn--solid moon-news__submit"><?php echo esc_html( (string) ( $settings['button_text'] ?? __( 'Subscribe', 'moon-core' ) ) ); ?></button>
					<p class="moon-newsletter-form__status" data-moon-newsletter-status role="status" aria-live="polite"></p>
				</form>
			</div>
		</section>
		<?php
	}
}
