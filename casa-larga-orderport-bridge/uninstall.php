<?php
/**
 * Uninstall script for Casa Larga OrderPort Bridge
 *
 * @package CasaLargaOrderPortBridge
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up database tables
global $wpdb;

$tables = array(
	$wpdb->prefix . 'orderport_sync_log',
	$wpdb->prefix . 'orderport_cache',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS " . esc_sql( $table ) );
}

// Clean up options
$options = array(
	'cl_orderport_db_version',
	'cl_orderport_client_id',
	'cl_orderport_api_key',
	'cl_orderport_api_token',
	'cl_orderport_hostname',
	'cl_sync_frequency',
	'cl_last_sync_completed',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Clean up transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cl_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_cl_%'" );

// Clean up custom post types and their meta
$post_types = array( 'cl_wine_product', 'cl_wine_category' );

foreach ( $post_types as $post_type ) {
	$posts = get_posts( array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'post_status' => 'any',
	) );

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

// Clean up custom taxonomy
$terms = get_terms( array(
	'taxonomy' => 'cl_wine_type',
	'hide_empty' => false,
) );

if ( ! is_wp_error( $terms ) ) {
	foreach ( $terms as $term ) {
		wp_delete_term( $term->term_id, 'cl_wine_type' );
	}
}

// Clean up uploaded files (optional - be careful with this)
$upload_dir = wp_upload_dir();
$plugin_upload_dir = $upload_dir['basedir'] . '/casa-larga-logs';

if ( is_dir( $plugin_upload_dir ) ) {
	// Remove log files
	$files = glob( $plugin_upload_dir . '/*' );
	foreach ( $files as $file ) {
		if ( is_file( $file ) ) {
			unlink( $file );
		}
	}
	rmdir( $plugin_upload_dir );
}

// Clear any scheduled events
wp_clear_scheduled_hook( 'cl_orderport_cron_sync' );
