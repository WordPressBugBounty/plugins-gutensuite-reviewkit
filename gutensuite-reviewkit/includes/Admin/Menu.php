<?php
namespace Gutensuite\Reviewkit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Menu {
	/**
	 * add plugin menu page and submenu pages
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * add admin menu page
	 *
	 * @return hooks
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Reviewkit', 'gutensuite-reviewkit' ),
			__( 'Reviewkit', 'gutensuite-reviewkit' ),
			'manage_options',
			'gutensuite/reviewkit',
			array( $this, 'load_main_template' ),
			'dashicons-star-half',
			6
		);

		add_submenu_page(
			'gutensuite/reviewkit',
			__( 'Reviewkit Home', 'gutensuite-reviewkit' ),
			__( 'Home', 'gutensuite-reviewkit' ),
			'manage_options',
			'gutensuite/reviewkit',
			array( $this, 'load_main_template' )
		);
	}

	/**
	 * dashboard root
	 *
	 * @return hooks
	 */
	public function load_main_template() {
		echo '<div id="gutensuite-reviewkit-body" class="gutensuite-reviewkit-body"></div>';
	}
}
