<?php

namespace Ignite;

/**
 * Class Admin
 * @package Ignite
 */
class Admin {
	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initial plugin
	 */
	private function init_hooks() {
		// Check exists require function
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include( ABSPATH . "wp-includes/pluggable.php" );
		}

		// Add plugin caps to admin role
		if ( is_admin() and is_super_admin() ) {
			$this->add_cap();
		}

		// Actions.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Adding new capability in the plugin
	 */
	public function add_cap() {
		// Get administrator role
		$role = get_role( 'administrator' );

		$role->add_cap( 'ignite_table' );
		$role->add_cap( 'ignite_setting' );
	}

	/**
	 * Include admin assets
	 *
	 * @param $hook
	 */
	public function admin_assets( $hook ) {
		if ( 'edit.php' == $hook ) {
			return;
		}

		wp_register_style( 'ignite-admin-css', IGNITE_URL . 'includes/admin/assets/css/admin.css', true );
		wp_enqueue_style( 'ignite-admin-css' );

		wp_enqueue_script( 'ignite-admin-js', IGNITE_URL . 'includes/admin/assets/js/admin.js' );
	}

	/**
	 * Register admin menu
	 */
	public function admin_menu() {
		add_menu_page( __( 'Ignite', 'ignite' ), __( 'Ignite', 'ignite' ), 'ignite_table', 'ignite', array( $this, 'ignite_callback' ));
		add_submenu_page( 'ignite', __( 'Example Table Data', 'ignite' ), __( 'Example Table Data', 'ignite' ), 'ignite_table', 'ignite', array( $this, 'table_callback' ) );
	}

	/**
	 * Callback outbox page.
	 */
	public function table_callback() {
		include_once IGNITE_ABSPATH . "includes/admin/table/class-ignite-table.php";

		// Create an instance of our package class...
		$list_table = new List_Table();

		// Fetch, prepare, sort, and filter our data...
		$list_table->prepare_items();

		include_once IGNITE_ABSPATH . "includes/admin/table/table.php";
	}
}

new Admin();
