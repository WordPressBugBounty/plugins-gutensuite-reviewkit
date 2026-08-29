<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use Gutensuite\Reviewkit\BusinessData;


/**
 * Micro Combo shortcode
 */
class MicroCombo extends BaseShortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tag = 'reviewkit_tp_micro_combo';

		$business_data = BusinessData::get_business_data();
		$trust_score   = $business_data['trustScore'] ?? $business_data['trust_score'] ?? 4.8;

		$this->default_attributes = array(
			'rating'      => BusinessData::get_rating_text( $trust_score ),
			'stars'       => BusinessData::get_rounded_trust_score( $trust_score ),
			'reviews'     => $business_data['numberOfReviews'] ?? $business_data['review_count'] ?? 347,
			'url'         => $business_data['trustpilot_url'] ?? '#',
			'star_image'  => BusinessData::get_star_image_url( $trust_score ),
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
		$rating      = $this->default_attributes['rating'];
		$reviews     = $this->default_attributes['reviews'];
		$single_star = BusinessData::get_single_star_image_url();

		$rating_text = $this->default_attributes['rating'];

		$html = '<div class="gutensuite-reviewkit_fpln_mc gutensuite-reviewkit_fpln_common frontend">';

		$html .= '<div class="gutensuite-reviewkit_fpln_mc_inner_left">';
		$html .= '<span class="review_us_one">' . esc_html( $rating ) . '</span>';
		$html .= '<div class="gutensuite-reviewkit_star_rating">';
		$html .= '<img src="' . esc_url( $this->default_attributes['star_image'] ) . '" alt="' . esc_attr( $rating_text . ' rating' ) . '">';
		$html .= '</div>';
		$html .= '</div>';

		$html           .= '<div class="gutensuite-reviewkit_fpln_mc_inner_right">';
		$formatted_count = BusinessData::format_review_count( $reviews );
		$html           .= '<span class="mirc_r_count">' . esc_html( $formatted_count ) . '</span>';
		$html           .= '<span class="review_us_one">reviews on</span>';
		$html           .= '<img src="' . esc_url( $single_star ) . '" alt="Trustpilot logo"  style="width: 20px;" />';
		$html           .= '<span class="place_name">Trustpilot</span>';
		$html           .= '</div>';

		$html .= '</div>';

		return $html;
	}
}
