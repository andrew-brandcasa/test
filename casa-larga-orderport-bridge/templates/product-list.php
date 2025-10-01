<?php
/**
 * Wine products list template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $wines->have_posts() ) {
	echo '<p>' . esc_html__( 'No wines found.', 'casa-larga-orderport-bridge' ) . '</p>';
	return;
}
?>
<div class="cl-wine-list">
	<div class="cl-wine-filters">
		<select id="wine-type-filter">
			<option value=""><?php esc_html_e( 'All Wine Types', 'casa-larga-orderport-bridge' ); ?></option>
			<?php
			$wine_types = get_terms( array(
				'taxonomy' => 'cl_wine_type',
				'hide_empty' => true,
			) );
			foreach ( $wine_types as $type ) :
				?>
				<option value="<?php echo esc_attr( $type->slug ); ?>">
					<?php echo esc_html( $type->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		
		<select id="price-filter">
			<option value=""><?php esc_html_e( 'All Prices', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="0-25"><?php esc_html_e( 'Under $25', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="25-50"><?php esc_html_e( '$25 - $50', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="50-100"><?php esc_html_e( '$50 - $100', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="100+"><?php esc_html_e( 'Over $100', 'casa-larga-orderport-bridge' ); ?></option>
		</select>
		
		<select id="sort-filter">
			<option value="menu_order"><?php esc_html_e( 'Default', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="title"><?php esc_html_e( 'Name A-Z', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="title_desc"><?php esc_html_e( 'Name Z-A', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="price_asc"><?php esc_html_e( 'Price Low to High', 'casa-larga-orderport-bridge' ); ?></option>
			<option value="price_desc"><?php esc_html_e( 'Price High to Low', 'casa-larga-orderport-bridge' ); ?></option>
		</select>
	</div>

	<div class="cl-wine-grid" id="wine-grid">
		<?php while ( $wines->have_posts() ) : $wines->the_post(); ?>
			<?php
			$opsku = get_post_meta( get_the_ID(), 'orderport_sku', true );
			$retail_price = get_post_meta( get_the_ID(), 'retail_price', true );
			$sale_price = get_post_meta( get_the_ID(), 'sale_price', true );
			$vintage = get_post_meta( get_the_ID(), 'spec_vintage', true );
			$varietal = get_post_meta( get_the_ID(), 'spec_varietal', true );
			?>
			<?php 
			$wine_types = wp_get_post_terms(get_the_ID(), 'cl_wine_type', array('fields' => 'slugs'));
			$type_string = !empty($wine_types) && !is_wp_error($wine_types) ? implode(',', $wine_types) : '';
			?>
			<div class="cl-wine-item" 
			     data-price="<?php echo esc_attr( $retail_price ); ?>" 
			     data-type="<?php echo esc_attr( $type_string ); ?>"
			     data-vintage="<?php echo esc_attr( $vintage ); ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="cl-wine-image">
						<a href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) ); ?>
						</a>
					</div>
				<?php endif; ?>
				
				<div class="cl-wine-content">
					<h3 class="cl-wine-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					
					<?php if ( $vintage ) : ?>
						<div class="cl-wine-vintage"><?php echo esc_html( $vintage ); ?></div>
					<?php endif; ?>
					
					<?php if ( $varietal ) : ?>
						<div class="cl-wine-varietal"><?php echo esc_html( $varietal ); ?></div>
					<?php endif; ?>
					
					<div class="cl-wine-price">
						<?php if ( $sale_price && $sale_price < $retail_price ) : ?>
							<span class="cl-sale-price">$<?php echo esc_html( number_format( $sale_price, 2 ) ); ?></span>
							<span class="cl-original-price">$<?php echo esc_html( number_format( $retail_price, 2 ) ); ?></span>
						<?php else : ?>
							<span class="cl-price">$<?php echo esc_html( number_format( $retail_price, 2 ) ); ?></span>
						<?php endif; ?>
					</div>
					
					<div class="cl-wine-actions">
						<?php if ( $opsku ) : ?>
							<a href="https://casalarga.orderport.net/addtocart?sku=<?php echo esc_attr( $opsku ); ?>" 
							   class="cl-add-to-cart" target="_blank">
								<?php esc_html_e( 'Add to Cart', 'casa-larga-orderport-bridge' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</div>

	<?php if ( $wines->max_num_pages > 1 ) : ?>
		<div class="cl-pagination">
			<?php
			echo paginate_links( array(
				'total' => $wines->max_num_pages,
				'current' => max( 1, get_query_var( 'paged' ) ),
				'format' => '?paged=%#%',
				'show_all' => false,
				'type' => 'list',
				'end_size' => 2,
				'mid_size' => 1,
				'prev_text' => __( '&laquo; Previous', 'casa-larga-orderport-bridge' ),
				'next_text' => __( 'Next &raquo;', 'casa-larga-orderport-bridge' ),
			) );
			?>
		</div>
	<?php endif; ?>
</div>
