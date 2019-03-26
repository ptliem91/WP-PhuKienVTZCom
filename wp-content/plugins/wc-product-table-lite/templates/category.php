<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

$terms = get_the_terms( $product->get_id(), 'product_cat' );

// excludes array
$excludes_arr = array();
if( ! empty( $exclude_terms ) ){
	$excludes_arr = preg_split( '/\r\n|\r|\n/', $exclude_terms );
}

// relabels

if( $terms && count( $terms ) ){
// associated terms exist

	if( empty ( $separator ) ){
 		$separator = '';
 	}else{
		$separator = wcpt_parse_2( $separator );
	}

	$output = '';

	// relabel each term
	foreach( $terms as $index => $term ){

		// exclude
		if( in_array( $term->name, $excludes_arr ) ){
			continue;
		}

		// look for a matching rule
	    $match = false;

	    if( ! empty( $relabels ) ){

		    foreach( $relabels as $rule ){
					if( wp_specialchars_decode( $term->name ) == $rule['term'] ){
		        $match = true;

						// style
						wcpt_parse_style_2( $rule, '!important' );
						$term_html_class = 'wcpt-' . $rule['id'];

						// append
						$output .= '<div class="wcpt-category '. $html_class . ' ' . $term_html_class . '">' . wcpt_parse_2( $rule['label'] ) . '</div>';

						break;
		      }
		    }

	    }

		if( ! $match ){
			$output .= '<div class="wcpt-category '. $html_class . ' " data-wcpt-slug="'. $term->slug .'">' . $term->name . '</div>';
		}

		if( $index < count( $terms ) - 1 ){
			$output .= '<div class="wcpt-category-separator wcpt-term-separator">'. $separator .'</div>';
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
	echo '<div class="wcpt-categories">' . $output . '</div>';
}
