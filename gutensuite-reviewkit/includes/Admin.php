<?php
namespace Gutensuite\Reviewkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Gutensuite\Reviewkit\Admin\Menu;
use Gutensuite\Reviewkit\Admin\Ajax;
use Gutensuite\Reviewkit\Admin\Notice;

class Admin {
	public function __construct() {
		$this->add_menu();
		$this->add_actions();
		$this->add_notices();
	}

	private function add_menu() {
		new Menu();
	}

	private function add_actions() {
		new Ajax();
	}

	private function add_notices() {
		new Notice();
	}

}
