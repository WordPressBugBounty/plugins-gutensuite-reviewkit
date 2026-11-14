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
		add_action( 'wp_ajax_gutensuite_reviewkit_save_settings', array( $this, 'save_settings' ) );
		
		add_action( 'wp_ajax_gutensuite_reviewkit_load_more_reviews', array( $this, 'load_more_reviews' ) );
		add_action( 'wp_ajax_nopriv_gutensuite_reviewkit_load_more_reviews', array( $this, 'load_more_reviews' ) );
		add_action( 'wp_ajax_gutensuite_reviewkit_load_page_reviews', array( $this, 'load_page_reviews' ) );
		add_action( 'wp_ajax_nopriv_gutensuite_reviewkit_load_page_reviews', array( $this, 'load_page_reviews' ) );
	}

	/**
	 * Save settings.
	 */
	public function save_settings() {
		check_ajax_referer( 'gutensuite_reviewkit_nonce', 'security' );

		$settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array(); // phpcs:ignore
		$settings = map_deep( $settings, 'sanitize_text_field' );
		$settings = json_decode( $settings, true );

		update_option( 'gutensuite_reviewkit_global_settings', $settings );

		wp_send_json(
			array(
				'success' => true,
				'message' => __( 'Settings saved successfully', 'gutensuite-reviewkit' ),
			)
		);
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

		error_log( apply_filters('gutensuite/reviewkit/user_agent', 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )) );

		$args = array(
			'timeout'     => 30,
			'sslverify'   => false,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => apply_filters('gutensuite/reviewkit/user_agent', 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )),
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

	/**
	 * Load more reviews via AJAX.
	 */
	public function load_more_reviews() {
		check_ajax_referer( 'gutensuite_reviewkit_pagination_nonce', 'nonce' );

		$page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 6;
		$min_star = isset( $_POST['min_star'] ) ? absint( $_POST['min_star'] ) : 1;
		$sort  = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : 'date_newest';
		$style    = isset( $_POST['style'] ) ? sanitize_text_field( $_POST['style'] ) : 'grid';

		$review_data = get_option( 'gutensuite_reviewkit_tp_reviews_data', array() );
		$review_data = gtp_get_sorted_reviews( $review_data, $sort );

		$filtered_reviews = array_filter(
			$review_data,
			function( $review ) use ( $min_star ) {
				return (int) $review['rating'] >= $min_star;
			}
		);

		$filtered_reviews = array_values( $filtered_reviews );
		$total_reviews    = count( $filtered_reviews );
		$offset           = ( $page - 1 ) * $per_page;
		$paged_reviews    = array_slice( $filtered_reviews, $offset, $per_page );
		$atts = apply_filters('gutensuite_reviewkit/tp_review_atts', $_POST);
		$card_path = apply_filters('gutensuite/reviewkit/pro_root_dir_path', GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH) . 'includes/templates/cards/card-1.php';
		
		ob_start();
		foreach ( $paged_reviews as $key => $review ) {
			$review      = (object) $review;
			$time_string = strtotime( $review->dates['experiencedDate'] );
			$times_ago   = gtp_times_ago( $time_string );
			include $card_path;
		}
		$html = ob_get_clean();

		$has_more = ( $offset + $per_page ) < $total_reviews;

		wp_send_json_success(
			array(
				'html'     => $html,
				'has_more' => $has_more,
				'page'     => $page,
			)
		);
	}

	/**
	 * Load specific page of reviews via AJAX.
	 */
	public function load_page_reviews() {
		check_ajax_referer( 'gutensuite_reviewkit_pagination_nonce', 'nonce' );

		$page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 6;
		$min_star = isset( $_POST['min_star'] ) ? absint( $_POST['min_star'] ) : 1;
		$sort  = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : 'date_newest';
		$style    = isset( $_POST['style'] ) ? sanitize_text_field( $_POST['style'] ) : 'grid';

		$review_data = get_option( 'gutensuite_reviewkit_tp_reviews_data', array() );
		$review_data = gtp_get_sorted_reviews( $review_data, $sort );
		$atts = apply_filters('gutensuite_reviewkit/tp_review_atts', $_POST);
		$card_path = apply_filters('gutensuite/reviewkit/pro_root_dir_path', GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH) . 'includes/templates/cards/card-1.php';

		$filtered_reviews = array_filter(
			$review_data,
			function( $review ) use ( $min_star ) {
				return (int) $review['rating'] >= $min_star;
			}
		);

		$filtered_reviews = array_values( $filtered_reviews );
		$total_reviews    = count( $filtered_reviews );
		$total_pages      = ceil( $total_reviews / $per_page );
		$offset           = ( $page - 1 ) * $per_page;
		$paged_reviews    = array_slice( $filtered_reviews, $offset, $per_page );

		ob_start();
		foreach ( $paged_reviews as $key => $review ) {
			$review      = (object) $review;
			$time_string = strtotime( $review->dates['experiencedDate'] );
			$times_ago   = gtp_times_ago( $time_string );
			include $card_path;
		}
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'        => $html,
				'page'        => $page,
				'total_pages' => $total_pages,
			)
		);
	}
}
