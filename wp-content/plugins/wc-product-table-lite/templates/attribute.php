<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// false attribute
if( empty( $attribute_name ) ){
	return;
}

// product variation
if( $product->get_type() == 'variation' ){
	$field_name = 'attribute_pa_' . $attribute_name;
	include( 'custom_field.php' );
	return;
}

$attributes = $product->get_attributes();

if ( isset( $attributes[ $attribute_name] ) ) {
	$attribute_object = $attributes[ $attribute_name];
} elseif ( isset( $attributes[ 'pa_' . $attribute_name ] ) ) {
	$attribute_object = $attributes[ 'pa_' . $attribute_name ];
}

if( empty( $attribute_object ) ){
	$terms = false;

} else if( $attribute_object && $attribute_object->is_taxonomy() ){
	$terms = wc_get_product_terms( $product->get_id(), $attribute_object->get_name(), array( 'fields' => 'all', 'orderby' => 'menu_id' ) );

}else{ // text attribute
	$terms = $attribute_object->get_options();

}

// excludes array
$excludes_arr = array();
if( ! empty( $exclude_terms ) ){
	$excludes_arr = preg_split( '/\r\n|\r|\n/', $exclude_terms );
}

if( $terms && count( $terms ) ){
// associated terms exist

	if( empty ( $separator ) ){
 		$separator = '';
 	}else{
		$separator = wcpt_parse_2( $separator );
	}

	$output = '';

	if( empty( $relabels ) ){
		$relabels = array();
	}

	// sort terms prioritizing current fitler
	global $wcpt_table_data;
	$table_id = $wcpt_table_data['id'];
	$filter_key = $table_id . '_attr_pa_' . $attribute_name;
	if( ! empty( $_GET[ $filter_key ] ) && ! empty( $terms ) ){
		$_terms = array();
		foreach( $terms as $term ){
			$_terms[$term->term_id] = $term;
		}
		$terms = array_replace( array_intersect_key( array_flip( $_GET[ $filter_key ] ), $_terms ), $_terms );
	}

	$terms = array_values($terms);

	// relabel each term
	foreach( $terms as $index => $term ){

		// exclude
		if( in_array( $term->name, $excludes_arr ) ){
			continue;
		}

		// look for a matching rule
    $match = false;

    foreach( $relabels as $rule ){
      if( ! empty( $rule['term'] ) && wp_specialchars_decode( $term->name ) == $rule['term'] ){
        $match = true;

				// style
				wcpt_parse_style_2( $rule, '!important' );
				$term_html_class = 'wcpt-' . $rule['id'];

				// append
				$output .= '<div class="wcpt-attribute-term ' . $term_html_class . '" data-wcpt-slug="'. $term->slug .'">' . wcpt_parse_2( $rule['label'] ) . '</div>';

				break;
      }
    }

		if( ! $match ){
			$output .= '<div class="wcpt-attribute-term " data-wcpt-slug="'. $term->slug .'">' . $term->name . '</div>';
		}

		if( $index < count( $terms ) - 1 ){
			$output .= '<div class="wcpt-attribute-term-separator wcpt-term-separator">'. $separator .'</div>';
		}

  }

}else{
// no associated terms

	if( empty( $empty_relabel ) ){
		$empty_relabel = '';
	}

	$output = wcpt_parse_2($empty_relabel);

}


if( ! empty( $output ) ){
	echo '<div class="wcpt-attribute '. $html_class .'">' . $output . '</div>';
}
