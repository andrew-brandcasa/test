<?php
/**
 * CL_API_Client - OrderPort API client
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CL_API_Client' ) ) :

class CL_API_Client {
	/**
	 * Base API URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://wwwapps.orderport.net';

	/**
	 * Webstore hostname.
	 *
	 * @var string
	 */
	private $hostname = 'casalarga.orderport.net';

	/**
	 * Constructor: load credentials/hostname from options or defaults.
	 */
	public function __construct() {
		$hostname_option = get_option( 'cl_orderport_hostname' );
		if ( ! empty( $hostname_option ) ) {
			$this->hostname = sanitize_text_field( $hostname_option );
		}
	}

	/**
	 * Build Authorization header. MUST match required format exactly.
	 *
	 * @return string
	 */
	private function get_auth_header() {
		$auth = array(
			'ClientId' => (int) get_option( 'cl_orderport_client_id' ),
			'ApiKey' => get_option( 'cl_orderport_api_key' ),
			'ApiToken' => get_option( 'cl_orderport_api_token' ), // STORED AS PLAIN TEXT
		);
		$json = json_encode( $auth );
		$base64 = base64_encode( $json );
		return 'Bearer ' . $base64;
	}

	/**
	 * Rate limiter using transient: max 10 calls per minute.
	 *
	 * @return true|WP_Error
	 */
	private function enforce_rate_limit() {
		$key = 'cl_api_calls_count';
		$count = get_transient($key);
		
		if (false === $count) {
			set_transient($key, 1, 60);
			return true;
		}
		
		if ($count >= 10) {
			return new WP_Error('cl_rate_limited', __('API rate limit reached', 'casa-larga-orderport-bridge'));
		}
		
		set_transient($key, $count + 1, 60);
		return true;
	}

	/**
	 * Make an HTTP request using WordPress HTTP API.
	 *
	 * @param string $endpoint Endpoint path like '/api/...', or full URL.
	 * @param string $method   HTTP method.
	 * @param array|null $body Optional body.
	 * @param int $timeout     Timeout seconds.
	 * @return array|WP_Error  Decoded JSON array or WP_Error.
	 */
	public function make_request( $endpoint, $method = 'GET', $body = null, $timeout = 30 ) {
		$rate_ok = $this->enforce_rate_limit();
		if ( is_wp_error( $rate_ok ) ) {
			return $rate_ok;
		}

		$endpoint = is_string( $endpoint ) ? $endpoint : '';
		if ( 0 === strpos( $endpoint, 'http' ) ) {
			$url = $endpoint;
		} else {
			$url = trailingslashit( $this->api_url ) . ltrim( $endpoint, '/' );
		}

		$args = array(
			'method'  => $method,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => $this->get_auth_header(),
				'api-version'   => '1.0.0',
			),
			'timeout' => absint( $timeout ),
		);
		if ( null !== $body ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );
		$response_code = 0;
		if ( is_wp_error( $response ) ) {
			$this->log_request( $url, $method, $response_code, 'WP_Error: ' . $response->get_error_message() );
			return $response;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );
		$body_raw = wp_remote_retrieve_body( $response );

		if ( $response_code >= 200 && $response_code < 300 ) {
			$decoded = json_decode( $body_raw, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$this->log_request( $url, $method, $response_code, 'Malformed JSON' );
				return new WP_Error( 'cl_malformed_json', __( 'Malformed JSON response from OrderPort API.', 'casa-larga-orderport-bridge' ) );
			}
			$this->log_request( $url, $method, $response_code, 'OK' );
			return $decoded;
		}

		if ( 401 === $response_code || 403 === $response_code ) {
			$this->log_request( $url, $method, $response_code, 'Invalid credentials' );
			return new WP_Error( 'cl_invalid_credentials', __( 'Invalid API credentials.', 'casa-larga-orderport-bridge' ) );
		}

		if ( $response_code >= 500 ) {
			$this->log_request( $url, $method, $response_code, 'Server error' );
			return new WP_Error( 'cl_server_error', __( 'OrderPort server error.', 'casa-larga-orderport-bridge' ) );
		}

		$this->log_request( $url, $method, $response_code, 'HTTP ' . $response_code );
		return new WP_Error( 'cl_http_error', sprintf( __( 'Unexpected HTTP status: %d', 'casa-larga-orderport-bridge' ), $response_code ) );
	}

	/**
	 * Get webstore catalog with optional customer class filter.
	 * Caches response for 1 hour.
	 *
	 * @param int|null $customer_class_id Optional class id.
	 * @return array|WP_Error
	 */
	public function get_catalog( $customer_class_id = null ) {
		$endpoint = '/api/catalog/webstore-catalog/';
		$query_args = array( 'hostname' => $this->hostname );
		if ( ! empty( $customer_class_id ) ) {
			$query_args['customerclassid'] = absint( $customer_class_id );
		}
		$url = add_query_arg( $query_args, $this->api_url . $endpoint );

		$transient_key = 'cl_catalog_' . md5( $url );
		$cached = get_transient( $transient_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$result = $this->make_request( $url, 'GET', null, 30 );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		// Ensure structure.
		if ( ! is_array( $result ) ) {
			$result = array();
		}
		if ( ! isset( $result['Groups'] ) ) {
			$result['Groups'] = array();
		}
		if ( ! isset( $result['Products'] ) ) {
			$result['Products'] = array();
		}

		set_transient( $transient_key, $result, HOUR_IN_SECONDS );
		return $result;
	}

	/**
	 * Simple connection test wrapper.
	 *
	 * @return true|WP_Error
	 */
	public function test_connection() {
		$response = $this->get_catalog();
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		return true;
	}

	/**
	 * Log request outcome to sync log table.
	 *
	 * @param string $endpoint
	 * @param string $method
	 * @param int    $response_code
	 * @param string $message
	 * @return void
	 */
	public function log_request( $endpoint, $method, $response_code, $message ) {
		global $wpdb;
		$table = $wpdb->prefix . 'orderport_sync_log';
		$data  = array(
			'sync_date' => current_time( 'mysql' ),
			'status'    => ( $response_code >= 200 && $response_code < 300 ) ? 'success' : 'error',
			'message'   => sprintf( '%s %s - %s', sanitize_text_field( $method ), esc_url_raw( $endpoint ), sanitize_text_field( $message ) ),
			'products_synced'   => 0,
			'categories_synced' => 0,
		);
		$format = array( '%s', '%s', '%s', '%d', '%d' );
		// Use $wpdb->insert which prepares internally.
		$wpdb->insert( $table, $data, $format );
	}
}

endif;
