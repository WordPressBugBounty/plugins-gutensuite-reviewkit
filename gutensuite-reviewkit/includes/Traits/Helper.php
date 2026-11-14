<?php
/**
 * Helper trait.
 *
 * @package Gutensuite\Reviewkit\Traits
 */

namespace Gutensuite\Reviewkit\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Helper trait.
 */
trait Helper {
	/**
	 * Process domain string.
	 *
	 * @param string $domain Domain string.
	 * @return string Processed domain.
	 */
	public function process_domain( $domain ) {
		if ( preg_match( '/^(?:https?:\/\/)?(?:[a-z0-9\-]+\.)*trustpilot\.com\/review\/([^\/\s]+)/i', $domain, $matches ) ) {
			$domain = $matches[1];
		}

		$domain = preg_replace( '/^(https?:\/\/)?(www\.)?/', '', $domain );
		$domain = rtrim( $domain, '/' );

		return $domain;
	}

	/**
	 * Process reviews data.
	 *
	 * @param string $domain Domain string.
	 * @param array  $data   Reviews data.
	 * @return int|false Timestamp of update or false on failure.
	 */
	public function process_reviews( $domain, $data ) {
		if ( empty( $data ) ) {
			return;
		}

		$business_details = $data['business_details'];
		update_option( 'gutensuite_reviewkit_tp_reviews_business_data', $business_details );

		$reviews = isset( $data['reviews'] ) ? $data['reviews'] : array();
		update_option( 'gutensuite_reviewkit_tp_reviews_data', $reviews );

		$misc_data = $data;
		unset( $misc_data['business_details'] );
		unset( $misc_data['reviews'] );
		update_option( 'gutensuite_reviewkit_tp_reviews_misc_data', $misc_data );

		update_option( 'gutensuite_reviewkit_tp_reviews_domain', $domain );
		update_option( 'gutensuite_reviewkit_tp_reviews_count', count( $reviews ) );
		$time = time();
		update_option( 'gutensuite_reviewkit_tp_reviews_last_updated', $time );

		return $time;
	}

	/**
	 * Get previous domain.
	 *
	 * @return string Previous domain.
	 */
	public function get_previous_domain() {
		return get_option( 'gutensuite_reviewkit_tp_reviews_original_domain', '' );
	}

	/**
	 * Get cached reviews data.
	 *
	 * @return array|false Cached data or false if not available.
	 */
	public function get_cache() {
		if ( empty( get_option( 'gutensuite_reviewkit_tp_reviews_last_updated', null ) ) ) {
			return false;
		}

		$business_details = get_option( 'gutensuite_reviewkit_tp_reviews_business_data', array() );
		$reviews          = get_option( 'gutensuite_reviewkit_tp_reviews_data', array() );
		$misc_data        = get_option( 'gutensuite_reviewkit_tp_reviews_misc_data', array() );

		$complete_data                     = $misc_data;
		$complete_data['business_details'] = $business_details;
		$complete_data['reviews']          = $reviews;

		return array(
			'data'         => $complete_data,
			'last_updated' => get_option( 'gutensuite_reviewkit_tp_reviews_last_updated', null ),
		);
	}

	/**
	 * Delete cache data.
	 */
	public function delete_cache_data() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'gutensuite_reviewkit_tp_%' ) );
		wp_cache_flush();
	}
}
