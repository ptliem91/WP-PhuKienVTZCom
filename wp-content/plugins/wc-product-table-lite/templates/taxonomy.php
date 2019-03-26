<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// false attribute
if( empty( $taxonomy ) ){
	return;
}

$terms = wp_get_post_terms( $product->get_id(), $taxonomy );

if(
	empty( $terms ) ||
	is_wp_error( $terms )
){
	$terms = false;

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
				$output .= '<div class="wcpt-taxonomy-term ' . $term_html_class . '" data-wcpt-slug="'. $term->slug .'">' . wcpt_parse_2( $rule['label'] ) . '</div>';

				break;
      }
    }

		if( ! $match ){
			$output .= '<div class="wcpt-taxonomy-term " data-wcpt-slug="'. $term->slug .'">' . $term->name . '</div>';
		}

		if( $index < count( $terms ) - 1 ){
			$output .= '<div class="wcpt-taxonomy-term-separator wcpt-term-separator">'. $separator .'</div>';
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
	echo '<div class="wcpt-taxonomy '. $html_class .'">' . $output . '</div>';
}
