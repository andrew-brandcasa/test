<?php
/**
 * Admin page template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Casa Larga OrderPort', 'casa-larga-orderport-bridge' ); ?></h1>
	
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
