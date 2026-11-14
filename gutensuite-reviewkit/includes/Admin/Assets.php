<?php
namespace Gutensuite\Reviewkit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Assets {
	private $pages = array( 'toplevel_page_gutensuite/reviewkit' );

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_library_assets' ) );
	}

	public function plugin_scripts( $hook ) {
		if ( ! in_array( $hook, $this->pages, true ) ) {
			return;
		}
		remove_all_actions( 'admin_notices' );
		$dependencies = include_once GUTENSUITE_REVIEWKIT_ASSETS_DIR_PATH . 'js/gutensuite-reviewkit.core.min.asset.php';
		wp_enqueue_style( 'gutensuite-reviewkit-admin', GUTENSUITE_REVIEWKIT_ASSETS_URI . 'js/gutensuite-reviewkit.core.min.css', array( 'wp-components' ), $dependencies['version'], 'all' );
		wp_enqueue_script(
			'gutensuite-reviewkit',
			GUTENSUITE_REVIEWKIT_ASSETS_URI . 'js/gutensuite-reviewkit.core.min.js',
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		wp_set_script_translations('gutensuite-reviewkit', 'gutensuite-reviewkit', GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH . 'languages');

		wp_enqueue_style(
			'gutensuite-reviewkit-shortcodes',
			GUTENSUITE_REVIEWKIT_ASSETS_URI . 'js/gutensuite-shortcode/index.min.css',
			array(),
			$dependencies['version'],
		);

		$cache_data = $this->get_cache_data();

		wp_localize_script(
			'gutensuite-reviewkit',
			'gutensuite_reviewkit',
			array(
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'gutensuite_reviewkit_nonce' ),
				'plugin_root_url' => GUTENSUITE_REVIEWKIT_PLUGIN_ROOT_URI,
				'data'            => $cache_data['data'] ?? array(),
				'count'           => $cache_data['count'] ?? 0,
				'last_updated'    => $cache_data['last_updated'] ?? null,
				'original_domain' => $cache_data['original_domain'] ?? '',
				'domain'          => $cache_data['domain'] ?? '',
				'tp_api'          => GUTENSUITE_REVIEWKIT_TP_API,
				'is_pro_enabled'  => apply_filters('gutensuite/reviewkit/pro_enabled', false),
				'global_settings' => apply_filters('gutensuite/reviewkit/settings', array() ),
			)
		);
	}

	/**
	 * Enqueue frontend assets for shortcodes
	 */
	public function enqueue_frontend_assets() {
		// if ( ! \Gutensuite\Reviewkit\Shortcodes::has_shortcodes_on_page() ) {
		// 	return;
		// }

		if ( ! file_exists( GUTENSUITE_REVIEWKIT_ASSETS_DIR_PATH . 'js/gutensuite-shortcode/index.min.css' ) ) {
			return;
		}

		wp_register_style(
			'gutensuite-reviewkit-shortcodes',
			GUTENSUITE_REVIEWKIT_ASSETS_URI . 'js/gutensuite-shortcode/index.min.css',
			array(),
			GUTENSUITE_REVIEWKIT_VERSION
		);
	}

	public function enqueue_library_assets() {
		wp_register_style( 'gutensuite-reviewkit-swiper', GUTENSUITE_REVIEWKIT_ASSETS_URI . 'lib/swiper.min.css', array(), GUTENSUITE_REVIEWKIT_VERSION );
		wp_register_script( 'gutensuite-reviewkit-swiper', GUTENSUITE_REVIEWKIT_ASSETS_URI . 'lib/swiper.min.js', array(), GUTENSUITE_REVIEWKIT_VERSION, true );
		wp_register_script( 'gutensuite-reviewkit-review-carousel', GUTENSUITE_REVIEWKIT_ASSETS_URI . 'lib/review-carousel.js', array( 'jquery', 'gutensuite-reviewkit-swiper' ), GUTENSUITE_REVIEWKIT_VERSION, true );

		wp_register_style( 'gutensuite-reviewkit-pagination', GUTENSUITE_REVIEWKIT_ASSETS_URI . 'js/gutensuite-shortcode/index.min.css', array(), time() );
		wp_register_script( 'gutensuite-reviewkit-pagination', GUTENSUITE_REVIEWKIT_ASSETS_URI . 'lib/review-grid-pagination.js', array( 'jquery' ), time(), true );
		
		wp_localize_script(
			'gutensuite-reviewkit-pagination',
			'gutensuite_reviewkit_pagination',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gutensuite_reviewkit_pagination_nonce' ),
			)
		);
	}


	public function get_cache_data() {
		$data_map = array(
			'original_domain' => 'gutensuite_reviewkit_tp_reviews_original_domain',
			'domain'          => 'gutensuite_reviewkit_tp_reviews_domain',
			'data'            => 'gutensuite_reviewkit_tp_reviews_data',
			'misc_data'       => 'gutensuite_reviewkit_tp_reviews_misc_data',
			'business_data'   => 'gutensuite_reviewkit_tp_reviews_business_data',
			'count'           => 'gutensuite_reviewkit_tp_reviews_count',
			'last_updated'    => 'gutensuite_reviewkit_tp_reviews_last_updated',
		);

		$results = array();
		foreach ( $data_map as $key => $option_name ) {
			$option_value = get_option( $option_name, null );
			if ( $option_value !== null ) {
				$results[] = array(
					'option_name'  => $option_name,
					'option_value' => $option_value,
				);
			}
		}

		$data = array();

		foreach ( $results as $result ) {
			foreach ( $data_map as $key => $prefix ) {
				if ( substr( $result['option_name'], 0, strlen( $prefix ) ) === $prefix ) {
					if ( $key === 'data' || $key === 'misc_data' || $key === 'business_data' ) {
						$unserialize_data = maybe_unserialize( $result['option_value'] );
						$data[ $key ]     = $unserialize_data;
					} else {
						$data[ $key ] = $result['option_value'];
					}
				}
			}
		}

		if ( isset( $data['misc_data'], $data['business_data'], $data['data'] ) ) {
			$complete_data                     = $data['misc_data'];
			$complete_data['business_details'] = $data['business_data'];
			$complete_data['reviews']          = $data['data'];
			$data['data']                      = $complete_data;
		}

		return $data;
	}
}
