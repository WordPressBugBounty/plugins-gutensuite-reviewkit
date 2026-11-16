<?php
/**
 * Plugin Name:       Reviewkit
 * Plugin URI:        https://reviewkit.gutensuite.net
 * Description:       Easily embed and showcase Trustpilot reviews on your WordPress site to build trust and boost conversions.
 * Version:           1.2.1
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            GutenSuite
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gutensuite-reviewkit
 *
 * @package GutenSuite Reviewkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	exit;
}
require_once __DIR__ . '/vendor/autoload.php';

if ( ! class_exists( 'Gutensuite_Reviewkit' ) ) {
	/**
	 * Main plugin class.
	 *
	 * @package GutenSuite Reviewkit
	 */
	final class Gutensuite_Reviewkit {

		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->define_constants();
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
			add_action( 'init', array( $this, 'on_plugins_loaded' ) );
			add_action( 'gutensuite_reviewkit_loaded', array( $this, 'init_plugin' ) );
			add_action( 'init', array( $this, 'init_cron' ) );
		}

		/**
		 * Initialize the plugin instance.
		 *
		 * @return \Gutensuite_Reviewkit Plugin instance.
		 */
		public static function init() {
			static $instance = false;

			if ( ! $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		/**
		 * Defines CONSTANTS for Whole plugins.
		 */
		public function define_constants() {
			define( 'GUTENSUITE_REVIEWKIT_VERSION', '1.2.1' );
			define( 'GUTENSUITE_REVIEWKIT_SLUG', 'gutensuite-reviewkit' );
			define( 'GUTENSUITE_REVIEWKIT_PLUGIN_ROOT_URI', plugins_url( '/', __FILE__ ) );
			define( 'GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
			define( 'GUTENSUITE_REVIEWKIT_ASSETS_DIR_PATH', GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH . 'assets/' );
			define( 'GUTENSUITE_REVIEWKIT_ASSETS_URI', GUTENSUITE_REVIEWKIT_PLUGIN_ROOT_URI . 'assets/' );
			define( 'GUTENSUITE_REVIEWKIT_TP_API', 'https://api.gutensuite.net/data/tp/v1/reviews/' );
		}


		/**
		 * Fires when plugins are loaded.
		 */
		public function on_plugins_loaded() {
			do_action( 'gutensuite_reviewkit_loaded' );
		}

		/**
		 * Initialize the plugin
		 *
		 * @return void
		 */
		public function init_plugin() {
			require_once __DIR__ . '/includes/functions.php';
			$this->load_textdomain();
			if ( is_admin() ) {
				new \Gutensuite\Reviewkit\Admin();
			}
			new \Gutensuite\Reviewkit\Admin\Assets();
			new \Gutensuite\Reviewkit\Shortcodes();
		}

		public function load_textdomain() {
			load_plugin_textdomain( 'gutensuite-reviewkit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Plugin activation handler.
		 */
		public function activate() {
			update_option(
				'gutensuite_reviewkit_activation_flag',
				array(
					'version'              => GUTENSUITE_REVIEWKIT_VERSION,
					'last_activation_time' => time(),
				)
			);
		}

		/**
		 * Plugin deactivation handler.
		 */
		public function deactivate() {
			$timestamp = wp_next_scheduled( 'gutensuite_reviewkit_sync_reviews_cron' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'gutensuite_reviewkit_sync_reviews_cron' );
			}
		}

		/**
		 * Initialize cron jobs.
		 */
		public function init_cron() {
			$cron = \Gutensuite\Reviewkit\Admin\Cron::instance();
			$cron->schedule_sync_reviews();
		}
	}
}

/**
 * Initializes the main plugin.
 *
 * @return \Gutensuite_Reviewkit Plugin instance.
 */
if ( ! function_exists( 'gutensuite_reviewkit_start' ) ) {
	/**
	 * Start the plugin.
	 *
	 * @return \Gutensuite_Reviewkit Plugin instance.
	 */
	function gutensuite_reviewkit_start() {
		return Gutensuite_Reviewkit::init();
	}
}

// Plugin start.
gutensuite_reviewkit_start();

if ( ! function_exists( 'gutensuite_reviewkit_fs' ) ) {
	/**
	 * Initialize Freemius SDK.
	 *
	 * @return object Freemius instance.
	 */
	function gutensuite_reviewkit_fs() {
		global $gutensuite_reviewkit_fs;

		if ( ! isset( $gutensuite_reviewkit_fs ) ) {
			$gutensuite_reviewkit_fs = fs_dynamic_init(
				array(
					'id'             => '21247',
					'slug'           => 'gutensuite-reviewkit',
					'type'           => 'plugin',
					'public_key'     => 'pk_4d847aee16a8defb333dd6594503c',
					'is_premium'     => false,
					'has_addons'     => true,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'    => 'gutensuite/reviewkit',
						'account' => true,
						'contact' => true,
						'support' => true,
					),
				)
			);
		}

		return $gutensuite_reviewkit_fs;
	}

	// Init Freemius.
	gutensuite_reviewkit_fs();
	// Signal that SDK was initiated.
	do_action( 'gutensuite_reviewkit_fs_loaded' );
}
