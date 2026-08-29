<?php
namespace Gutensuite\Reviewkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Business Data Manager
 * Handles storage and retrieval of business profile data from wp_options
 */
class BusinessData {

	const OPTION_KEY = 'gutensuite_reviewkit_tp_reviews_business_data'; // gutensuite_reviewkit_tp_reviews_data

	private static $default_data = array(
		'business_name'     => 'Your Business',
		'trust_score'       => 4.8,
		'review_count'      => 347,
		'website_url'       => '#',
		'trustpilot_url'    => '#',
		'business_category' => 'Technology',
		'is_verified'       => true,
		'is_claimed'        => true,
		'last_updated'      => '',
		// API response keys for compatibility
		'identifyingName'   => 'example.com',
		'trustScore'        => 4.8,
		'numberOfReviews'   => 347,
		'websiteUrl'        => '#',
		'displayName'       => 'Your Business',
	);

	public $profile = array();


	public function __construct() {
		$this->profile = self::get_business_data();
	}

	/**
	 * Get business data from wp_options
	 *
	 * @return array Business data
	 */
	public static function get_business_data() {
		global $gtp_business_data;
		if ( empty( $gtp_business_data ) ) {
			$gtp_business_data = get_option( self::OPTION_KEY, self::$default_data );
		}
		$data = $gtp_business_data;

		$data = wp_parse_args( $data, self::$default_data );

		$data['trust_score'] = max( 1.0, min( 5.0, floatval( $data['trust_score'] ) ) );

		$data['review_count'] = max( 0, intval( $data['review_count'] ) );

		if ( isset( $data['identifyingName'] ) && ! empty( $data['identifyingName'] ) ) {
			$data['trustpilot_url'] = 'https://www.trustpilot.com/review/' . $data['identifyingName'];
		} else {
			$data['trustpilot_url'] = '#';
		}

		return $data;
	}

	/**
	 * Update business data in wp_options
	 *
	 * @param array $data Business data to update
	 * @return bool Success status
	 */
	public static function update_business_data( $data ) {
		$current_data = self::get_business_data();
		$updated_data = wp_parse_args( $data, $current_data );

		$updated_data['last_updated'] = current_time( 'mysql' );

		$updated_data = self::sanitize_business_data( $updated_data );

		return update_option( self::OPTION_KEY, $updated_data );
	}

	/**
	 * Sanitize business data
	 *
	 * @param array $data Raw business data
	 * @return array Sanitized business data
	 */
	private static function sanitize_business_data( $data ) {
		return array(
			'business_name'     => sanitize_text_field( $data['business_name'] ?? '' ),
			'trust_score'       => max( 1.0, min( 5.0, floatval( $data['trust_score'] ?? 4.8 ) ) ),
			'review_count'      => max( 0, intval( $data['review_count'] ?? 0 ) ),
			'website_url'       => esc_url_raw( $data['website_url'] ?? '#' ),
			'trustpilot_url'    => esc_url_raw( $data['trustpilot_url'] ?? '#' ),
			'business_category' => sanitize_text_field( $data['business_category'] ?? '' ),
			'is_verified'       => (bool) ( $data['is_verified'] ?? false ),
			'is_claimed'        => (bool) ( $data['is_claimed'] ?? false ),
			'last_updated'      => sanitize_text_field( $data['last_updated'] ?? '' ),
		);
	}

	/**
	 * Get formatted review count for display
	 *
	 * @param int $count Review count
	 * @return string Formatted count (e.g., "1.2K", "347")
	 */
	public static function format_review_count( $count = null ) {
		if ( $count === null ) {
			$data  = self::get_business_data();
			$count = $data['numberOfReviews'] ?? $data['review_count'] ?? 347;
		}
		$count = (int) $count;

		if ( $count >= 1000000 ) {
			return number_format( $count / 1000000, 1 ) . 'M';
		} elseif ( $count >= 1000 ) {
			return number_format( $count / 1000, 1 ) . 'K';
		}

		return number_format( $count );
	}

	/**
	 * Get trust score rounded to nearest 0.5 for image selection
	 *
	 * @param float $score Trust score
	 * @return float Rounded score
	 */
	public static function get_rounded_trust_score( $score = null ) {
		if ( $score === null ) {
			$data  = self::get_business_data();
			$score = $data['trust_score'];
		}

		return round( $score * 2 ) / 2;
	}

	/**
	 * Get star rating image filename based on trust score
	 *
	 * @param float $score Trust score
	 * @return string Image filename
	 */
	public static function get_star_image_filename( $score = null ) {
		$rounded_score = self::get_rounded_trust_score( $score );

		$filename = number_format( $rounded_score, 1 );

		return "stars-{$filename}.svg";
	}

	/**
	 * Get star rating image URL
	 *
	 * @param float $score Trust score
	 * @return string Image URL
	 */
	public static function get_star_image_url( $score = null ) {
		$filename = self::get_star_image_filename( $score );
		return plugin_dir_url( __DIR__ ) . "assets/images/{$filename}";
	}

	/**
	 * Get single star image URL
	 *
	 * @param float $score Trust score
	 * @return string Image URL
	 */
	public static function get_single_star_image_url() {
		return plugin_dir_url( __DIR__ ) . 'assets/images/single-star.svg';
	}

	/**
	 * Get rating text based on trust score
	 *
	 * @param float $score Trust score
	 * @return string Rating text
	 */
	public static function get_rating_text( $score = null ) {
		if ( $score === null ) {
			$data  = self::get_business_data();
			$score = $data['trustScore'] ?? $data['trust_score'] ?? 4.8;
		}
		if ( $score >= 4.5 ) {
			return esc_html__( 'Excellent', 'gutensuite-reviewkit' );
		} elseif ( $score >= 4.0 ) {
			return esc_html__( 'Great', 'gutensuite-reviewkit' );
		} elseif ( $score >= 3.0 ) {
			return esc_html__( 'Good', 'gutensuite-reviewkit' );
		} elseif ( $score >= 2.0 ) {
			return esc_html__( 'Average', 'gutensuite-reviewkit' );
		} else {
			return esc_html__( 'Poor', 'gutensuite-reviewkit' );
		}
	}
}
