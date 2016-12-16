<?php

/**
 * Plugin Name: Superdesk Publisher
 * Plugin URI: 
 * Description: Nacitani clanku
 * Version: 0.1
 * Author: AdminIT
 */
if (!function_exists('add_action')) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}


define('MVP_VERSION', '0.1');

define('MVP_REQUIRED_WP_VERSION', '4.2');

define('MVP_PLUGIN', __FILE__);

define('MVP_PLUGIN_DIR', untrailingslashit(dirname(MVP_PLUGIN)));

define('MVP_PLUGIN_URL', untrailingslashit(plugins_url('', MVP_PLUGIN)));

define('MVP_DATABASE_VERSION', '2');

define('DB_TABLE_SYNC_POST', 'sync_posts');


require_once MVP_PLUGIN_DIR . '/settings.php';

function mvp_database_install() {
  global $wpdb;
  $table_name = $wpdb->prefix . DB_TABLE_SYNC_POST;

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
    post_id bigint(20) NOT NULL,
		guid text NOT NULL,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta($sql);

  add_option('MVP_DATABASE_VERSION', MVP_DATABASE_VERSION);
}

function mvp_database_update() {
  if (get_site_option('MVP_DATABASE_VERSION') != MVP_DATABASE_VERSION) {
    mvp_database_install();
  }
}

function saveFile($from, $to) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $from);

  $fp = fopen($to, 'w+');
  curl_setopt($ch, CURLOPT_FILE, $fp);

  curl_exec($ch);

  curl_close($ch);
  fclose($fp);
}

register_activation_hook(__FILE__, 'mvp_database_install');
add_action('plugins_loaded', 'mvp_database_update');
