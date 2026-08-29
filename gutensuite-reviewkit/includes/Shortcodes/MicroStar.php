<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use Gutensuite\Reviewkit\BusinessData;

/**
 * Micro Star shortcode
 */
class MicroStar extends BaseShortcode {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tag = 'reviewkit_tp_micro_star';

		$business_data = BusinessData::get_business_data();
		$trust_score   = $business_data['trustScore'] ?? $business_data['trust_score'] ?? 4.8;

		$this->default_attributes = array(
			'rating'      => BusinessData::get_rating_text( $trust_score ),
			'stars'       => BusinessData::get_rounded_trust_score( $trust_score ),
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
		$rating = $this->default_attributes['rating'];

		$html = '';
		ob_start();
		?>
			<div class="gutensuite-reviewkit_fpln_mcs gutensuite-reviewkit_fpln_common frontend">
				<div class="dynamic_rating"><?php echo esc_html( $rating ); ?></div>
				<div class="dynamic_stars">
					<img src="<?php echo esc_url( $this->default_attributes['star_image'] ); ?>" alt="<?php echo esc_attr( $rating . ' star rating' ); ?>" style="width: 100%;">
				</div>
				<div class="tp-wrapper">
					<img src="<?php echo esc_url( $this->default_attributes['single_star'] ); ?>" alt="Trustpilot Logo">
					<div class="place_name"><?php echo esc_html( 'Trustpilot' ); ?></div>
				</div>
			</div>
		<?php

		$html = ob_get_clean();
		return $html;
	}
}
