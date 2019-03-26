<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$min_value   = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );
$max_value   = apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product );
$input_value = ( isset( $_REQUEST['quantity'] ) && defined( 'DOING_AJAX' ) ) ? wc_stock_amount( (int) $_REQUEST['quantity'] ) : $product->get_min_purchase_quantity();
$input_id    = uniqid( 'quantity_' );
$input_name  = 'quantity';
$step        = apply_filters( 'woocommerce_quantity_input_step', 1, $product );
$pattern     = apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' );
$inputmode   = apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' );


?>
<div class="quantity wcpt-quantity-wrapper">
	<input type="number" id="<?php echo esc_attr( $input_id ); ?>" class="input-text qty text <?php echo $html_class; ?>" <?php if( $product->get_sold_individually() ) echo 'disabled'; ?> step="<?php echo esc_attr( $step ); ?>" min="<?php echo esc_attr( $min_value ); ?>" max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" title="<?php echo esc_attr_x( 'Quantity', 'Product quantity input tooltip', 'woocommerce' ) ?>" size="4" pattern="<?php echo esc_attr( $pattern ); ?>" inputmode="<?php echo esc_attr( $inputmode ); ?>" aria-labelledby="<?php echo ! empty( $args['product_name'] ) ? sprintf( esc_attr__( '%s quantity', 'woocommerce' ), $args['product_name'] ) : ''; ?>" />
</div>
