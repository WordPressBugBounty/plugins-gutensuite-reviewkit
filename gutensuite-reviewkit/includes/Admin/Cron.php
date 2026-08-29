<?php
/**
 * Cron handler class.
 *
 * @package Gutensuite\Reviewkit\Admin
 */

namespace Gutensuite\Reviewkit\Admin;

use Gutensuite\Reviewkit\Traits\Singleton;
use Gutensuite\Reviewkit\Traits\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron handler class.
 */
class Cron {
	use Singleton;
	use Helper;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'gutensuite_reviewkit_sync_reviews_cron', array( $this, 'sync_reviews_cron' ) );
	}

	/**
	 * Sync reviews via cron.
	 */
	public function sync_reviews_cron() {
		$domain = get_option( 'gutensuite_reviewkit_tp_reviews_original_domain', '' );

		if ( empty( $domain ) ) {
			return;
		}

		$original_domain = $domain;
		$domain          = $this->process_domain( $domain );

		$api_url = GUTENSUITE_REVIEWKIT_TP_API . urlencode( $domain ); // phpcs:ignore

		$args = array(
			'timeout'     => 30,
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
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return;
		}

		update_option( 'gutensuite_reviewkit_tp_reviews_original_domain', $original_domain );
		$this->process_reviews( $domain, $data );
	}

	/**
	 * Schedule sync reviews cron job.
	 */
	public function schedule_sync_reviews() {
		if ( ! wp_next_scheduled( 'gutensuite_reviewkit_sync_reviews_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'gutensuite_reviewkit_sync_reviews_cron' );
		}
	}
}
