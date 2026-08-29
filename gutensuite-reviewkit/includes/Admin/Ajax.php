<?php
/**
 * AJAX handler class.
 *
 * @package Gutensuite\Reviewkit\Admin
 */

namespace Gutensuite\Reviewkit\Admin;

use Gutensuite\Reviewkit\Traits\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler class.
 */
class Ajax {
	use Helper;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_gutensuite_reviewkit_get_reviews', array( $this, 'get_reviews' ) );
		add_action( 'wp_ajax_gutensuite_reviewkit_delete_cache', array( $this, 'delete_cache' ) );
	}

	/**
	 * Get reviews from API.
	 */
	public function get_reviews() {
		check_ajax_referer( 'gutensuite_reviewkit_nonce', 'security' );

		$domain     = isset( $_POST['domain'] ) ? sanitize_text_field( $_POST['domain'] ) : ''; // phpcs:ignore

		if ( empty( $domain ) ) {
			wp_send_json_error( array( 'message' => 'Domain is required' ), 400 );
		}

		$original_domain = $domain;
		$domain          = $this->process_domain( $domain );

		$previous_original_domain = $this->get_previous_domain();

		if ( $original_domain !== $previous_original_domain ) {
			$this->delete_cache_data();
		}

		$api_url = GUTENSUITE_REVIEWKIT_TP_API . urlencode( $domain ); // phpcs:ignore

		$args = array(
			'timeout'     => 30,
			'sslverify'   => false,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			'headers'     => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			),
		);

		$response = wp_remote_get( $api_url, $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => 'Failed to fetch reviews: ' . $response->get_error_message(),
				),
				500
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			wp_send_json_error(
				array(
					'message'  => 'API returned error code: ' . $response_code,
					'response' => wp_remote_retrieve_body( $response ),
				),
				$response_code
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error(
				array(
					'message' => 'Failed to parse API response: ' . json_last_error_msg(),
				),
				500
			);
		}

		update_option( 'gutensuite_reviewkit_tp_reviews_original_domain', $original_domain );
		$updated_at = $this->process_reviews( $domain, $data );

		$cron = \Gutensuite\Reviewkit\Admin\Cron::instance();
		$cron->schedule_sync_reviews();

		wp_send_json_success(
			array(
				'data'         => $data,
				'last_updated' => $updated_at,
			)
		);
	}

	/**
	 * Delete cache data.
	 */
	public function delete_cache() {
		check_ajax_referer( 'gutensuite_reviewkit_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not authorized to delete the cache', 'gutensuite-reviewkit' ) ), 403 );
		}

		$this->delete_cache_data();

		wp_send_json_success( array( 'message' => __( 'Cache deleted successfully', 'gutensuite-reviewkit' ) ) );
	}
}
