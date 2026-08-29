<?php
namespace Gutensuite\Reviewkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Gutensuite\Reviewkit\Shortcodes\MicroButton;
use Gutensuite\Reviewkit\Shortcodes\MicroCombo;
use Gutensuite\Reviewkit\Shortcodes\MicroReviewCount;
use Gutensuite\Reviewkit\Shortcodes\MicroStar;
use Gutensuite\Reviewkit\Shortcodes\MicroTrustScore;
use Gutensuite\Reviewkit\Shortcodes\MiniTrustbox;
use Gutensuite\Reviewkit\Shortcodes\ReviewWidgets;
use Gutensuite\Reviewkit\Shortcodes\StarterTrustbox;

/**
 * Main Shortcodes class to register and manage all Trustpilot shortcodes
 */
class Shortcodes {

	/**
	 * Array of shortcode instances
	 *
	 * @var array
	 */
	public $shortcodes = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_shortcodes();
	}

	/**
	 * Initialize all shortcodes
	 */
	public function init_shortcodes() {
		$this->shortcodes = array(
			'mini'              => new MiniTrustbox(),
			'starter'           => new StarterTrustbox(),
			'micro_trustscore'  => new MicroTrustScore(),
			'micro_star'        => new MicroStar(),
			'micro_reviewcount' => new MicroReviewCount(),
			'micro_button'      => new MicroButton(),
			'micro_combo'       => new MicroCombo(),
		);
		// Register each shortcode
		foreach ( $this->shortcodes as $key => $shortcode ) {
			$shortcode->register();
		}

		$this->init_review_widgets();
	}

	public function init_review_widgets() {
		new ReviewWidgets();
	}

	public function enqueue_shortcode_assets() {
	}

	/**
	 * Check if any of our shortcodes are being used on the current page
	 *
	 * @return bool
	 */
	public static function has_shortcodes_on_page() {
		global $post;

		if ( ! $post ) {
			return false;
		}

		$shortcode_tags = array(
			'reviewkit_tp_mini',
			'reviewkit_tp_starter',
			'reviewkit_tp_micro_trustscore',
			'reviewkit_tp_micro_star',
			'reviewkit_tp_micro_reviewcount',
			'reviewkit_tp_micro_button',
			'reviewkit_tp_micro_combo',
			'reviewkit_review',
		);

		foreach ( $shortcode_tags as $tag ) {
			if ( has_shortcode( $post->post_content, $tag ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all registered shortcodes
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return $this->shortcodes;
	}

	/**
	 * Get shortcode information for admin interface
	 *
	 * @return array
	 */
	public function get_shortcodes_info() {
		return array(
			array(
				'name'        => 'Mini Trustbox',
				'shortcode'   => '[reviewkit_tp_mini]',
				'description' => 'Compact widget showing TrustScore, star rating, and review count',
				'category'    => 'free',
			),
			array(
				'name'        => 'Starter Trustbox',
				'shortcode'   => '[reviewkit_tp_starter]',
				'description' => 'Interactive widget with star rating and tooltip',
				'category'    => 'free',
			),
			array(
				'name'        => 'Micro TrustScore',
				'shortcode'   => '[reviewkit_tp_micro_trustscore]',
				'description' => 'Simple display of TrustScore rating',
				'category'    => 'free',
			),
			array(
				'name'        => 'Micro Star',
				'shortcode'   => '[reviewkit_tp_micro_star]',
				'description' => 'Interactive star rating display',
				'category'    => 'free',
			),
			array(
				'name'        => 'Micro Review Count',
				'shortcode'   => '[reviewkit_tp_micro_reviewcount]',
				'description' => 'Link showing review count',
				'category'    => 'free',
			),
			array(
				'name'        => 'Micro Button',
				'shortcode'   => '[reviewkit_tp_micro_button]',
				'description' => 'Button-style widget with review count',
				'category'    => 'free',
			),
			array(
				'name'        => 'Micro Combo',
				'shortcode'   => '[reviewkit_tp_micro_combo]',
				'description' => 'Combined star rating and review count widget',
				'category'    => 'free',
			),
		);
	}
}
