<?php
defined('ABSPATH') || exit;

if(!class_exists('iPanorama_Activator')) :

class iPanorama_Activator {
	public function activate() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		global $wpdb;
        $charsetCollate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

        $table = $wpdb->prefix . IPANORAMA_PLUGIN_NAME;
		$sql = "CREATE TABLE {$table} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title text DEFAULT NULL,
			slug varchar(200) DEFAULT NULL,
			active tinyint NOT NULL DEFAULT 1,
			data longtext DEFAULT NULL,
			config longtext DEFAULT NULL,
			author bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			editor bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			deleted tinyint NOT NULL DEFAULT 0,
			created datetime NULL,
			modified datetime NULL,
			UNIQUE KEY id (id)
		) {$charsetCollate};";
		dbDelta($sql);
		
		update_option('ipanorama_db_version', IPANORAMA_DB_VERSION, false);
		$this->update_data();
		if(get_option('ipanorama_activated') == false) {
			$this->install_data();
		}
		update_option('ipanorama_activated', time(), false);
	}
	
	public function update_data() {
		global $wpdb;
		$table = $wpdb->prefix . IPANORAMA_PLUGIN_NAME;

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = $wpdb->prepare("UPDATE {$table} SET editor=author WHERE editor=%d", 0);
		$wpdb->query($sql);
        // phpcs:enable
	}
	
	public function install_data() {
	}
	
	public function check_db() {
		if(get_option('ipanorama_db_version') != IPANORAMA_DB_VERSION) {
			$this->activate();
		}
	}
}
endif;