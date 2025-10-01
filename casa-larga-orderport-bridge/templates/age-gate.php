<?php
/**
 * Age verification modal template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="cl-age-gate-modal" style="display:none;">
	<div class="cl-age-gate-overlay"></div>
	<div class="cl-age-gate-content">
		<h2><?php esc_html_e( 'Age Verification Required', 'casa-larga-orderport-bridge' ); ?></h2>
		<p><?php esc_html_e( 'You must be 21 years or older to enter this site.', 'casa-larga-orderport-bridge' ); ?></p>
		<div class="cl-age-gate-buttons">
			<button id="cl-age-yes" class="button button-primary"><?php esc_html_e( 'I am 21 or older', 'casa-larga-orderport-bridge' ); ?></button>
			<button id="cl-age-no" class="button"><?php esc_html_e( 'I am under 21', 'casa-larga-orderport-bridge' ); ?></button>
		</div>
	</div>
</div>
