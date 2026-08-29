<?php

if ( ! function_exists( 'gtp_get_business_data' ) ) {
	function gtp_get_business_data() {

		global $gtp_business_data;
		$default_data = array(
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
		if ( empty( $gtp_business_data ) ) {
			$gtp_business_data = get_option( 'gutensuite_reviewkit_tp_reviews_business_data', array() );
		}
		$data = $gtp_business_data;

		// Ensure all required keys exist
		$data = wp_parse_args( $data, $default_data );

		$data['trust_score'] = max( 1.0, min( 5.0, floatval( $data['trust_score'] ) ) );

		// Ensure review_count is positive integer
		$data['review_count'] = max( 0, intval( $data['review_count'] ) );

		// Build trustpilot_url only if identifyingName exists
		if ( isset( $data['identifyingName'] ) && ! empty( $data['identifyingName'] ) ) {
			$data['trustpilot_url'] = 'https://www.trustpilot.com/review/' . $data['identifyingName'];
		} else {
			$data['trustpilot_url'] = '#';
		}

		return $data;
	}
}
if ( ! function_exists( 'gtp_get_rounded_trust_score' ) ) {
	function gtp_get_rounded_trust_score( $score = null ) {
		if ( $score === null ) {
			$data  = gtp_get_business_data();
			$score = $data['trust_score'];
		}

		// Round to nearest 0.5 (Trustpilot standard)
		return round( $score * 2 ) / 2;
	}
}

if ( ! function_exists( 'gtp_get_star_image_filename' ) ) {
	function gtp_get_star_image_filename( $score = null ) {
		$rounded_score = gtp_get_rounded_trust_score( $score );

		$filename = number_format( $rounded_score, 1 );

		return "stars-{$filename}.svg";
	}
}
if ( ! function_exists( 'gtp_get_star_image_url' ) ) {
	function gtp_get_star_image_url( $score = null ) {
		$filename = gtp_get_star_image_filename( $score );
		return plugin_dir_url( __DIR__ ) . "assets/images/{$filename}";
	}
}


if ( ! function_exists( 'gtp_get_single_star_image_url' ) ) {
	function gtp_get_single_star_image_url() {
		return plugin_dir_url( __DIR__ ) . 'assets/images/single-star.svg';
	}
}

if ( ! function_exists( 'gtp_get_rating_text' ) ) {
	function gtp_get_rating_text( $score = null ) {
		if ( $score === null ) {
			$data  = gtp_get_business_data();
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

if ( ! function_exists( 'gtp_times_ago' ) ) {
	function gtp_times_ago( $timestamp ) {
		$time_difference = time() - $timestamp;

		if ( $time_difference < 1 ) {
			return 'less than 1 second ago';
		}

		$conditions = array(
			12 * 30 * 24 * 60 * 60 => 'year',
			30 * 24 * 60 * 60      => 'month',
			24 * 60 * 60           => 'day',
			60 * 60                => 'hour',
			60                     => 'minute',
			1                      => 'second',
		);

		foreach ( $conditions as $secs => $str ) {
			$d = $time_difference / $secs;

			if ( $d >= 1 ) {
				$t = round( $d );
				return 'about ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
			}
		}
	}
}
