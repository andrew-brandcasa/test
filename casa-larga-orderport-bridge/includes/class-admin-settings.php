<?php
/**
 * CL_Admin_Settings - Admin interface for OrderPort Bridge
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CL_Admin_Settings' ) ) :

class CL_Admin_Settings {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_cl_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_cl_sync_now', array( $this, 'ajax_sync_now' ) );
	}

	/**
	 * Add admin menu pages.
	 */
	public function add_menu_pages() {
		// Main menu page
		add_menu_page(
			__( 'Casa Larga OrderPort', 'casa-larga-orderport-bridge' ),
			__( 'OrderPort Sync', 'casa-larga-orderport-bridge' ),
			'manage_options',
			'cl-orderport',
			array( $this, 'render_dashboard' ),
			'dashicons-update-alt',
			58
		);

		// Settings submenu
		add_submenu_page(
			'cl-orderport',
			__( 'OrderPort Settings', 'casa-larga-orderport-bridge' ),
			__( 'Settings', 'casa-larga-orderport-bridge' ),
			'manage_options',
			'cl-orderport-settings',
			array( $this, 'render_settings' )
		);

		// Logs submenu
		add_submenu_page(
			'cl-orderport',
			__( 'Sync Logs', 'casa-larga-orderport-bridge' ),
			__( 'Logs', 'casa-larga-orderport-bridge' ),
			'manage_options',
			'cl-orderport-logs',
			array( $this, 'render_logs' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting( 'cl_orderport_settings', 'cl_orderport_client_id', array(
			'type' => 'integer',
			'sanitize_callback' => 'absint',
		) );
		register_setting( 'cl_orderport_settings', 'cl_orderport_api_key', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		register_setting( 'cl_orderport_settings', 'cl_orderport_api_token', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		register_setting( 'cl_orderport_settings', 'cl_orderport_hostname', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		register_setting( 'cl_orderport_settings', 'cl_sync_frequency', array(
			'type' => 'string',
			'default' => 'hourly',
			'sanitize_callback' => 'sanitize_text_field',
		) );
	}

	/**
	 * Render dashboard page.
	 */
	public function render_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'casa-larga-orderport-bridge' ) );
		}

		// Handle manual sync
		if ( isset( $_POST['cl_sync_now'] ) && check_admin_referer( 'cl_sync_now' ) ) {
			$api_client = new CL_API_Client();
			$sync = new CL_Product_Sync( $api_client );
			$result = $sync->sync_catalog();
			
			if ( is_wp_error( $result ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success"><p>' . esc_html( $result['message'] ) . '</p></div>';
			}
		}

		$last_sync = get_option( 'cl_last_sync_completed', 0 );
		$last_sync_time = $last_sync ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sync ) : __( 'Never', 'casa-larga-orderport-bridge' );

		// Get latest sync stats
		global $wpdb;
		$log_table = $wpdb->prefix . 'orderport_sync_log';
		$latest_log = $wpdb->get_row( "SELECT * FROM {$log_table} ORDER BY sync_date DESC LIMIT 1" );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Casa Larga OrderPort Dashboard', 'casa-larga-orderport-bridge' ); ?></h1>
			
			<div class="cl-dashboard-widgets">
				<div class="cl-widget">
					<h2><?php esc_html_e( 'Last Sync', 'casa-larga-orderport-bridge' ); ?></h2>
					<p><strong><?php esc_html_e( 'Time:', 'casa-larga-orderport-bridge' ); ?></strong> <?php echo esc_html( $last_sync_time ); ?></p>
					<?php if ( $latest_log ) : ?>
						<p><strong><?php esc_html_e( 'Status:', 'casa-larga-orderport-bridge' ); ?></strong> 
							<span class="cl-status-<?php echo esc_attr( $latest_log->status ); ?>">
								<?php echo esc_html( ucfirst( $latest_log->status ) ); ?>
							</span>
						</p>
						<p><strong><?php esc_html_e( 'Products Synced:', 'casa-larga-orderport-bridge' ); ?></strong> <?php echo esc_html( $latest_log->products_synced ); ?></p>
						<p><strong><?php esc_html_e( 'Categories Synced:', 'casa-larga-orderport-bridge' ); ?></strong> <?php echo esc_html( $latest_log->categories_synced ); ?></p>
						<?php if ( $latest_log->message ) : ?>
							<p><strong><?php esc_html_e( 'Message:', 'casa-larga-orderport-bridge' ); ?></strong> <?php echo esc_html( $latest_log->message ); ?></p>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<div class="cl-widget">
					<h2><?php esc_html_e( 'Quick Actions', 'casa-larga-orderport-bridge' ); ?></h2>
					<form method="post" style="display: inline;">
						<?php wp_nonce_field( 'cl_sync_now' ); ?>
						<input type="submit" name="cl_sync_now" class="button button-primary" value="<?php esc_attr_e( 'Sync Now', 'casa-larga-orderport-bridge' ); ?>" />
					</form>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-orderport-settings' ) ); ?>" class="button">
						<?php esc_html_e( 'Settings', 'casa-larga-orderport-bridge' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cl-orderport-logs' ) ); ?>" class="button">
						<?php esc_html_e( 'View Logs', 'casa-larga-orderport-bridge' ); ?>
					</a>
				</div>
			</div>

			<div class="cl-widget">
				<h2><?php esc_html_e( 'Connection Test', 'casa-larga-orderport-bridge' ); ?></h2>
				<button type="button" id="cl-test-connection" class="button"><?php esc_html_e( 'Test Connection', 'casa-larga-orderport-bridge' ); ?></button>
				<div id="cl-test-result" style="margin-top: 10px;"></div>
			</div>
		</div>

		<style>
		.cl-dashboard-widgets {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 20px;
			margin: 20px 0;
		}
		.cl-widget {
			background: #fff;
			border: 1px solid #ccd0d4;
			padding: 20px;
			border-radius: 4px;
		}
		.cl-widget h2 {
			margin-top: 0;
		}
		.cl-status-success { color: #46b450; }
		.cl-status-error { color: #dc3232; }
		.cl-status-running { color: #ffb900; }
		</style>

		<script>
		jQuery(document).ready(function($) {
			$('#cl-test-connection').on('click', function() {
				var button = $(this);
				var result = $('#cl-test-result');
				
				button.prop('disabled', true).text('<?php esc_js( __( 'Testing...', 'casa-larga-orderport-bridge' ) ); ?>');
				result.html('');
				
				$.post(ajaxurl, {
					action: 'cl_test_connection',
					nonce: '<?php echo esc_js( wp_create_nonce( 'cl_test_connection' ) ); ?>'
				}, function(response) {
					if (response.success) {
						result.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
					} else {
						result.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
					}
				}).always(function() {
					button.prop('disabled', false).text('<?php esc_js( __( 'Test Connection', 'casa-larga-orderport-bridge' ) ); ?>');
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Render settings page.
	 */
	public function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'casa-larga-orderport-bridge' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'OrderPort Settings', 'casa-larga-orderport-bridge' ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'cl_orderport_settings' );
				do_settings_sections( 'cl_orderport_settings' );
				?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="cl_orderport_client_id"><?php esc_html_e( 'Client ID', 'casa-larga-orderport-bridge' ); ?></label>
						</th>
						<td>
							<input type="number" id="cl_orderport_client_id" name="cl_orderport_client_id" 
								   value="<?php echo esc_attr( get_option( 'cl_orderport_client_id', 88884255 ) ); ?>" 
								   class="regular-text" />
							<p class="description"><?php esc_html_e( 'Your OrderPort Client ID', 'casa-larga-orderport-bridge' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cl_orderport_api_key"><?php esc_html_e( 'API Key', 'casa-larga-orderport-bridge' ); ?></label>
						</th>
						<td>
							<input type="text" id="cl_orderport_api_key" name="cl_orderport_api_key" 
								   value="<?php echo esc_attr( get_option( 'cl_orderport_api_key', '4b024d52-c688-4264-90c4-47c5abcdbdfb' ) ); ?>" 
								   class="regular-text" />
							<p class="description"><?php esc_html_e( 'Your OrderPort API Key', 'casa-larga-orderport-bridge' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cl_orderport_api_token"><?php esc_html_e( 'API Token', 'casa-larga-orderport-bridge' ); ?></label>
						</th>
						<td>
							<input type="password" id="cl_orderport_api_token" name="cl_orderport_api_token" 
								   value="<?php echo esc_attr( get_option( 'cl_orderport_api_token', 'BN3I09@bWyeKTICdU0P8Q5S-7kdXBBRS' ) ); ?>" 
								   class="regular-text" />
							<p class="description"><?php esc_html_e( 'Your OrderPort API Token (stored as plain text)', 'casa-larga-orderport-bridge' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cl_orderport_hostname"><?php esc_html_e( 'Hostname', 'casa-larga-orderport-bridge' ); ?></label>
						</th>
						<td>
							<input type="text" id="cl_orderport_hostname" name="cl_orderport_hostname" 
								   value="<?php echo esc_attr( get_option( 'cl_orderport_hostname', 'casalarga.orderport.net' ) ); ?>" 
								   class="regular-text" />
							<p class="description"><?php esc_html_e( 'Your OrderPort webstore hostname', 'casa-larga-orderport-bridge' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cl_sync_frequency"><?php esc_html_e( 'Sync Frequency', 'casa-larga-orderport-bridge' ); ?></label>
						</th>
						<td>
							<select id="cl_sync_frequency" name="cl_sync_frequency">
								<option value="hourly" <?php selected( get_option( 'cl_sync_frequency', 'hourly' ), 'hourly' ); ?>>
									<?php esc_html_e( 'Hourly', 'casa-larga-orderport-bridge' ); ?>
								</option>
								<option value="twicedaily" <?php selected( get_option( 'cl_sync_frequency', 'hourly' ), 'twicedaily' ); ?>>
									<?php esc_html_e( 'Twice Daily', 'casa-larga-orderport-bridge' ); ?>
								</option>
								<option value="daily" <?php selected( get_option( 'cl_sync_frequency', 'hourly' ), 'daily' ); ?>>
									<?php esc_html_e( 'Daily', 'casa-larga-orderport-bridge' ); ?>
								</option>
							</select>
							<p class="description"><?php esc_html_e( 'How often to sync with OrderPort', 'casa-larga-orderport-bridge' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<div style="margin-top: 30px;">
				<h3><?php esc_html_e( 'Connection Test', 'casa-larga-orderport-bridge' ); ?></h3>
				<button type="button" id="cl-test-connection-settings" class="button"><?php esc_html_e( 'Test Connection', 'casa-larga-orderport-bridge' ); ?></button>
				<div id="cl-test-result-settings" style="margin-top: 10px;"></div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#cl-test-connection-settings').on('click', function() {
				var button = $(this);
				var result = $('#cl-test-result-settings');
				
				button.prop('disabled', true).text('<?php esc_js( __( 'Testing...', 'casa-larga-orderport-bridge' ) ); ?>');
				result.html('');
				
				$.post(ajaxurl, {
					action: 'cl_test_connection',
					nonce: '<?php echo esc_js( wp_create_nonce( 'cl_test_connection' ) ); ?>'
				}, function(response) {
					if (response.success) {
						result.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
					} else {
						result.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
					}
				}).always(function() {
					button.prop('disabled', false).text('<?php esc_js( __( 'Test Connection', 'casa-larga-orderport-bridge' ) ); ?>');
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Render logs page.
	 */
	public function render_logs() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'casa-larga-orderport-bridge' ) );
		}

		// Handle clear old logs
		if ( isset( $_POST['cl_clear_old_logs'] ) && check_admin_referer( 'cl_clear_old_logs' ) ) {
			global $wpdb;
			$log_table = $wpdb->prefix . 'orderport_sync_log';
			$deleted = $wpdb->query( $wpdb->prepare( 
				"DELETE FROM {$log_table} WHERE sync_date < %s", 
				date( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			) );
			echo '<div class="notice notice-success"><p>' . sprintf( 
				esc_html__( 'Cleared %d old log entries.', 'casa-larga-orderport-bridge' ), 
				$deleted 
			) . '</p></div>';
		}

		global $wpdb;
		$log_table = $wpdb->prefix . 'orderport_sync_log';
		$logs = $wpdb->get_results( "SELECT * FROM {$log_table} ORDER BY sync_date DESC LIMIT 50" );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sync Logs', 'casa-larga-orderport-bridge' ); ?></h1>
			
			<form method="post" style="margin-bottom: 20px;">
				<?php wp_nonce_field( 'cl_clear_old_logs' ); ?>
				<input type="submit" name="cl_clear_old_logs" class="button" 
					   value="<?php esc_attr_e( 'Clear Old Logs (30+ days)', 'casa-larga-orderport-bridge' ); ?>" />
			</form>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'casa-larga-orderport-bridge' ); ?></th>
						<th><?php esc_html_e( 'Status', 'casa-larga-orderport-bridge' ); ?></th>
						<th><?php esc_html_e( 'Products', 'casa-larga-orderport-bridge' ); ?></th>
						<th><?php esc_html_e( 'Categories', 'casa-larga-orderport-bridge' ); ?></th>
						<th><?php esc_html_e( 'Message', 'casa-larga-orderport-bridge' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr>
							<td colspan="5"><?php esc_html_e( 'No logs found.', 'casa-larga-orderport-bridge' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->sync_date ) ) ); ?></td>
								<td>
									<span class="cl-status-<?php echo esc_attr( $log->status ); ?>">
										<?php echo esc_html( ucfirst( $log->status ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $log->products_synced ); ?></td>
								<td><?php echo esc_html( $log->categories_synced ); ?></td>
								<td><?php echo esc_html( $log->message ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<style>
		.cl-status-success { color: #46b450; font-weight: bold; }
		.cl-status-error { color: #dc3232; font-weight: bold; }
		.cl-status-running { color: #ffb900; font-weight: bold; }
		</style>
		<?php
	}

	/**
	 * AJAX handler for connection test.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'cl_test_connection', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$api_client = new CL_API_Client();
		$result = $api_client->test_connection();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( __( 'Connection successful!', 'casa-larga-orderport-bridge' ) );
		}
	}

	/**
	 * AJAX handler for manual sync.
	 */
	public function ajax_sync_now() {
		check_ajax_referer( 'cl_sync_now', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$api_client = new CL_API_Client();
		$sync = new CL_Product_Sync( $api_client );
		$result = $sync->sync_catalog();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( $result['message'] );
		}
	}
}

endif;
