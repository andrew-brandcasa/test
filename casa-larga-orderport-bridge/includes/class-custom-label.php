<?php
/**
 * CL_Custom_Label - Custom label builder functionality
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CL_Custom_Label' ) ) :

class CL_Custom_Label {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'cl_custom_label_builder', array( $this, 'render_builder' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue assets for custom label builder.
	 */
	public function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cl_custom_label_builder' ) ) {
			wp_enqueue_script( 'fabricjs', 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js', array(), '5.3.0', true );
			wp_enqueue_script( 'cl-label-builder', CL_ORDERPORT_URL . 'assets/js/label-builder.js', array( 'jquery', 'fabricjs' ), CL_ORDERPORT_VERSION, true );
			wp_enqueue_style( 'cl-label-builder', CL_ORDERPORT_URL . 'assets/css/label-builder.css', array(), CL_ORDERPORT_VERSION );

			wp_localize_script( 'cl-label-builder', 'clLabelBuilder', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'resturl' => rest_url( 'casa-larga/v1/' ),
				'nonce'   => wp_create_nonce( 'cl_label_nonce' ),
			) );
		}
	}

	/**
	 * Render custom label builder shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_builder( $atts ) {
		$wines = get_posts( array(
			'post_type'      => 'cl_wine_product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		ob_start();
		$template = trailingslashit( CL_ORDERPORT_PATH ) . 'templates/custom-label-builder.php';
		if ( file_exists( $template ) ) {
			include $template;
		} else {
			$this->render_default_builder( $wines );
		}
		return ob_get_clean();
	}

	/**
	 * Render default builder template.
	 *
	 * @param array $wines Available wines.
	 */
	private function render_default_builder( $wines ) {
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
								?>
								<option value="<?php echo esc_attr( $opsku ); ?>" data-price="<?php echo esc_attr( $price ); ?>">
									<?php echo esc_html( get_the_title( $wine ) ); ?> - $<?php echo esc_html( $price ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div id="selected-wine-info" style="margin-top: 10px;"></div>
					</div>

					<div class="cl-design-tools">
						<h3><?php esc_html_e( 'Design Tools', 'casa-larga-orderport-bridge' ); ?></h3>
						<button type="button" id="add-text"><?php esc_html_e( 'Add Text', 'casa-larga-orderport-bridge' ); ?></button>
						<button type="button" id="add-image"><?php esc_html_e( 'Add Image', 'casa-larga-orderport-bridge' ); ?></button>
						<input type="file" id="image-upload" accept="image/*" style="display: none;">
						
						<div class="cl-font-controls" style="margin-top: 15px;">
							<label for="font-family"><?php esc_html_e( 'Font:', 'casa-larga-orderport-bridge' ); ?></label>
							<select id="font-family">
								<option value="Arial">Arial</option>
								<option value="Georgia">Georgia</option>
								<option value="Times New Roman">Times New Roman</option>
								<option value="Courier New">Courier New</option>
								<option value="Verdana">Verdana</option>
							</select>
						</div>
						
						<div class="cl-size-controls" style="margin-top: 10px;">
							<label for="font-size"><?php esc_html_e( 'Size:', 'casa-larga-orderport-bridge' ); ?></label>
							<input type="range" id="font-size" min="12" max="72" value="24">
							<span id="font-size-value">24px</span>
						</div>
						
						<div class="cl-color-controls" style="margin-top: 10px;">
							<label for="text-color"><?php esc_html_e( 'Color:', 'casa-larga-orderport-bridge' ); ?></label>
							<input type="color" id="text-color" value="#000000">
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
				</div>

				<div class="cl-canvas-container">
					<canvas id="label-canvas" width="500" height="700"></canvas>
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
					
					<button type="submit" class="cl-submit-order"><?php esc_html_e( 'Submit Order', 'casa-larga-orderport-bridge' ); ?></button>
				</form>
			</div>
		</div>
		<?php
	}
}

endif;
