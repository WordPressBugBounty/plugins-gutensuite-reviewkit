<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use Gutensuite\Reviewkit\BusinessData;

/**
 * Micro Button shortcode
 */
class MicroButton extends BaseShortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tag                = 'reviewkit_tp_micro_button';
		$business_data            = BusinessData::get_business_data();
		$this->default_attributes = array(
			'reviews' => BusinessData::format_review_count( $business_data['numberOfReviews'] ?? $business_data['review_count'] ?? 347 ),
			'url'     => $business_data['trustpilot_url'] ?? '#',
		);
	}

	/**
	 * Generate the HTML output
	 *
	 * @param array  $atts Sanitized attributes
	 * @param string $content Shortcode content
	 * @return string
	 */
	protected function generate_html( $atts, $content ) {
		$reviews = $this->default_attributes['reviews'];
		$url     = $this->default_attributes['url'];

		$html = '<div class="gutensuite-reviewkit_fpln_mcb_wrap">';

		$light_star = GUTENSUITE_REVIEWKIT_ASSETS_URI . 'images/light-single-star.png';

		$html .= '<a class="gutensuite-reviewkit_fpln_mcb_left" href="' . esc_url( $url ) . '">';
		$html .= '<img src="' . esc_url( $light_star ) . '" alt="Trustpilot logo"  style="width: 20px;" />';
		$html .= '<span class="place_name">Trustpilot</span>';
		$html .= '</a>';

		$html .= '<div class="gutensuite-reviewkit_fpln_mcb_right">';
		$html .= esc_html( $reviews ) . ' reviews';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}
}
