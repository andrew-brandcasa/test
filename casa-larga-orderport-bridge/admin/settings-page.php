<?php
/**
 * Settings page template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
