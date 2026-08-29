<?php
namespace Gutensuite\Reviewkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Gutensuite\Reviewkit\Admin\Menu;
use Gutensuite\Reviewkit\Admin\Ajax;

class Admin {
	public function __construct() {
		$this->add_menu();
		$this->add_actions();
	}

	private function add_menu() {
		new Menu();
	}

	private function add_actions() {
		new Ajax();
	}
}
