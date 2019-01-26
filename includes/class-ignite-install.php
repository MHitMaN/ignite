<?php

namespace Ignite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class Install
 * @package Ignite
 */
class Install {
	/**
	 * Creating plugin tables
	 *
	 */
	static function install() {
		global $wpdb;

		$table_name      = $wpdb->prefix . "ignite_table";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			created datetime NOT NULL,
			name tinytext NOT NULL,
			text text NOT NULL,
			url varchar(55) DEFAULT '' NOT NULL,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'ignite_db_version', 1.0 );
	}
}