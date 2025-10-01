<?php
/**
 * Plugin Name:       Casa Larga OrderPort Bridge
 * Plugin URI:        https://casalarga.com/
 * Description:       Integrates Casa Larga WordPress site with OrderPort UAPI v1 for product catalog, REST endpoints, and custom label builder.
 * Version:           1.0.0
 * Author:            Casa Larga / Your Company
 * Author URI:        https://casalarga.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       casa-larga-orderport-bridge
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// Constants
// -----------------------------------------------------------------------------
if ( ! defined( 'CL_ORDERPORT_VERSION' ) ) {
	define( 'CL_ORDERPORT_VERSION', '1.0.0' );
}
if ( ! defined( 'CL_ORDERPORT_PATH' ) ) {
	define( 'CL_ORDERPORT_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'CL_ORDERPORT_URL' ) ) {
	define( 'CL_ORDERPORT_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'CL_ORDERPORT_BASENAME' ) ) {
	define( 'CL_ORDERPORT_BASENAME', plugin_basename( __FILE__ ) );
}

// -----------------------------------------------------------------------------
// i18n
// -----------------------------------------------------------------------------
function cl_orderport_load_textdomain() {
	load_plugin_textdomain( 'casa-larga-orderport-bridge', false, dirname( CL_ORDERPORT_BASENAME ) . '/languages' );
}
add_action( 'plugins_loaded', 'cl_orderport_load_textdomain' );

// -----------------------------------------------------------------------------
// Includes (guarded to avoid fatals before later phases create files)
// -----------------------------------------------------------------------------
function cl_orderport_maybe_require( $relative ) {
	$path = trailingslashit( CL_ORDERPORT_PATH ) . ltrim( $relative, '/' );
	if ( file_exists( $path ) ) {
		require_once $path;
		return true;
	}
	return false;
}

cl_orderport_maybe_require( 'includes/class-api-client.php' );
cl_orderport_maybe_require( 'includes/class-product-sync.php' );
cl_orderport_maybe_require( 'includes/class-admin-settings.php' );
cl_orderport_maybe_require( 'includes/class-rest-endpoints.php' );
cl_orderport_maybe_require( 'includes/class-custom-label.php' );

// -----------------------------------------------------------------------------
// Post Types & Taxonomies (Phase 1 & used by activation)
// -----------------------------------------------------------------------------
function cl_register_post_types() {
	// cl_wine_product
	$labels_product = array(
		'name'                  => _x( 'Wine Products', 'Post type general name', 'casa-larga-orderport-bridge' ),
		'singular_name'         => _x( 'Wine Product', 'Post type singular name', 'casa-larga-orderport-bridge' ),
		'menu_name'             => _x( 'Wine Products', 'Admin Menu text', 'casa-larga-orderport-bridge' ),
		'name_admin_bar'        => _x( 'Wine Product', 'Add New on Toolbar', 'casa-larga-orderport-bridge' ),
		'add_new'               => __( 'Add New', 'casa-larga-orderport-bridge' ),
		'add_new_item'          => __( 'Add New Wine Product', 'casa-larga-orderport-bridge' ),
		'new_item'              => __( 'New Wine Product', 'casa-larga-orderport-bridge' ),
		'edit_item'             => __( 'Edit Wine Product', 'casa-larga-orderport-bridge' ),
		'view_item'             => __( 'View Wine Product', 'casa-larga-orderport-bridge' ),
		'all_items'             => __( 'All Wine Products', 'casa-larga-orderport-bridge' ),
		'archives'              => __( 'Wine Product Archives', 'casa-larga-orderport-bridge' ),
		'attributes'            => __( 'Wine Product Attributes', 'casa-larga-orderport-bridge' ),
		'insert_into_item'      => __( 'Insert into wine product', 'casa-larga-orderport-bridge' ),
		'uploaded_to_this_item' => __( 'Uploaded to this wine product', 'casa-larga-orderport-bridge' ),
		'search_items'          => __( 'Search Wine Products', 'casa-larga-orderport-bridge' ),
		'parent_item_colon'     => __( 'Parent Wine Products:', 'casa-larga-orderport-bridge' ),
		'not_found'             => __( 'No wine products found.', 'casa-larga-orderport-bridge' ),
		'not_found_in_trash'    => __( 'No wine products found in Trash.', 'casa-larga-orderport-bridge' ),
	);

	$args_product = array(
		'labels'             => $labels_product,
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'wines' ),
		'show_in_rest'       => true,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'menu_position'      => 25,
		'menu_icon'          => 'dashicons-store',
		'show_in_admin_bar'  => true,
		'show_in_nav_menus'  => true,
		'show_ui'            => true,
	);
	register_post_type( 'cl_wine_product', $args_product );

	// cl_wine_category (implemented as hierarchical CPT per spec)
	$labels_category = array(
		'name'                  => _x( 'Wine Categories', 'Post type general name', 'casa-larga-orderport-bridge' ),
		'singular_name'         => _x( 'Wine Category', 'Post type singular name', 'casa-larga-orderport-bridge' ),
		'menu_name'             => _x( 'Wine Categories', 'Admin Menu text', 'casa-larga-orderport-bridge' ),
		'add_new_item'          => __( 'Add New Wine Category', 'casa-larga-orderport-bridge' ),
		'edit_item'             => __( 'Edit Wine Category', 'casa-larga-orderport-bridge' ),
		'new_item'              => __( 'New Wine Category', 'casa-larga-orderport-bridge' ),
		'view_item'             => __( 'View Wine Category', 'casa-larga-orderport-bridge' ),
		'all_items'             => __( 'All Wine Categories', 'casa-larga-orderport-bridge' ),
		'not_found'             => __( 'No wine categories found.', 'casa-larga-orderport-bridge' ),
	);

	$args_category = array(
		'labels'             => $labels_category,
		'public'             => true,
		'hierarchical'       => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'wine-categories' ),
		'show_in_rest'       => true,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'menu_position'      => 26,
		'menu_icon'          => 'dashicons-category',
		'show_in_admin_bar'  => true,
		'show_in_nav_menus'  => true,
		'show_ui'            => true,
	);
	register_post_type( 'cl_wine_category', $args_category );

	// cl_wine_type taxonomy for products
	$labels_tax = array(
		'name'              => _x( 'Wine Types', 'taxonomy general name', 'casa-larga-orderport-bridge' ),
		'singular_name'     => _x( 'Wine Type', 'taxonomy singular name', 'casa-larga-orderport-bridge' ),
		'search_items'      => __( 'Search Wine Types', 'casa-larga-orderport-bridge' ),
		'all_items'         => __( 'All Wine Types', 'casa-larga-orderport-bridge' ),
		'parent_item'       => __( 'Parent Wine Type', 'casa-larga-orderport-bridge' ),
		'parent_item_colon' => __( 'Parent Wine Type:', 'casa-larga-orderport-bridge' ),
		'edit_item'         => __( 'Edit Wine Type', 'casa-larga-orderport-bridge' ),
		'update_item'       => __( 'Update Wine Type', 'casa-larga-orderport-bridge' ),
		'add_new_item'      => __( 'Add New Wine Type', 'casa-larga-orderport-bridge' ),
		'new_item_name'     => __( 'New Wine Type Name', 'casa-larga-orderport-bridge' ),
		'menu_name'         => __( 'Wine Types', 'casa-larga-orderport-bridge' ),
	);

	$args_tax = array(
		'hierarchical'      => true,
		'labels'            => $labels_tax,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'wine-type' ),
		'show_in_rest'      => true,
	);
	register_taxonomy( 'cl_wine_type', array( 'cl_wine_product' ), $args_tax );
}
add_action( 'init', 'cl_register_post_types' );

// -----------------------------------------------------------------------------
// Activation / Deactivation (Phase 2)
// -----------------------------------------------------------------------------
function cl_orderport_activate() {
	global $wpdb;

	// Version checks
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		wp_die( esc_html__( 'Casa Larga OrderPort Bridge requires PHP 7.4 or higher.', 'casa-larga-orderport-bridge' ) );
	}
	global $wp_version;
	if ( version_compare( $wp_version, '5.0', '<' ) ) {
		wp_die( esc_html__( 'Casa Larga OrderPort Bridge requires WordPress 5.0 or higher.', 'casa-larga-orderport-bridge' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();
	$log_table       = $wpdb->prefix . 'orderport_sync_log';
	$cache_table     = $wpdb->prefix . 'orderport_cache';

	$log_sql = "CREATE TABLE {$log_table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		sync_date datetime NOT NULL,
		status varchar(20) NOT NULL,
		message text,
		products_synced int(11) DEFAULT 0,
		categories_synced int(11) DEFAULT 0,
		PRIMARY KEY  (id),
		KEY idx_sync_date (sync_date)
	) {$charset_collate};";

	$cache_sql = "CREATE TABLE {$cache_table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		cache_key varchar(255) NOT NULL,
		cache_data longtext,
		expires datetime DEFAULT NULL,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		UNIQUE KEY cache_key (cache_key),
		KEY idx_cache_key (cache_key),
		KEY idx_expires (expires)
	) {$charset_collate};";

	dbDelta( $log_sql );
	dbDelta( $cache_sql );

	// Options
	add_option( 'cl_orderport_db_version', '1.0' );
	add_option( 'cl_orderport_client_id', 88884255 );
	add_option( 'cl_orderport_api_key', '4b024d52-c688-4264-90c4-47c5abcdbdfb' );
	add_option( 'cl_orderport_api_token', 'BN3I09@bWyeKTICdU0P8Q5S-7kdXBBRS' );
	add_option( 'cl_orderport_hostname', 'casalarga.orderport.net' );
	add_option( 'cl_sync_frequency', 'hourly' );
	add_option( 'cl_last_sync_completed', 0 );

	// Ensure CPTs exist before flushing rewrites
	cl_register_post_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'cl_orderport_activate' );

function cl_orderport_deactivate() {
	// Unschedule any cron jobs if present in later phases
	$timestamp = wp_next_scheduled( 'cl_orderport_cron_sync' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'cl_orderport_cron_sync' );
	}
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cl_orderport_deactivate' );

// -----------------------------------------------------------------------------
// Admin Menu (wrapper; full admin pages in later phases)
// -----------------------------------------------------------------------------
function cl_add_admin_menu() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// If the admin settings class exists, let it register its own menus.
	if ( class_exists( 'CL_Admin_Settings' ) ) {
		new CL_Admin_Settings();
		return;
	}

	// Fallback minimal menu to avoid orphaned hooks before class exists
	add_menu_page(
		__( 'Casa Larga OrderPort', 'casa-larga-orderport-bridge' ),
		__( 'OrderPort Sync', 'casa-larga-orderport-bridge' ),
		'manage_options',
		'cl-orderport',
		'cl_admin_menu_placeholder',
		'dashicons-update-alt',
		58
	);
}
add_action( 'admin_menu', 'cl_add_admin_menu' );

function cl_admin_menu_placeholder() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'casa-larga-orderport-bridge' ) );
	}
	cl_placeholder_admin();
}

function cl_placeholder_admin() {
	echo '<div class="wrap"><h1>' . esc_html__( 'Casa Larga OrderPort', 'casa-larga-orderport-bridge' ) . '</h1>';
	echo '<p>' . esc_html__( 'Admin interface will be available after setup is completed in later phases.', 'casa-larga-orderport-bridge' ) . '</p></div>';
}

// -----------------------------------------------------------------------------
// Assets
// -----------------------------------------------------------------------------
function cl_enqueue_frontend_assets() {
	wp_register_style( 'cl-frontend-styles', CL_ORDERPORT_URL . 'assets/css/frontend-styles.css', array(), CL_ORDERPORT_VERSION );
	wp_register_script( 'cl-frontend-scripts', CL_ORDERPORT_URL . 'assets/js/frontend-scripts.js', array( 'jquery' ), CL_ORDERPORT_VERSION, true );

	wp_enqueue_style( 'cl-frontend-styles' );
	wp_enqueue_script( 'cl-frontend-scripts' );
}
add_action( 'wp_enqueue_scripts', 'cl_enqueue_frontend_assets' );

// Inject age gate modal into footer
function cl_inject_age_gate() {
    global $post;
    
    // Show on wine product pages or pages with label builder shortcode
    $show_gate = false;
    
    if (is_singular('cl_wine_product')) {
        $show_gate = true;
    } elseif (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'cl_custom_label_builder')) {
        $show_gate = true;
    }
    
    if ($show_gate) {
        $template = trailingslashit(CL_ORDERPORT_PATH) . 'templates/age-gate.php';
        if (file_exists($template)) {
            include $template;
        }
    }
}
add_action('wp_footer', 'cl_inject_age_gate');

function cl_enqueue_admin_assets( $hook_suffix ) {
	wp_register_style( 'cl-admin-styles', CL_ORDERPORT_URL . 'assets/css/admin-styles.css', array(), CL_ORDERPORT_VERSION );
	wp_register_script( 'cl-admin-scripts', CL_ORDERPORT_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), CL_ORDERPORT_VERSION, true );
	
	// Localize script with needed data
	wp_localize_script( 'cl-admin-scripts', 'clLabelBuilder', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('cl_label_nonce'),
	));

	wp_enqueue_style( 'cl-admin-styles' );
	wp_enqueue_script( 'cl-admin-scripts' );
}
add_action( 'admin_enqueue_scripts', 'cl_enqueue_admin_assets' );

// -----------------------------------------------------------------------------
// Shortcodes
// -----------------------------------------------------------------------------
function cl_wine_categories_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'style' => 'grid' ), $atts, 'cl_wine_categories' );

	$categories = get_posts( array(
		'post_type'      => 'cl_wine_category',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );

	ob_start();
	$template = trailingslashit( CL_ORDERPORT_PATH ) . 'templates/category-grid.php';
	if ( file_exists( $template ) ) {
		include $template;
	} else {
		echo '<div class="cl-categories">';
		foreach ( $categories as $cat ) {
			echo '<div class="cl-category-item">' . esc_html( get_the_title( $cat ) ) . '</div>';
		}
		echo '</div>';
	}
	return ob_get_clean();
}
add_shortcode( 'cl_wine_categories', 'cl_wine_categories_shortcode' );

function cl_wine_list_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'category' => '',
		'per_page' => 12,
		'orderby'  => 'menu_order',
	), $atts, 'cl_wine_list' );

	$args = array(
		'post_type'      => 'cl_wine_product',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $atts['per_page'],
		'orderby'        => sanitize_text_field( $atts['orderby'] ),
		'order'          => 'ASC',
	);

	if ( ! empty( $atts['category'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'cl_wine_type',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			),
		);
	}

	$wines = new WP_Query( $args );
	ob_start();
	$template = trailingslashit( CL_ORDERPORT_PATH ) . 'templates/product-list.php';
	if ( file_exists( $template ) ) {
		include $template;
	} else {
		echo '<div class="cl-wine-list">';
		if ( $wines->have_posts() ) {
			while ( $wines->have_posts() ) {
				$wines->the_post();
				echo '<div class="cl-wine-item">';
				echo '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
				echo '</div>';
			}
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No wines found.', 'casa-larga-orderport-bridge' ) . '</p>';
		}
		echo '</div>';
	}
	return ob_get_clean();
}
add_shortcode( 'cl_wine_list', 'cl_wine_list_shortcode' );

function cl_wine_detail_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'cl_wine_detail' );
	$post_id = absint( $atts['id'] );
	if ( ! $post_id ) {
		return '<p>' . esc_html__( 'Invalid wine ID.', 'casa-larga-orderport-bridge' ) . '</p>';
	}
	$wine = get_post( $post_id );
	if ( ! $wine || 'cl_wine_product' !== $wine->post_type ) {
		return '<p>' . esc_html__( 'Wine not found.', 'casa-larga-orderport-bridge' ) . '</p>';
	}
	setup_postdata( $wine );
	ob_start();
	$template = trailingslashit( CL_ORDERPORT_PATH ) . 'templates/product-detail.php';
	if ( file_exists( $template ) ) {
		include $template;
	} else {
		echo '<div class="cl-wine-detail">';
		echo '<h2>' . esc_html( get_the_title( $wine ) ) . '</h2>';
		echo apply_filters( 'the_content', wp_kses_post( $wine->post_content ) );
		echo '</div>';
	}
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'cl_wine_detail', 'cl_wine_detail_shortcode' );

function cl_custom_label_builder_shortcode( $atts ) {
	// If class exists, defer to it. Otherwise include template directly when available.
	if ( class_exists( 'CL_Custom_Label' ) ) {
		static $cl_custom_label_instance = null;
		if ( null === $cl_custom_label_instance ) {
			$cl_custom_label_instance = new CL_Custom_Label();
		}
		return $cl_custom_label_instance->render_builder( $atts );
	}

	ob_start();
	$template = trailingslashit( CL_ORDERPORT_PATH ) . 'templates/custom-label-builder.php';
	if ( file_exists( $template ) ) {
		include $template;
	} else {
		echo '<div class="cl-custom-label-builder">' . esc_html__( 'Custom label builder will be available soon.', 'casa-larga-orderport-bridge' ) . '</div>';
	}
	return ob_get_clean();
}
add_shortcode( 'cl_custom_label_builder', 'cl_custom_label_builder_shortcode' );

// -----------------------------------------------------------------------------
// REST API bootstrap
// -----------------------------------------------------------------------------
function cl_orderport_register_rest_endpoints() {
	if ( class_exists( 'CL_REST_Endpoints' ) ) {
		static $cl_rest_instance = null;
		if ( null === $cl_rest_instance ) {
			$cl_rest_instance = new CL_REST_Endpoints();
		}
	}
}
add_action( 'rest_api_init', 'cl_orderport_register_rest_endpoints' );

// Schedule automatic sync
function cl_schedule_sync() {
    $frequency = get_option('cl_sync_frequency', 'hourly');
    if (!wp_next_scheduled('cl_orderport_cron_sync')) {
        wp_schedule_event(time(), $frequency, 'cl_orderport_cron_sync');
    } else {
        // Update frequency if changed
        $timestamp = wp_next_scheduled('cl_orderport_cron_sync');
        wp_unschedule_event($timestamp, 'cl_orderport_cron_sync');
        wp_schedule_event(time(), $frequency, 'cl_orderport_cron_sync');
    }
}
add_action('wp', 'cl_schedule_sync');

function cl_run_scheduled_sync() {
    if (!class_exists('CL_API_Client') || !class_exists('CL_Product_Sync')) {
        return;
    }
    $api_client = new CL_API_Client();
    $sync = new CL_Product_Sync($api_client);
    $sync->sync_catalog();
}
add_action('cl_orderport_cron_sync', 'cl_run_scheduled_sync');

// -----------------------------------------------------------------------------
// Admin notices if required files are missing
// -----------------------------------------------------------------------------
function cl_orderport_missing_files_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$missing  = array();
	$expected = array(
		'includes/class-api-client.php',
		'includes/class-product-sync.php',
		'includes/class-admin-settings.php',
		'includes/class-rest-endpoints.php',
		'includes/class-custom-label.php',
	);
	foreach ( $expected as $rel ) {
		if ( ! file_exists( trailingslashit( CL_ORDERPORT_PATH ) . $rel ) ) {
			$missing[] = $rel;
		}
	}
	if ( ! empty( $missing ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Casa Larga OrderPort Bridge: Some files are not yet present. The plugin may be partially functional until setup is completed.', 'casa-larga-orderport-bridge' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'cl_orderport_missing_files_notice' );

