<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

++$GLOBALS['wcpt_search_count'];

$keyword = '';
$table_id = $GLOBALS['wcpt_table_data']['id'];
$param = $table_id . '_search_' . $GLOBALS['wcpt_search_count'];

if( in_array($target, array('title', 'title+content')) ){

	// pre-selected
	if( $pre_selected = wcpt_get_nav_filter( 'search' ) ){
		if( empty( $_GET[$table_id . '_filtered'] ) ){
			// apply
			$keyword = $_GET[$param] = $_REQUEST[$param] = $pre_selected['keyword'];
		}else{
			// remove
			wcpt_clear_nav_filter( 'search' );
		}
	}

}

if( ! empty( $_GET[ $param ] ) ){
  $keyword = sanitize_text_field( $_GET[ $param ] );

	$filter_info = array(
		'filter'    		=> 'search',
		'keyword'				=> $keyword,
    'values'      	=> array( $keyword ),
		'target'				=> $target,
		'custom_fields'	=> $custom_fields,
	);

	if( ! empty( $clear_label ) ){
		$filter_info['clear_labels_2'] = array(
			$filter_info['keyword'] => str_replace( '[kw]', htmlentities( $keyword ), $clear_label ),
		);
	}else{
		$filter_info['clear_labels_2'] = array(
			$filter_info['keyword'] => __('Search') . ' : ' . htmlentities( $keyword ),
		);
	}

	$single = false;

	wcpt_update_user_filters( $filter_info, $single );
}

$search_label = '';
$placeholder = ! empty( $placeholder ) ? wcpt_parse_2( $placeholder ) : __( 'Search', 'wcpt' );

?>
<div class="wcpt-search-wrapper">
	<div
		class="wcpt-search <?php if( ! empty( $keyword ) ) echo 'wcpt-active'; echo $html_class; ?>"
		data-wcpt-table-id="<?php echo $GLOBALS['wcpt_table_data']['id']; ?>"
	>

	  <!-- input -->
	  <input
	    class="wcpt-search-input"
	    type="search"
	    name="<?php echo $param; ?>"
	    placeholder="<?php echo $placeholder; ?>" value="<?php echo $keyword; ?>"
	    autocomplete="off"
	  >

	  <!-- submit -->
	  <span class="wcpt-search-submit">
			<?php wcpt_icon('search', 'wcpt-search-submit-icon'); ?>
	  </span>

	  <!-- clear -->
	  <?php if( ! empty( $keyword ) ) { ?>
	    <a href="javascript:void(0)" class="wcpt-search-clear">
				<?php wcpt_icon('x', 'wcpt-search-clear-icon'); ?>
	    </a>
	  <?php } ?>

	</div>
</div>
