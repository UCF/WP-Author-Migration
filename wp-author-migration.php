<?php
/*
Plugin Name: WP Author Migration
Description: Set of utilities for migrating authors from one site to another and remapping posts with the appropriate author IDs.
Version: 1.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/WP-Author-Migration
*/

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WPAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once dirname( __FILE__ ) . '/includes/class-wpam-author-migrate.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/commands/wpam-wp-cli-author-migrate.php';
}
