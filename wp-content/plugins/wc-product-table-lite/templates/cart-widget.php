<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! WC()->cart ){
	return;
}
$wcpt_settings = json_decode( stripslashes( get_option( 'wcpt_settings', '' ) ), true );
$settings = $wcpt_settings['cart_widget'];

$labels = $settings['labels'];
$locale = get_locale();

$strings = array();

if( ! empty( $labels ) ){
	foreach( $labels as $key => $translations ){
		$strings[$key] = array();
		$translations = preg_split ('/$\R?^/m', $translations);
		foreach( $translations as $translation ){
			$array = explode( ':', $translation );
			$strings[$key][ trim( $array[0] ) ] = trim( $array[1] );
		}
	}
}

?>
<style media="screen">
	@media(min-width:1101px){
		.wcpt-cart-widget{
			display: <?php echo $settings['toggle'] == 'enabled' ? 'inline-block' : 'none'; ?>;
			<?php
				if( ! empty( $settings['style']['bottom'] ) ){
					?>
					bottom: <?php echo $settings['style']['bottom'] . 'px'; ?>;
					<?php
				}
			?>
		}
	}
	@media(max-width:1100px){
		.wcpt-cart-widget{
			display: <?php echo $settings['r_toggle'] == 'enabled' ? 'inline-block' : 'none'; ?>;
		}
	}

	.wcpt-cart-widget{
		<?php
			if( ! empty( $settings['style'] ) ){
				foreach( $settings['style'] as $prop => $val ){
					if( $prop == 'bottom' ) continue;

					if( ! empty( $val ) ){
						echo $prop . ' : ' . $val . '; ';
		 			}
				}
			}
		?>
	}

</style>
<?php

$total_qty = WC()->cart->cart_contents_count;
$total_price = WC()->cart->get_cart_subtotal();
$cart_url = wc_get_cart_url();

$hide = $total_qty ? false : true;

?>
<div class="wcpt-cart-widget <?php echo $total_qty ? '' : 'wcpt-hide'; ?>" data-wcpt-href="<?php echo $cart_url; ?>">
  <div class="wcpt-cw-half">
      <span class="wcpt-cw-qty-total">
				<span class="wcpt-cw-figure"><?php echo $total_qty; ?></span>
				<span class="wcpt-cw-text">
					<?php
						if( $total_qty > 1 ){
							echo ( ! empty( $strings['items'] ) && ! empty( $strings['items'][$locale] ) ) ?  $strings['items'][$locale] : __('Sản Phẩm', 'wc-product-table');
						}else{
							echo ( ! empty( $strings['item'] ) && ! empty( $strings['item'][$locale] ) ) ?  $strings['item'][$locale] : __('Sản Phẩm', 'wc-product-table');
						}
					?>
				</span>
			</span>
      <span class="wcpt-cw-separator">|</span>
      <span class="wcpt-cw-price-total">
				<?php echo $total_price; ?>
			</span>
  </div>
  	<!--
	<div class="wcpt-cw-footer">
		<?php echo ( ! empty( $strings['extra_charges'] ) && ! empty( $strings['extra_charges'][$locale] ) ) ?  $strings['extra_charges'][$locale] : __('Phụ phí có thể được áp dụng.', 'wc-product-table'); ?>
	</div>
	-->

  <a href="<?php echo $cart_url;?>" class="wcpt-cw-half">
      <span class="wcpt-cw-loading-icon wcpt-hide"><?php wcpt_icon('loader'); ?></span>
      <span class="wcpt-cw-view-label">
				<?php echo ( ! empty( $strings['view_cart'] ) && ! empty( $strings['view_cart'][$locale] ) ) ?  $strings['view_cart'][$locale] : __('Giỏ Hàng', 'wc-product-table'); ?>
			</span>
      <span class="wcpt-cw-cart-icon"><?php wcpt_icon('shopping-bag'); ?></span>
  </a>
</div>
