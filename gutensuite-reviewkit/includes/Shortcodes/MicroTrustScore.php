<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use Gutensuite\Reviewkit\BusinessData;

/**
 * Micro TrustScore shortcode
 */
class MicroTrustScore extends BaseShortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tag = 'reviewkit_tp_micro_trustscore';

		$business_data = BusinessData::get_business_data();

		$this->default_attributes = array(
			'rating' => BusinessData::get_rating_text( $business_data['trustScore'] ?? $business_data['trust_score'] ?? 4.8 ),
			'score'  => $business_data['trustScore'] ?? $business_data['trust_score'] ?? 4.8,
			'url'    => $business_data['trustpilot_url'] ?? '#',
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
		$rating      = $this->default_attributes['rating'];
		$score       = $this->default_attributes['score'];
		$single_star = BusinessData::get_single_star_image_url();

		$html = '<div class="gutensuite-reviewkit_fpln_mcts gutensuite-reviewkit_fpln_common">';

		$html .= '<div class="gutensuite-reviewkit_fpln_inner_left">';
		$html .= '<span class="review_us_one">' . esc_html( $rating ) . '</span>';

		$html .= '<div class="gutensuite-reviewkit_score">';
		$html .= '<span class="gutensuite-reviewkit_orignal_rcount">' . esc_html( $score ) . '</span>';
		$html .= '<span class="gutensuite-reviewkit_out_of"> ' . esc_html__( 'out of', 'gutensuite-reviewkit' ) . ' 5</span>';
		$html .= '</div>';

		$html .= '</div>';

		$html .= '<div class="gutensuite-reviewkit_fpln_inner_right">';
		$html .= '<img src="' . esc_url( $single_star ) . '" alt="Trustpilot logo"  style="width: 20px;" />';
		$html .= '<span class="place_name"> Trustpilot</span>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}
}
