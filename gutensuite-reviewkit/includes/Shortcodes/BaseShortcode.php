<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Base class for all Trustpilot shortcodes
 */
abstract class BaseShortcode {

	/**
	 * Shortcode tag name (new format)
	 *
	 * @var string
	 */
	protected $tag;

	/**
	 * Default attributes
	 *
	 * @var array
	 */
	protected $default_attributes = array();

	/**
	 * Register the shortcode
	 */
	public function register() {
		add_shortcode( $this->tag, array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode
	 *
	 * @param array  $atts Shortcode attributes
	 * @param string $content Shortcode content
	 * @return string
	 */
	public function render( $atts = array(), $content = null ) {
		$atts = shortcode_atts( $this->default_attributes, $atts, $this->tag );
		$atts = $this->sanitize_attributes( $atts );
		return $this->generate_html( $atts, $content );
	}

	/**
	 * Generate the HTML output
	 *
	 * @param array  $atts Sanitized attributes
	 * @param string $content Shortcode content
	 * @return string
	 */
	abstract protected function generate_html( $atts, $content );

	/**
	 * Sanitize shortcode attributes
	 *
	 * @param array $atts Raw attributes
	 * @return array Sanitized attributes
	 */
	protected function sanitize_attributes( $atts ) {
		$sanitized = array();

		foreach ( $atts as $key => $value ) {
			switch ( $key ) {
				case 'url':
					$sanitized[ $key ] = esc_url( $value );
					break;
				case 'rating':
					$sanitized[ $key ] = floatval( $value );
					break;
				case 'count':
					$sanitized[ $key ] = intval( $value );
					break;
				default:
					$sanitized[ $key ] = sanitize_text_field( $value );
					break;
			}
		}

		return $sanitized;
	}

	/**
	 * Generate star SVG icon
	 *
	 * @return string
	 */
	protected function get_star_svg() {
		return '<svg class="gutensuite-reviewkit_fpln_star_icon" viewBox="0 0 24 24">
			<path d="M12 2l2.938 7.968h8.382l-6.76 4.898 2.553 7.834L12 17.334l-7.113 5.366 2.553-7.834-6.76-4.898h8.382z" />
		</svg>';
	}

	/**
	 * Generate star rating HTML
	 *
	 * @param int $rating Rating value (1-5)
	 * @return string
	 */
	protected function get_star_rating_html( $rating = 5 ) {
		$html = '<div class="gutensuite-reviewkit_star_rating">';

		for ( $i = 5; $i >= 1; $i-- ) {
			$checked = $i <= $rating ? 'checked' : '';
			$html   .= sprintf(
				'<input type="radio" id="star%d" name="rating" value="%d" %s>
				<label for="star%d">★</label>',
				$i,
				$i,
				$checked,
				$i
			);
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Format number for display
	 *
	 * @param int $number
	 * @return string
	 */
	protected function format_number( $number ) {
		if ( $number >= 1000 ) {
			return number_format( $number / 1000, 1 ) . 'K';
		}
		return number_format( $number );
	}
}
