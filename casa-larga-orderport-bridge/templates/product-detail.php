<?php
/**
 * Wine product detail template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wine;
if ( ! $wine ) {
	return;
}

$opsku = get_post_meta( $wine->ID, 'orderport_sku', true );
$retail_price = get_post_meta( $wine->ID, 'retail_price', true );
$sale_price = get_post_meta( $wine->ID, 'sale_price', true );
$vintage = get_post_meta( $wine->ID, 'spec_vintage', true );
$varietal = get_post_meta( $wine->ID, 'spec_varietal', true );
$alcohol = get_post_meta( $wine->ID, 'spec_alcohol', true );
$volume = get_post_meta( $wine->ID, 'spec_volume', true );
$appellation = get_post_meta( $wine->ID, 'spec_appellation', true );
$tasting_notes = get_post_meta( $wine->ID, 'spec_tasting_notes', true );
$production_notes = get_post_meta( $wine->ID, 'spec_production_notes', true );
$vineyard_notes = get_post_meta( $wine->ID, 'spec_vineyard_notes', true );

// Get related wines
$related_wines = get_posts( array(
	'post_type'      => 'cl_wine_product',
	'post_status'    => 'publish',
	'posts_per_page' => 4,
	'post__not_in'   => array( $wine->ID ),
	'tax_query'      => array(
		array(
			'taxonomy' => 'cl_wine_type',
			'field'    => 'term_id',
			'terms'    => wp_get_post_terms( $wine->ID, 'cl_wine_type', array( 'fields' => 'ids' ) ),
		),
	),
) );
?>
<div class="cl-wine-detail" itemscope itemtype="https://schema.org/Product">
	<meta itemprop="name" content="<?php echo esc_attr( get_the_title( $wine ) ); ?>">
	<meta itemprop="price" content="<?php echo esc_attr( $retail_price ); ?>">
	<meta itemprop="availability" content="InStock">
	
	<div class="cl-wine-detail-content">
		<div class="cl-wine-images">
			<?php if ( has_post_thumbnail( $wine ) ) : ?>
				<div class="cl-wine-main-image">
					<?php echo get_the_post_thumbnail( $wine, 'large', array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
		
		<div class="cl-wine-info">
			<h1 class="cl-wine-title"><?php echo esc_html( get_the_title( $wine ) ); ?></h1>
			
			<?php if ( $vintage ) : ?>
				<div class="cl-wine-vintage"><?php echo esc_html( $vintage ); ?></div>
			<?php endif; ?>
			
			<div class="cl-wine-price">
				<?php if ( $sale_price && $sale_price < $retail_price ) : ?>
					<span class="cl-sale-price">$<?php echo esc_html( number_format( $sale_price, 2 ) ); ?></span>
					<span class="cl-original-price">$<?php echo esc_html( number_format( $retail_price, 2 ) ); ?></span>
				<?php else : ?>
					<span class="cl-price">$<?php echo esc_html( number_format( $retail_price, 2 ) ); ?></span>
				<?php endif; ?>
			</div>
			
			<?php if ( $opsku ) : ?>
				<div class="cl-wine-actions">
					<a href="https://casalarga.orderport.net/addtocart?sku=<?php echo esc_attr( $opsku ); ?>" 
					   class="cl-add-to-cart button" target="_blank">
						<?php esc_html_e( 'Add to Cart', 'casa-larga-orderport-bridge' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
	
	<div class="cl-wine-specifications">
		<h3><?php esc_html_e( 'Wine Specifications', 'casa-larga-orderport-bridge' ); ?></h3>
		<table class="cl-specs-table">
			<?php if ( $vintage ) : ?>
				<tr>
					<th><?php esc_html_e( 'Vintage', 'casa-larga-orderport-bridge' ); ?></th>
					<td><?php echo esc_html( $vintage ); ?></td>
				</tr>
			<?php endif; ?>
			
			<?php if ( $varietal ) : ?>
				<tr>
					<th><?php esc_html_e( 'Varietal', 'casa-larga-orderport-bridge' ); ?></th>
					<td><?php echo esc_html( $varietal ); ?></td>
				</tr>
			<?php endif; ?>
			
			<?php if ( $alcohol ) : ?>
				<tr>
					<th><?php esc_html_e( 'Alcohol Content', 'casa-larga-orderport-bridge' ); ?></th>
					<td><?php echo esc_html( $alcohol ); ?>%</td>
				</tr>
			<?php endif; ?>
			
			<?php if ( $volume ) : ?>
				<tr>
					<th><?php esc_html_e( 'Volume', 'casa-larga-orderport-bridge' ); ?></th>
					<td><?php echo esc_html( $volume ); ?></td>
				</tr>
			<?php endif; ?>
			
			<?php if ( $appellation ) : ?>
				<tr>
					<th><?php esc_html_e( 'Appellation', 'casa-larga-orderport-bridge' ); ?></th>
					<td><?php echo esc_html( $appellation ); ?></td>
				</tr>
			<?php endif; ?>
		</table>
	</div>
	
	<?php if ( $tasting_notes ) : ?>
		<div class="cl-wine-tasting-notes">
			<h3><?php esc_html_e( 'Tasting Notes', 'casa-larga-orderport-bridge' ); ?></h3>
			<div class="cl-tasting-content">
				<?php echo wp_kses_post( $tasting_notes ); ?>
			</div>
		</div>
	<?php endif; ?>
	
	<?php if ( $production_notes ) : ?>
		<div class="cl-wine-production-notes">
			<h3><?php esc_html_e( 'Production Notes', 'casa-larga-orderport-bridge' ); ?></h3>
			<div class="cl-production-content">
				<?php echo wp_kses_post( $production_notes ); ?>
			</div>
		</div>
	<?php endif; ?>
	
	<?php if ( $vineyard_notes ) : ?>
		<div class="cl-wine-vineyard-notes">
			<h3><?php esc_html_e( 'Vineyard Notes', 'casa-larga-orderport-bridge' ); ?></h3>
			<div class="cl-vineyard-content">
				<?php echo wp_kses_post( $vineyard_notes ); ?>
			</div>
		</div>
	<?php endif; ?>
	
	<?php if ( $wine->post_content ) : ?>
		<div class="cl-wine-description">
			<h3><?php esc_html_e( 'Description', 'casa-larga-orderport-bridge' ); ?></h3>
			<div class="cl-description-content">
				<?php echo apply_filters( 'the_content', $wine->post_content ); ?>
			</div>
		</div>
	<?php endif; ?>
	
	<?php if ( ! empty( $related_wines ) ) : ?>
		<div class="cl-related-wines">
			<h3><?php esc_html_e( 'Related Wines', 'casa-larga-orderport-bridge' ); ?></h3>
			<div class="cl-related-grid">
				<?php foreach ( $related_wines as $related ) : ?>
					<?php
					$related_price = get_post_meta( $related->ID, 'retail_price', true );
					$related_image = get_the_post_thumbnail_url( $related, 'medium' );
					?>
					<div class="cl-related-item">
						<?php if ( $related_image ) : ?>
							<div class="cl-related-image">
								<a href="<?php echo esc_url( get_permalink( $related ) ); ?>">
									<img src="<?php echo esc_url( $related_image ); ?>" alt="<?php echo esc_attr( get_the_title( $related ) ); ?>" loading="lazy">
								</a>
							</div>
						<?php endif; ?>
						
						<div class="cl-related-content">
							<h4><a href="<?php echo esc_url( get_permalink( $related ) ); ?>"><?php echo esc_html( get_the_title( $related ) ); ?></a></h4>
							<div class="cl-related-price">$<?php echo esc_html( number_format( $related_price, 2 ) ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
