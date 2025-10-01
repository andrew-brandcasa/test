<?php
/**
 * CL_Product_Sync - Handles syncing OrderPort catalog to WordPress
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CL_Product_Sync' ) ) :

class CL_Product_Sync {
	/**
	 * API client instance.
	 *
	 * @var CL_API_Client
	 */
	private $api_client;

	/**
	 * Current sync log entry ID.
	 *
	 * @var int
	 */
	private $log_id;

	/**
	 * Constructor.
	 *
	 * @param CL_API_Client $api_client API client instance.
	 */
	public function __construct( CL_API_Client $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Sync entire catalog from OrderPort API.
	 *
	 * @return array|WP_Error Success message or error.
	 */
	public function sync_catalog() {
		global $wpdb;

		// Create sync log entry
		$table = $wpdb->prefix . 'orderport_sync_log';
		$wpdb->insert(
			$table,
			array(
				'sync_date' => current_time( 'mysql' ),
				'status'    => 'running',
				'message'   => 'Starting catalog sync',
				'products_synced'   => 0,
				'categories_synced' => 0,
			),
			array( '%s', '%s', '%s', '%d', '%d' )
		);
		$this->log_id = $wpdb->insert_id;
		if (0 === $this->log_id || empty($this->log_id)) {
			error_log('Casa Larga DB Error: ' . $wpdb->last_error);
			return new WP_Error('db_error', 'Failed to create sync log entry');
		}

		// Get catalog from API
		$response = $this->api_client->get_catalog();
		if ( is_wp_error( $response ) ) {
			$this->update_log( 'error', 'API Error: ' . $response->get_error_message(), 0, 0 );
			return $response;
		}

		$groups = isset( $response['Groups'] ) ? $response['Groups'] : array();
		$products = isset( $response['Products'] ) ? $response['Products'] : array();

		$products_synced = 0;
		$categories_synced = 0;

		// Process groups (categories)
		foreach ( $groups as $group ) {
			$post_id = $this->process_group( $group );
			if ( $post_id ) {
				$categories_synced++;
			}
		}

		// Process products
		foreach ( $products as $product ) {
			$post_id = $this->process_product( $product );
			if ( $post_id ) {
				$products_synced++;
			}
		}

		// Update sync log with success
		$this->update_log( 'success', 'Sync completed successfully', $products_synced, $categories_synced );

		// Update last sync timestamp
		update_option( 'cl_last_sync_completed', current_time( 'timestamp' ) );

		return array(
			'success' => true,
			'message' => sprintf(
				__( 'Sync completed: %d products, %d categories', 'casa-larga-orderport-bridge' ),
				$products_synced,
				$categories_synced
			),
			'products_synced' => $products_synced,
			'categories_synced' => $categories_synced,
		);
	}

	/**
	 * Process a group (category) from OrderPort API.
	 *
	 * @param array $group_data Group data from API.
	 * @return int|false Post ID or false on failure.
	 */
	private function process_group( $group_data ) {
		if (!isset($group_data['Id']) || !isset($group_data['Name'])) {
			error_log('Casa Larga: Invalid group data structure');
			return false;
		}
		$group_id = absint( $group_data['Id'] );
		$name = sanitize_text_field( $group_data['Name'] );

		// Check if category already exists
		$existing = get_posts( array(
			'post_type'      => 'cl_wine_category',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'orderport_group_id',
					'value' => $group_id,
				),
			),
		) );

		$post_data = array(
			'post_title'   => $name,
			'post_content' => isset( $group_data['Summary'] ) ? wp_kses_post( $group_data['Summary'] ) : '',
			'post_status'  => 'publish',
			'post_type'    => 'cl_wine_category',
		);

		if ( ! empty( $existing ) ) {
			$post_data['ID'] = $existing[0]->ID;
			$post_id = wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return false;
		}

		// Update meta fields
		update_post_meta( $post_id, 'orderport_group_id', $group_id );
		
		if ( isset( $group_data['Summary'] ) ) {
			update_post_meta( $post_id, 'group_summary', sanitize_text_field( $group_data['Summary'] ) );
		}
		if ( isset( $group_data['Style'] ) ) {
			update_post_meta( $post_id, 'display_style', sanitize_text_field( $group_data['Style'] ) );
		}
		if ( isset( $group_data['RequiresAuthentication'] ) ) {
			update_post_meta( $post_id, 'requires_authentication', (bool) $group_data['RequiresAuthentication'] );
		}
		if ( isset( $group_data['CustomerClassRestrictions'] ) ) {
			update_post_meta( $post_id, 'customer_class_restrictions', wp_json_encode( $group_data['CustomerClassRestrictions'] ) );
		}

		return $post_id;
	}

	/**
	 * Process a product from OrderPort API.
	 *
	 * @param array $product_data Product data from API.
	 * @return int|false Post ID or false on failure.
	 */
	private function process_product( $product_data ) {
		if (!isset($product_data['Opsku']) || !isset($product_data['Title'])) {
			error_log('Casa Larga: Invalid product data structure');
			return false;
		}
		$opsku = sanitize_text_field( $product_data['Opsku'] );
		$title = sanitize_text_field( $product_data['Title'] );
		$overview = isset( $product_data['Overview'] ) ? wp_kses_post( $product_data['Overview'] ) : '';

		// Check if product already exists
		$existing = get_posts( array(
			'post_type'      => 'cl_wine_product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'orderport_sku',
					'value' => $opsku,
				),
			),
		) );

		$post_data = array(
			'post_title'   => $title,
			'post_content' => $overview,
			'post_status'  => ( isset( $product_data['Status'] ) && 'Active' === $product_data['Status'] ) ? 'publish' : 'draft',
			'post_type'    => 'cl_wine_product',
		);

		if ( ! empty( $existing ) ) {
			$post_data['ID'] = $existing[0]->ID;
			$post_id = wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return false;
		}

		// Update meta fields
		update_post_meta( $post_id, 'orderport_sku', $opsku );
		update_post_meta( $post_id, 'part_no', sanitize_text_field( $product_data['PartNo'] ) );
		update_post_meta( $post_id, 'status', sanitize_text_field( $product_data['Status'] ) );
		
		if ( isset( $product_data['StatusMessage'] ) ) {
			update_post_meta( $post_id, 'status_message', sanitize_text_field( $product_data['StatusMessage'] ) );
		}
		if ( isset( $product_data['RetailPrice'] ) ) {
			update_post_meta( $post_id, 'retail_price', floatval( $product_data['RetailPrice'] ) );
		}
		if ( isset( $product_data['SalePrice'] ) ) {
			update_post_meta( $post_id, 'sale_price', floatval( $product_data['SalePrice'] ) );
		}
		if ( isset( $product_data['Color'] ) ) {
			update_post_meta( $post_id, 'color', sanitize_text_field( $product_data['Color'] ) );
		}
		if ( isset( $product_data['Size'] ) ) {
			update_post_meta( $post_id, 'size', sanitize_text_field( $product_data['Size'] ) );
		}
		if ( isset( $product_data['StartDate'] ) ) {
			update_post_meta( $post_id, 'start_date', sanitize_text_field( $product_data['StartDate'] ) );
		}
		if ( isset( $product_data['SummaryOverview'] ) ) {
			update_post_meta( $post_id, 'summary_overview', wp_kses_post( $product_data['SummaryOverview'] ) );
		}
		if ( isset( $product_data['ProductPageUrl'] ) ) {
			update_post_meta( $post_id, 'product_page_url', esc_url_raw( $product_data['ProductPageUrl'] ) );
		}

		update_post_meta( $post_id, 'last_synced', current_time( 'mysql' ) );

		// Process specs
		if ( isset( $product_data['Specs'] ) && is_array( $product_data['Specs'] ) ) {
			foreach ( $product_data['Specs'] as $key => $value ) {
				$meta_key = 'spec_' . sanitize_key( $key );
				update_post_meta( $post_id, $meta_key, sanitize_text_field( $value ) );
			}
		}

		// Process image
		if ( isset( $product_data['Image']['Large'] ) ) {
			$this->download_product_image( $product_data['Image']['Large'], $post_id );
		}

		return $post_id;
	}

	/**
	 * Download and set product image.
	 *
	 * @param string $image_url Image URL from OrderPort.
	 * @param int    $post_id   WordPress post ID.
	 * @return int|false Attachment ID or false on failure.
	 */
	private function download_product_image( $image_url, $post_id ) {
		// Check if image URL changed
		$stored_url = get_post_meta($post_id, '_cl_orderport_image_url', true);
		
		if ($stored_url === $image_url && has_post_thumbnail($post_id)) {
			return get_post_thumbnail_id($post_id);
		}
		
		// Delete old thumbnail if URL changed
		if (has_post_thumbnail($post_id)) {
			$old_thumb_id = get_post_thumbnail_id($post_id);
			wp_delete_attachment($old_thumb_id, true);
		}

		$attachment_id = media_sideload_image( $image_url, $post_id, '', 'id' );
		
		if ( is_wp_error( $attachment_id ) ) {
			// Use placeholder on failure
			$placeholder_path = CL_ORDERPORT_PATH . 'assets/images/wine-placeholder.jpg';
			if ( file_exists( $placeholder_path ) ) {
				$attachment_id = wp_insert_attachment(
					array(
						'post_mime_type' => 'image/jpeg',
						'post_title'     => 'Wine Placeholder',
						'post_content'   => '',
						'post_status'    => 'inherit',
					),
					$placeholder_path,
					$post_id
				);
			}
		}

		if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
			update_post_meta($post_id, '_cl_orderport_image_url', $image_url);
			return $attachment_id;
		}

		return false;
	}

	/**
	 * Update sync log entry.
	 *
	 * @param string $status           Status (success, error, running).
	 * @param string $message          Log message.
	 * @param int    $products_synced  Number of products synced.
	 * @param int    $categories_synced Number of categories synced.
	 */
	private function update_log( $status, $message, $products_synced, $categories_synced ) {
		if ( ! $this->log_id ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'orderport_sync_log';
		$wpdb->update(
			$table,
			array(
				'status'            => sanitize_text_field( $status ),
				'message'           => sanitize_text_field( $message ),
				'products_synced'   => absint( $products_synced ),
				'categories_synced' => absint( $categories_synced ),
			),
			array( 'id' => $this->log_id ),
			array( '%s', '%s', '%d', '%d' ),
			array( '%d' )
		);
		if (false === $wpdb->update) {
			error_log('Casa Larga DB Error: ' . $wpdb->last_error);
		}
	}

	/**
	 * Clear expired cache entries.
	 */
	public function clear_cache() {
		global $wpdb;
		$cache_table = $wpdb->prefix . 'orderport_cache';
		
		// Delete expired cache entries
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$cache_table} WHERE expires < %s", current_time( 'mysql' ) ) );
		if (false === $wpdb->query) {
			error_log('Casa Larga DB Error: ' . $wpdb->last_error);
		}
		
		// Clear catalog transients
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_cl_catalog_%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_cl_catalog_%' ) );
	}
}

endif;
