<?php
/**
 * Custom label confirmation email template
 *
 * @package CasaLargaOrderPortBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$customer_name = $customer_name ?? '';
$wine_title = $wine_title ?? '';
$quantity = $quantity ?? 1;
$wine_total = $wine_total ?? 0;
$label_total = $label_total ?? 0;
$grand_total = $grand_total ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php esc_html_e( 'Custom Wine Label Order Confirmation', 'casa-larga-orderport-bridge' ); ?></title>
	<style>
		body { 
			font-family: Arial, sans-serif; 
			line-height: 1.6; 
			color: #333; 
			max-width: 600px; 
			margin: 0 auto; 
			padding: 20px; 
		}
		.header { 
			background: #8B0000; 
			color: white; 
			padding: 20px; 
			text-align: center; 
			border-radius: 5px 5px 0 0; 
		}
		.content { 
			background: #f9f9f9; 
			padding: 20px; 
			border: 1px solid #ddd; 
		}
		.footer { 
			background: #f5f5f5; 
			padding: 20px; 
			text-align: center; 
			border-radius: 0 0 5px 5px; 
			border: 1px solid #ddd; 
			border-top: none; 
		}
		.order-details { 
			background: white; 
			padding: 15px; 
			margin: 15px 0; 
			border-radius: 5px; 
			border: 1px solid #ddd; 
		}
		.order-details ul { 
			list-style: none; 
			padding: 0; 
		}
		.order-details li { 
			padding: 5px 0; 
			border-bottom: 1px solid #eee; 
		}
		.order-details li:last-child { 
			border-bottom: none; 
			font-weight: bold; 
			font-size: 1.1em; 
		}
		.button { 
			display: inline-block; 
			background: #8B0000; 
			color: white; 
			padding: 10px 20px; 
			text-decoration: none; 
			border-radius: 5px; 
			margin: 10px 0; 
		}
	</style>
</head>
<body>
	<div class="header">
		<h1><?php esc_html_e( 'Custom Wine Label Order Received', 'casa-larga-orderport-bridge' ); ?></h1>
	</div>
	
	<div class="content">
		<p><?php printf( esc_html__( 'Dear %s,', 'casa-larga-orderport-bridge' ), esc_html( $customer_name ) ); ?></p>
		<p><?php esc_html_e( 'Thank you for your custom wine label order!', 'casa-larga-orderport-bridge' ); ?></p>
		
		<div class="order-details">
			<h3><?php esc_html_e( 'Order Details:', 'casa-larga-orderport-bridge' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'Wine:', 'casa-larga-orderport-bridge' ); ?></strong> <?php echo esc_html( $wine_title ); ?></li>
				<li><strong><?php esc_html_e( 'Quantity:', 'casa-larga-orderport-bridge' ); ?></strong> <?php echo esc_html( $quantity ); ?> <?php esc_html_e( 'bottles', 'casa-larga-orderport-bridge' ); ?></li>
				<li><strong><?php esc_html_e( 'Wine Price:', 'casa-larga-orderport-bridge' ); ?></strong> $<?php echo esc_html( number_format( $wine_total, 2 ) ); ?></li>
				<li><strong><?php esc_html_e( 'Label Fee:', 'casa-larga-orderport-bridge' ); ?></strong> $<?php echo esc_html( number_format( $label_total, 2 ) ); ?></li>
				<li><strong><?php esc_html_e( 'Total:', 'casa-larga-orderport-bridge' ); ?></strong> $<?php echo esc_html( number_format( $grand_total, 2 ) ); ?></li>
			</ul>
		</div>
		
		<p><?php esc_html_e( 'Your custom label design is attached to this email for your records.', 'casa-larga-orderport-bridge' ); ?></p>
		
		<p><?php esc_html_e( 'We will contact you within 1-2 business days to confirm your order and arrange payment and shipping.', 'casa-larga-orderport-bridge' ); ?></p>
		
		<p><?php esc_html_e( 'Questions? Contact us at: info@casalarga.com', 'casa-larga-orderport-bridge' ); ?></p>
	</div>
	
	<div class="footer">
		<p><strong><?php esc_html_e( 'Casa Larga Vineyards', 'casa-larga-orderport-bridge' ); ?></strong><br>
		2287 Turk Hill Rd, Fairport, NY 14450<br>
		(585) 223-4210</p>
	</div>
</body>
</html>
