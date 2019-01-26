<?php
/**
 * Plugin Name: Ignite
 * Plugin URI: https://github.com/veronalabs/ignite
 * Description: A WordPress Starter Plugin
 * Version: 1.0
 * Author: VeronaLabs
 * Author URI: https://veronalabs.com/
 * Text Domain: ignite
 * Domain Path: /languages
 */

// Define IGNITE_PLUGIN_FILE.
if ( ! defined( 'IGNITE_PLUGIN_FILE' ) ) {
	define( 'IGNITE_PLUGIN_FILE', __FILE__ );
}

/**
 * Load main class.
 */
require 'includes/class-ignite.php';

/**
 * Main instance of plugin.
 */
new Ignite();