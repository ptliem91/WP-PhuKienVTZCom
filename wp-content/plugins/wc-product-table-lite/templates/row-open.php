<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// product variation
if( $product->get_type() == 'variation' ){
  $product_id_data = ' data-wcpt-product-id="'. wp_get_post_parent_id( $product->get_id() ) .'" ';
  $variation_id_data = ' data-wcpt-variation-id="'. $product->get_id() .'" ';

// other product type
}else{
	$product_id_data = ' data-wcpt-product-id="'. $product->get_id() .'" ';
  $variation_id_data = '';

}

$product_type_html_class = 'wcpt-product-type-' . $product->get_type();

$in_cart = wcpt_get_cart_item_quantity($product->get_id());

$stock = $product->get_stock_quantity();

$has_product_addons = '';
if( function_exists( 'get_product_addons' ) ){
	$product_addons = get_product_addons( $product->get_id() );
	if ( is_array( $product_addons ) && count( $product_addons ) > 0 ) {
		$has_product_addons = 'wcpt-product-has-addons';
	}
}

echo '<tr class="wcpt-row wcpt-'. ( $products->current_post % 2 ? 'even' : 'odd' )  .' ' . $product_type_html_class . ' ' . $has_product_addons .'" '. $variation_id_data .' data-wcpt-product-id="'. $product->get_id() .'" data-wcpt-in-cart="'. $in_cart .'" data-wcpt-stock="'. $stock .'">';
?>
