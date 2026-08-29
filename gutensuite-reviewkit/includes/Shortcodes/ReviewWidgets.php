<?php
namespace Gutensuite\Reviewkit\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ReviewWidgets {
	public function __construct() {
		add_shortcode( 'reviewkit_review', array( $this, 'render' ) );
	}

	public function render( $atts, $content ) {
		$review_data = get_option( 'gutensuite_reviewkit_tp_reviews_data', array() );
		$b_data      = (object) gtp_get_business_data();

		if ( in_array( $atts['style'] ?? 'grid', array( 'grid', 'list' ) ) ) {
			return $this->render_review_grid(
				array(
					'review_data' => $review_data,
					'b_data'      => $b_data,
					'atts'        => $atts,
				)
			);
		}

		if ( $atts['style'] === 'carousel' ) {
			return $this->render_review_carousel(
				array(
					'review_data' => $review_data,
					'b_data'      => $b_data,
					'atts'        => $atts,
				)
			);
		}

		return '';
	}

	public function render_review_grid( $args = array() ) {
		$review_data = $args['review_data'] ?? array();
		$b_data      = $args['b_data'] ?? array();
		$atts        = $args['atts'] ?? array();
		ob_start();
		include GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH . 'includes/templates/tp-review-grid.php';
		return ob_get_clean();
	}

	public function render_review_carousel( $args = array() ) {
		$review_data = $args['review_data'] ?? array();
		$b_data      = $args['b_data'] ?? array();
		$atts        = $args['atts'] ?? array();

		wp_enqueue_style( 'gutensuite-reviewkit-swiper' );
		wp_enqueue_script( 'gutensuite-reviewkit-review-carousel' );
		ob_start();
		include GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH . 'includes/templates/tp-review-carousel.php';
		return ob_get_clean();
	}
}
