<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ignite {
	/**
	 * Ignite constructor.
	 */
	public function __construct() {
		$this->set_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define Constants.
	 */
	private function set_constants() {
		define( 'IGNITE_ABSPATH', plugin_dir_path( IGNITE_PLUGIN_FILE ) );
		define( 'IGNITE_URL', plugin_dir_url( dirname( __FILE__ ) ) );
	}

	/**
	 * Initial plugin setup.
	 */
	private function init_hooks() {
		register_activation_hook( IGNITE_PLUGIN_FILE, array( '\Ignite\Install', 'install' ) );

		// Load text domain
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ignite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Includes classes and functions.
	 */
	public function includes() {
		require_once IGNITE_ABSPATH . 'includes/class-ignite-install.php';

		if ( is_admin() ) {
			require_once IGNITE_ABSPATH . 'includes/admin/class-ignite-admin.php';
			require_once IGNITE_ABSPATH . 'includes/admin/class-ignite-settings.php';
		} else {
			require_once IGNITE_ABSPATH . 'includes/class-ignite-public.php';
		}

		// Utility classes.
		require_once IGNITE_ABSPATH . 'includes/class-ignite-option.php';
		require_once IGNITE_ABSPATH . 'includes/class-ignite-public.php';

		// API classes.
		require_once IGNITE_ABSPATH . 'includes/class-ignite-rest-api.php';
		require_once IGNITE_ABSPATH . 'includes/api/v1/class-ignite-api-controller.php';

		// Template functions.
		require_once IGNITE_ABSPATH . 'includes/template-functions.php';
	}
}