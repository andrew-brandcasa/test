<?php
/**
 * Sync dashboard template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Sync Dashboard', 'casa-larga-orderport-bridge' ); ?></h1>
	
	<div class="cl-sync-stats">
		<div class="cl-stat-card">
			<div class="cl-stat-number"><?php echo esc_html( $total_products ); ?></div>
			<div class="cl-stat-label"><?php esc_html_e( 'Total Products', 'casa-larga-orderport-bridge' ); ?></div>
		</div>
		<div class="cl-stat-card">
			<div class="cl-stat-number"><?php echo esc_html( $total_categories ); ?></div>
			<div class="cl-stat-label"><?php esc_html_e( 'Total Categories', 'casa-larga-orderport-bridge' ); ?></div>
		</div>
		<div class="cl-stat-card">
			<div class="cl-stat-number"><?php echo esc_html( $last_sync_time ); ?></div>
			<div class="cl-stat-label"><?php esc_html_e( 'Last Sync', 'casa-larga-orderport-bridge' ); ?></div>
		</div>
		<div class="cl-stat-card">
			<div class="cl-stat-number"><?php echo esc_html( $sync_status ); ?></div>
			<div class="cl-stat-label"><?php esc_html_e( 'Status', 'casa-larga-orderport-bridge' ); ?></div>
		</div>
	</div>

	<div class="cl-sync-actions">
		<h2><?php esc_html_e( 'Sync Actions', 'casa-larga-orderport-bridge' ); ?></h2>
		<form method="post" id="cl-sync-form">
			<?php wp_nonce_field( 'cl_sync_now' ); ?>
			<input type="submit" name="cl_sync_now" class="button button-primary cl-sync-button" 
				   value="<?php esc_attr_e( 'Start Sync', 'casa-larga-orderport-bridge' ); ?>" />
		</form>
		
		<div id="cl-sync-progress" class="cl-sync-progress" style="display: none;">
			<div class="cl-sync-progress-bar"></div>
			<div class="cl-sync-progress-text"><?php esc_html_e( 'Syncing...', 'casa-larga-orderport-bridge' ); ?></div>
		</div>
	</div>

	<div class="cl-recent-logs">
		<h2><?php esc_html_e( 'Recent Sync Logs', 'casa-larga-orderport-bridge' ); ?></h2>
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
				<?php if ( empty( $recent_logs ) ) : ?>
					<tr>
						<td colspan="5"><?php esc_html_e( 'No sync logs found.', 'casa-larga-orderport-bridge' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $recent_logs as $log ) : ?>
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
</div>
