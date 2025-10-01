<?php
/**
 * CL_REST_Endpoints - REST API endpoints for OrderPort Bridge
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CL_REST_Endpoints' ) ) :

class CL_REST_Endpoints {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		// Public wine endpoints
		register_rest_route( 'casa-larga/v1', '/wines', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_wines' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'category'  => array(
					'type'        => 'string',
					'description' => 'Filter by wine category slug',
				),
				'per_page'  => array(
					'type'        => 'integer',
					'default'     => 12,
					'minimum'     => 1,
					'maximum'     => 100,
				),
				'page'      => array(
					'type'        => 'integer',
					'default'     => 1,
					'minimum'     => 1,
				),
			),
		) );

		register_rest_route( 'casa-larga/v1', '/wines/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_wine' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'type'        => 'integer',
					'required'    => true,
				),
			),
		) );

		register_rest_route( 'casa-larga/v1', '/categories', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_categories' ),
			'permission_callback' => '__return_true',
		) );

		// Admin sync endpoint
		register_rest_route( 'casa-larga/v1', '/sync', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'trigger_sync' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );

		// Custom label order endpoint
		register_rest_route( 'casa-larga/v1', '/custom-label-order', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'submit_custom_label' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Check admin permission.
	 *
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get wines list.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_wines( $request ) {
		$category = $request->get_param( 'category' );
		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );

		$args = array(
			'post_type'      => 'cl_wine_product',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		if ( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'cl_wine_type',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $category ),
				),
			);
		}

		$query = new WP_Query( $args );
		$wines = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$wines[] = $this->format_wine_data( get_post() );
			}
			wp_reset_postdata();
		}

		return rest_ensure_response( array(
			'wines'      => $wines,
			'total'      => $query->found_posts,
			'total_pages' => $query->max_num_pages,
		) );
	}

	/**
	 * Get single wine.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_wine( $request ) {
		$id = $request->get_param( 'id' );
		$post = get_post( $id );

		if ( ! $post || 'cl_wine_product' !== $post->post_type ) {
			return new WP_Error( 'wine_not_found', __( 'Wine not found.', 'casa-larga-orderport-bridge' ), array( 'status' => 404 ) );
		}

		$wine_data = $this->format_wine_data( $post, true );
		return rest_ensure_response( $wine_data );
	}

	/**
	 * Get categories.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_categories( $request ) {
		$categories = get_posts( array(
			'post_type'      => 'cl_wine_category',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		) );

		$formatted_categories = array();
		foreach ( $categories as $category ) {
			$formatted_categories[] = array(
				'id'          => $category->ID,
				'name'        => get_the_title( $category ),
				'slug'        => $category->post_name,
				'description' => $category->post_content,
				'url'         => get_permalink( $category ),
			);
		}

		return rest_ensure_response( $formatted_categories );
	}

	/**
	 * Trigger manual sync.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function trigger_sync( $request ) {
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'casa-larga-orderport-bridge' ), array( 'status' => 403 ) );
		}

		$api_client = new CL_API_Client();
		$sync = new CL_Product_Sync( $api_client );
		$result = $sync->sync_catalog();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Submit custom label order.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function submit_custom_label( $request ) {
		// Verify nonce for security
		$nonce = $request->get_header('X-WP-Nonce');
		if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
			return new WP_Error('invalid_nonce', 'Security verification failed', array('status' => 403));
		}
		$design_image = $request->get_file_params()['design_image'] ?? null;
		$design_json = $request->get_param( 'design_json' );
		$wine_opsku = $request->get_param( 'wine_opsku' );
		$quantity = $request->get_param( 'quantity' );
		$customer_email = $request->get_param( 'customer_email' );
		$customer_name = $request->get_param( 'customer_name' );
		$customer_phone = $request->get_param( 'customer_phone' );

		// Validate inputs
		$wine_opsku = sanitize_text_field( $wine_opsku );
		$quantity = absint( $quantity );
		$customer_email = sanitize_email( $customer_email );
		$customer_name = sanitize_text_field( $customer_name );
		$customer_phone = sanitize_text_field( $customer_phone );

		if ( empty( $wine_opsku ) || empty( $quantity ) || empty( $customer_email ) || empty( $customer_name ) ) {
			return new WP_Error( 'missing_fields', __( 'Required fields are missing.', 'casa-larga-orderport-bridge' ), array( 'status' => 400 ) );
		}

		// Upload design image
		$attachment_id = null;
		if ( $design_image && isset( $design_image['tmp_name'] ) ) {
		// Validate file type
		$allowed_types = array('image/png', 'image/jpeg', 'image/jpg');
		if (!isset($design_image['type']) || !in_array($design_image['type'], $allowed_types, true)) {
			return new WP_Error('invalid_file_type', 'Only PNG and JPEG images are allowed', array('status' => 400));
		}
		
		// Validate file size (5MB max)
		if (!isset($design_image['size']) || $design_image['size'] > 5 * 1024 * 1024) {
			return new WP_Error('file_too_large', 'File must be under 5MB', array('status' => 400));
		}
		
		// Validate it's actually an image
		if (!isset($design_image['tmp_name']) || !getimagesize($design_image['tmp_name'])) {
			return new WP_Error('invalid_image', 'File is not a valid image', array('status' => 400));
		}
			$upload = wp_handle_upload( $design_image, array( 'test_form' => false ) );
			if ( ! isset( $upload['error'] ) ) {
				$attachment_id = wp_insert_attachment(
					array(
						'post_mime_type' => $upload['type'],
						'post_title'     => 'Custom Label Design - ' . $customer_name,
						'post_content'   => '',
						'post_status'    => 'private',
					),
					$upload['file']
				);
			}
		}

		// Send notification emails
		$this->send_custom_label_emails( $customer_name, $customer_email, $wine_opsku, $quantity, $attachment_id );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Custom label order submitted successfully.', 'casa-larga-orderport-bridge' ),
		) );
	}

	/**
	 * Format wine data for API response.
	 *
	 * @param WP_Post $post Post object.
	 * @param bool    $detailed Whether to include detailed information.
	 * @return array
	 */
	private function format_wine_data( $post, $detailed = false ) {
		$data = array(
			'id'                => $post->ID,
			'title'             => get_the_title( $post ),
			'excerpt'           => get_the_excerpt( $post ),
			'content'           => $post->post_content,
			'url'               => get_permalink( $post ),
			'featured_image_url' => get_the_post_thumbnail_url( $post, 'large' ),
			'orderport_sku'     => get_post_meta( $post->ID, 'orderport_sku', true ),
			'retail_price'      => get_post_meta( $post->ID, 'retail_price', true ),
			'sale_price'        => get_post_meta( $post->ID, 'sale_price', true ),
			'vintage'           => get_post_meta( $post->ID, 'spec_vintage', true ),
			'varietal'          => get_post_meta( $post->ID, 'spec_varietal', true ),
		);

		if ( $detailed ) {
			$data['specs'] = array();
			$meta_keys = array( 'vintage', 'varietal', 'alcohol', 'volume', 'tasting_notes', 'appellation' );
			foreach ( $meta_keys as $key ) {
				$value = get_post_meta( $post->ID, 'spec_' . $key, true );
				if ( $value ) {
					$data['specs'][ $key ] = $value;
				}
			}
		}

		return $data;
	}

	/**
	 * Send custom label order emails.
	 *
	 * @param string $customer_name Customer name.
	 * @param string $customer_email Customer email.
	 * @param string $wine_opsku Wine SKU.
	 * @param int    $quantity Quantity.
	 * @param int    $attachment_id Design image attachment ID.
	 */
	private function send_custom_label_emails( $customer_name, $customer_email, $wine_opsku, $quantity, $attachment_id ) {
		$admin_email = get_option( 'admin_email' );
		$subject = __( 'New Custom Wine Label Order', 'casa-larga-orderport-bridge' );
		
		$message = sprintf(
			__( 'New custom label order received from %s (%s)', 'casa-larga-orderport-bridge' ),
			$customer_name,
			$customer_email
		) . "\n\n";
		$message .= sprintf( __( 'Wine SKU: %s', 'casa-larga-orderport-bridge' ), $wine_opsku ) . "\n";
		$message .= sprintf( __( 'Quantity: %d bottles', 'casa-larga-orderport-bridge' ), $quantity ) . "\n";

		wp_mail( $admin_email, $subject, $message );

		// Send confirmation to customer
		$customer_subject = __( 'Your Custom Wine Label Order Confirmation', 'casa-larga-orderport-bridge' );
		$customer_message = sprintf(
			__( 'Dear %s,', 'casa-larga-orderport-bridge' ),
			$customer_name
		) . "\n\n";
		$customer_message .= __( 'Thank you for your custom wine label order!', 'casa-larga-orderport-bridge' ) . "\n\n";
		$customer_message .= sprintf( __( 'Wine SKU: %s', 'casa-larga-orderport-bridge' ), $wine_opsku ) . "\n";
		$customer_message .= sprintf( __( 'Quantity: %d bottles', 'casa-larga-orderport-bridge' ), $quantity ) . "\n\n";
		$customer_message .= __( 'We will contact you within 1-2 business days to confirm your order.', 'casa-larga-orderport-bridge' ) . "\n\n";
		$customer_message .= __( 'Questions? Contact us at: info@casalarga.com', 'casa-larga-orderport-bridge' );

		wp_mail( $customer_email, $customer_subject, $customer_message );
	}
}

endif;
