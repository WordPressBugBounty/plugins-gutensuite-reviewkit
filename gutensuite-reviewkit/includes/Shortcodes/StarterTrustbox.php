<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use Gutensuite\Reviewkit\BusinessData;

/**
 * Starter Trustbox shortcode
 */
class StarterTrustbox extends BaseShortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tag = 'reviewkit_tp_starter';

		// Get dynamic business data
		$business_data = BusinessData::get_business_data();

		$this->default_attributes = array(
			'reviews' => $business_data['review_count'],
			'url'     => $business_data['trustpilot_url'],
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
		$trustscore       = $business_data->profile['trustScore'] ?? $business_data->profile['trust_score'] ?? 4.8;
		$reviews          = $business_data->profile['numberOfReviews'] ?? $business_data->profile['review_count'] ?? 347;
		$identifying_name = $business_data->profile['identifyingName'] ?? 'example.com';

		$review_url = 'https://www.trustpilot.com/evaluate/' . $identifying_name;

		$single_star = $business_data::get_single_star_image_url();

		$html = '<div class="gutensuite-reviewkit_fpln_starter">';

		// Top section with review count
		$html           .= '<div class="gutensuite-reviewkit_fpln_inner_top">';
		$html           .= '<span class="mirc_see">Check out our </span>';
		$formatted_count = $business_data::format_review_count( $reviews );
		$html           .= '<span class="mirc_r_count">' . esc_html( $formatted_count ) . '</span>';
		$html           .= '<span class="review_us_one"> reviews</span>';
		$html           .= '</div>';

		// Star rating image - dynamic based on business trust score
		$business_data  = $business_data::get_business_data();
		$html          .= '<div class="gutensuite-reviewkit_bg">';
		$star_image_url = BusinessData::get_star_image_url( $trustscore );
		$rating_text    = BusinessData::get_rating_text( $trustscore );
		$html          .= '<img src="' . esc_url( $star_image_url ) . '" alt="' . esc_attr( $rating_text . ' rating' ) . '">';
		$html          .= '</div>';

		// Tooltip section
		$html .= '<div class="gutensuite-reviewkit_toltip_wrap">';
		$html .= '<div class="gutensuite-reviewkit_logo_container">';

		// Tooltip
		$html .= '<div class="gutensuite-reviewkit_tooltip gutensuite-reviewkit_tooltip_large">';
		$html .= '<div class="gutensuite-reviewkit_tooltip_content">Helping each other make better choices</div>';
		$html .= '<a href="' . esc_url( $review_url ) . '" target="_blank" rel="noopener noreferrer" class="gutensuite-reviewkit_tooltip_link">Read and write reviews</a>';
		$html .= '</div>';

		// Star icon and text
		// $html .= '<div class="gutensuite-reviewkit_star"></div>';
		$html .= '<img src="' . esc_url( $single_star ) . '" alt="Trustpilot logo"  style="width: 25px;" />';
		$html .= '<span class="gutensuite-reviewkit_text">Trustpilot</span>';

		$html .= '</div>'; // Close logo_container
		$html .= '</div>'; // Close tooltip_wrap
		$html .= '</div>'; // Close main container

		return $html;
	}
}
