<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use Gutensuite\Reviewkit\BusinessData;

/**
 * Mini Trustbox shortcode
 */
class MiniTrustbox extends BaseShortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tag = 'reviewkit_tp_mini';

		// Get dynamic business data
		$business_data = BusinessData::get_business_data();

		$this->default_attributes = array(
			'trustscore' => $business_data['trust_score'],
			'reviews'    => $business_data['review_count'],
			'url'        => $business_data['trustpilot_url'],
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
		$business_data = new BusinessData();

		// Use safe array access with fallbacks
		$trustscore  = $business_data->profile['trustScore'] ?? $business_data->profile['trust_score'] ?? 4.8;
		$reviews     = $business_data->profile['numberOfReviews'] ?? $business_data->profile['review_count'] ?? 347;
		$url         = $business_data->profile['websiteUrl'] ?? $business_data->profile['website_url'] ?? '#';
		$single_star = BusinessData::get_single_star_image_url();

		// Debug: Add a simple test output first
		$html  = '<!-- Trustpilot Mini Shortcode Debug: Working -->';
		$html .= '<div class="gutensuite-reviewkit_fpln_mini">';

		// Top section with Trustpilot logo
		$html .= '<div class="gutensuite-reviewkit_fpln_inner_top">';
		$html .= '<img src="' . esc_url( $single_star ) . '" alt="Trustpilot logo" />';
		$html .= '<span class="place_name">Trustpilot</span>';
		$html .= '</div>';

		// Star rating image - dynamic based on trust score
		$html          .= '<div class="gutensuite-reviewkit_bg">';
		$star_image_url = BusinessData::get_star_image_url( $trustscore );
		$rating_text    = BusinessData::get_rating_text( $trustscore );
		$html          .= '<img src="' . esc_url( $star_image_url ) . '" alt="' . esc_attr( $rating_text . ' rating' ) . '">';
		$html          .= '</div>';

		// Bottom section with score and reviews
		$html .= '<div class="gutensuite-reviewkit_fpln_inner_bottom">';

		// Left side - TrustScore
		$html .= '<div class="gutensuite-reviewkit_left_reviews">';
		$html .= '<span class="review_us_one">TrustScore </span>';
		$html .= '<span class="gutensuite-reviewkit_orignal_rcount">' . esc_html( $trustscore ) . '</span>';
		$html .= '</div>';

		// Right side - Review count
		$html           .= '<div class="gutensuite-reviewkit_review_area">';
		$formatted_count = BusinessData::format_review_count( $reviews );
		$html           .= '<span class="gutensuite-reviewkit_out_of">' . esc_html( $formatted_count ) . '</span>';
		$html           .= '<span class="gutensuite-reviewkit_reviews"> reviews</span>';
		$html           .= '</div>';

		$html .= '</div>'; // Close inner_bottom
		$html .= '</div>'; // Close main container

		return $html;
	}
}
