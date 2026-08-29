<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use Gutensuite\Reviewkit\BusinessData;

/**
 * Micro Review Count shortcode
 */
class MicroReviewCount extends BaseShortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tag = 'reviewkit_tp_micro_reviewcount';

		$business_data = BusinessData::get_business_data();

		$this->default_attributes = array(
			'count'       => $business_data['numberOfReviews'] ?? $business_data['review_count'] ?? 347,
			'url'         => $business_data['trustpilot_url'] ?? '#',
			'single_star' => BusinessData::get_single_star_image_url(),
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
		$count = $atts['count'];
		$url   = $atts['url'];

		$html  = '<div>';
		$html .= '<a class="gutensuite-reviewkit_fpln_mirc gutensuite-reviewkit_fpln_common" target="_blank" rel="noopener noreferrer" href="' . esc_url( $url ) . '">';

		$html           .= '<span class="mirc_see">See our</span>';
		$formatted_count = BusinessData::format_review_count( $count );
		$html           .= '<span class="mirc_r_count">' . esc_html( $formatted_count ) . '</span>';
		$html           .= '<span class="review_us_one">Reviews us on</span>';
		$html           .= '<img src="' . esc_url( $this->default_attributes['single_star'] ) . '" alt="Trustpilot logo"  style="width: 20px;" />';
		$html           .= '<span class="place_name">Trustpilot</span>';

		$html .= '</a>';
		$html .= '</div>';

		return $html;
	}
}
