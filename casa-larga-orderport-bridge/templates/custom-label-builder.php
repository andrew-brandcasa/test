<?php
/**
 * Custom label builder template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $wines ) ) {
	echo '<p>' . esc_html__( 'No wines available for custom labels.', 'casa-larga-orderport-bridge' ) . '</p>';
	return;
}
?>
<div class="cl-custom-label-builder">
	<h2><?php esc_html_e( 'Custom Wine Label Builder', 'casa-larga-orderport-bridge' ); ?></h2>
	
	<div class="cl-builder-container">
		<div class="cl-builder-sidebar">
			<div class="cl-wine-selection">
				<h3><?php esc_html_e( 'Select Wine', 'casa-larga-orderport-bridge' ); ?></h3>
				<select name="wine_opsku" id="wine-opsku" required>
					<option value=""><?php esc_html_e( 'Choose a wine...', 'casa-larga-orderport-bridge' ); ?></option>
					<?php foreach ( $wines as $wine ) : ?>
						<?php
						$opsku = get_post_meta( $wine->ID, 'orderport_sku', true );
						$price = get_post_meta( $wine->ID, 'retail_price', true );
						$image = get_the_post_thumbnail_url( $wine, 'medium' );
						?>
						<option value="<?php echo esc_attr( $opsku ); ?>" 
								data-price="<?php echo esc_attr( $price ); ?>"
								data-image="<?php echo esc_attr( $image ); ?>">
							<?php echo esc_html( get_the_title( $wine ) ); ?> - $<?php echo esc_html( number_format( $price, 2 ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<div id="selected-wine-info" style="margin-top: 10px;"></div>
			</div>

			<div class="cl-design-tools">
				<h3><?php esc_html_e( 'Design Tools', 'casa-larga-orderport-bridge' ); ?></h3>
				<button type="button" id="add-text" class="cl-tool-button">
					<?php esc_html_e( 'Add Text', 'casa-larga-orderport-bridge' ); ?>
				</button>
				<button type="button" id="add-image" class="cl-tool-button">
					<?php esc_html_e( 'Add Image', 'casa-larga-orderport-bridge' ); ?>
				</button>
				<input type="file" id="image-upload" accept="image/*" style="display: none;">
				
				<div class="cl-font-controls" style="margin-top: 15px;">
					<label for="font-family"><?php esc_html_e( 'Font:', 'casa-larga-orderport-bridge' ); ?></label>
					<select id="font-family">
						<option value="Arial">Arial</option>
						<option value="Georgia">Georgia</option>
						<option value="Times New Roman">Times New Roman</option>
						<option value="Courier New">Courier New</option>
						<option value="Verdana">Verdana</option>
						<option value="Helvetica">Helvetica</option>
						<option value="Impact">Impact</option>
						<option value="Comic Sans MS">Comic Sans MS</option>
					</select>
				</div>
				
				<div class="cl-size-controls" style="margin-top: 10px;">
					<label for="font-size"><?php esc_html_e( 'Size:', 'casa-larga-orderport-bridge' ); ?></label>
					<input type="range" id="font-size" min="12" max="72" value="24">
					<span id="font-size-value">24px</span>
				</div>
				
				<div class="cl-color-controls" style="margin-top: 10px;">
					<label for="text-color"><?php esc_html_e( 'Text Color:', 'casa-larga-orderport-bridge' ); ?></label>
					<input type="color" id="text-color" value="#000000">
				</div>
				
				<div class="cl-alignment-controls" style="margin-top: 10px;">
					<label><?php esc_html_e( 'Alignment:', 'casa-larga-orderport-bridge' ); ?></label>
					<div class="cl-alignment-buttons">
						<button type="button" id="align-left" class="cl-align-btn" data-align="left">L</button>
						<button type="button" id="align-center" class="cl-align-btn" data-align="center">C</button>
						<button type="button" id="align-right" class="cl-align-btn" data-align="right">R</button>
					</div>
				</div>
				
				<div class="cl-background-controls" style="margin-top: 10px;">
					<label for="background-color"><?php esc_html_e( 'Background:', 'casa-larga-orderport-bridge' ); ?></label>
					<input type="color" id="background-color" value="#ffffff">
				</div>
			</div>

			<div class="cl-pricing">
				<h3><?php esc_html_e( 'Pricing', 'casa-larga-orderport-bridge' ); ?></h3>
				<div class="cl-pricing-tiers">
					<p><strong><?php esc_html_e( 'Label Pricing:', 'casa-larga-orderport-bridge' ); ?></strong></p>
					<ul>
						<li>1-11 bottles: $5.00/label</li>
						<li>12-23 bottles: $4.00/label</li>
						<li>24-47 bottles: $3.50/label</li>
						<li>48-59 bottles: $3.00/label</li>
						<li>60+ bottles: $2.50/label</li>
					</ul>
				</div>
				
				<div class="cl-quantity">
					<label for="quantity"><?php esc_html_e( 'Quantity:', 'casa-larga-orderport-bridge' ); ?></label>
					<input type="number" id="quantity" min="1" value="1">
				</div>
				
				<div class="cl-total">
					<p><strong><?php esc_html_e( 'Total:', 'casa-larga-orderport-bridge' ); ?> $<span id="total-price">0.00</span></strong></p>
				</div>
			</div>

			<div class="cl-save-load">
				<h3><?php esc_html_e( 'Save/Load Design', 'casa-larga-orderport-bridge' ); ?></h3>
				<button type="button" id="save-design" class="cl-tool-button">
					<?php esc_html_e( 'Save Design', 'casa-larga-orderport-bridge' ); ?>
				</button>
				<button type="button" id="load-design" class="cl-tool-button">
					<?php esc_html_e( 'Load Design', 'casa-larga-orderport-bridge' ); ?>
				</button>
				<button type="button" id="clear-design" class="cl-tool-button">
					<?php esc_html_e( 'Clear All', 'casa-larga-orderport-bridge' ); ?>
				</button>
			</div>
		</div>

		<div class="cl-canvas-container">
			<div class="cl-canvas-header">
				<h3><?php esc_html_e( 'Label Design', 'casa-larga-orderport-bridge' ); ?></h3>
				<p class="cl-canvas-help"><?php esc_html_e( 'Click and drag to move objects. Use the tools on the left to add text and images.', 'casa-larga-orderport-bridge' ); ?></p>
			</div>
			<canvas id="label-canvas" width="500" height="700"></canvas>
			<div class="cl-canvas-actions">
				<button type="button" id="zoom-in" class="cl-canvas-btn">+</button>
				<button type="button" id="zoom-out" class="cl-canvas-btn">-</button>
				<button type="button" id="reset-zoom" class="cl-canvas-btn">Reset</button>
			</div>
		</div>
	</div>

	<div class="cl-order-form">
		<h3><?php esc_html_e( 'Order Information', 'casa-larga-orderport-bridge' ); ?></h3>
		<form id="custom-label-order-form">
			<div class="cl-form-row">
				<label for="customer-name"><?php esc_html_e( 'Name *', 'casa-larga-orderport-bridge' ); ?></label>
				<input type="text" id="customer-name" name="customer_name" required>
			</div>
			
			<div class="cl-form-row">
				<label for="customer-email"><?php esc_html_e( 'Email *', 'casa-larga-orderport-bridge' ); ?></label>
				<input type="email" id="customer-email" name="customer_email" required>
			</div>
			
			<div class="cl-form-row">
				<label for="customer-phone"><?php esc_html_e( 'Phone', 'casa-larga-orderport-bridge' ); ?></label>
				<input type="tel" id="customer-phone" name="customer_phone">
			</div>
			
			<div class="cl-form-row">
				<label for="special-instructions"><?php esc_html_e( 'Special Instructions', 'casa-larga-orderport-bridge' ); ?></label>
				<textarea id="special-instructions" name="special_instructions" rows="3"></textarea>
			</div>
			
			<button type="submit" class="cl-submit-order">
				<?php esc_html_e( 'Submit Order', 'casa-larga-orderport-bridge' ); ?>
			</button>
		</form>
	</div>
</div>
