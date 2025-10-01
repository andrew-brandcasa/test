<?php
/**
 * Wine categories grid template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $categories ) ) {
	return;
}
?>
<div class="cl-categories-grid">
	<?php foreach ( $categories as $category ) : ?>
		<div class="cl-category-item">
			<?php if ( has_post_thumbnail( $category ) ) : ?>
				<div class="cl-category-image">
					<?php echo get_the_post_thumbnail( $category, 'medium' ); ?>
				</div>
			<?php endif; ?>
			
			<div class="cl-category-content">
				<h3 class="cl-category-title">
					<a href="<?php echo esc_url( get_permalink( $category ) ); ?>">
						<?php echo esc_html( get_the_title( $category ) ); ?>
					</a>
				</h3>
				
				<?php if ( $category->post_content ) : ?>
					<div class="cl-category-description">
						<?php echo wp_kses_post( $category->post_content ); ?>
					</div>
				<?php endif; ?>
				
				<div class="cl-category-meta">
					<?php
					$product_count = get_posts( array(
						'post_type'      => 'cl_wine_product',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'meta_query'     => array(
							array(
								'key'   => 'orderport_group_id',
								'value' => get_post_meta( $category->ID, 'orderport_group_id', true ),
							),
						),
					) );
					?>
					<span class="cl-product-count">
						<?php echo esc_html( count( $product_count ) ); ?> 
						<?php esc_html_e( 'products', 'casa-larga-orderport-bridge' ); ?>
					</span>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
