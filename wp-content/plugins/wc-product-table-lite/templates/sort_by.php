<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$table_id = $GLOBALS['wcpt_table_data']['id'];
$field_name_orderby = $table_id . '_orderby';

// get default orderby params
$default_params = wcpt_get_nav_filter('orderby');

// ensure drop down options
if( empty( $dropdown_options ) ){
  $dropdown_options = json_decode( '[{"orderby":"popularity","order":"DESC","meta_key":"","label":"Sort by Popularity"},{"orderby":"rating","order":"DESC","meta_key":"","label":"Sort by Rating"},{"orderby":"price","order":"ASC","meta_key":"","label":"Sort by Price low to high"},{"orderby":"price-desc","order":"DESC","meta_key":"","label":"Sort by Price high to low"},{"orderby":"date","order":"DESC","meta_key":"","label":"Sort by Newness"},{"orderby":"title","order":"ASC","meta_key":"","label":"Sort by Name A - Z"},{"orderby":"title","order":"DESC","meta_key":"","label":"Sort by Name Z - A"}]', true );
}

// get current orderby

//-- none selected
if( empty( $_GET[ $field_name_orderby ] ) ){

  $selected_index = wcpt_sortby_get_matching_option_index( $default_params, $dropdown_options );

  // matching option found
  if( $selected_index !== false ){
    $current_params = $dropdown_options[$selected_index];

  // no option here matching the default option
  }else{

    // add default option
    $current_params = array(
      'label'   => __( 'Sort by ', 'wcpt' ),
      'orderby' => $default_params['orderby'],
      'order'   => $default_params['order'],
      'meta_key'=> $default_params['meta_key'],
    );

    $dropdown_options[] = $current_params;
    $selected_index = count($dropdown_options) - 1;

  }

//-- user selected
}else{

  $orderby = $_GET[ $field_name_orderby ];

  //-- -- column sort
  if( substr( $orderby, 0, 7 ) == 'column_'  ){

    $data =& $GLOBALS['wcpt_table_data'];
    $col_index = (int) substr( $orderby, 7 );
    $device = empty( $_GET[ $table_id . '_device' ] ) ? 'laptop' : (string) $_GET[ $table_id . '_device' ];

    $column_sorting = wcpt_get_column_sorting_info( $col_index, $device );

    $current_order = $_GET[ $table_id . '_order' ];
    $column_sorting['order'] = $current_order;
    if( $column_sorting['orderby'] == 'price' && strtolower( $current_order ) == 'desc' ){
      $column_sorting['orderby'] = 'price-desc';
    }

    $selected_index = wcpt_sortby_get_matching_option_index( $column_sorting, $dropdown_options );

    // matching option found
    if( $selected_index !== false ){
      $current_params = $dropdown_options[$selected_index];

    // no matching option
    }else{

      // add the column as an option

			$label = __( 'Sort by ', 'wcpt' );
			$label_prefix = '';

			if( in_array( $column_sorting['orderby'], array( 'meta_value', 'meta_value_num' ) )  ){
				$label_prefix = $column_sorting['meta_key'];
			}else{
				$label_prefix = $column_sorting['orderby'];
			}

			$label_prefix = strtoupper( $label_prefix[0] ) .  substr( $label_prefix, 1 );

      $current_params = array(
        'label' => $label . $label_prefix,
        'orderby' => $column_sorting['orderby'],
        'order' => $column_sorting['order'],
        'meta_key' => $column_sorting['meta_key'],
      );

      $dropdown_options[] = $current_params;
      $selected_index = count($dropdown_options) - 1;

    }

  //-- -- dropdown sort
  }else if( substr( $orderby, 0, 7 ) == 'option_'  ){

    $selected_index = (int) substr( $orderby, 7 );
    $current_params = $dropdown_options[$selected_index];

    $current_params['filter'] = 'orderby';
    wcpt_update_user_filters( $current_params, true );

  }

}

if(
	empty( $display_type ) ||
	( ! empty( $position ) && $position === 'left_sidebar' )
){
  $display_type = 'dropdown';
}

if( $display_type == 'dropdown' ){
  $container_html_class = 'wcpt-dropdown wcpt-filter ' . $html_class;
  $heading_html_class = 'wcpt-dropdown-label';
  $options_container_html_class = 'wcpt-dropdown-menu';
  $single_option_container_html_class = 'wcpt-dropdown-option';

}else{
  $container_html_class = 'wcpt-options-row wcpt-filter ' . $html_class;
  $heading_html_class = 'wcpt-options-heading';
  $options_container_html_class = 'wcpt-options';
  $single_option_container_html_class = 'wcpt-option';

}

// heading row
if( empty( $heading ) ){
	$heading = '';
}

if( ! $heading = wcpt_parse_2( $heading ) ){
	$container_html_class .= ' wcpt-no-heading wcpt-filter-open';
}

if( ! empty( $accordion_always_open ) ){
	$container_html_class .= ' wcpt-filter-open';
}

?>
<div class="<?php echo $container_html_class; ?>" data-wcpt-filter="sort_by">

  <?php
  ob_start();
  ?>

  <!-- options menu -->
  <div class="<?php echo $options_container_html_class; ?>">
    <?php
    $selected_label = '';
    foreach ( $dropdown_options as $option_index => $option ) {

      if( $selected_index == $option_index ){
        $checked = ' checked="checked" ';
        $selected_label = $option['label'];
      }else{
        $checked = '';
      }

      ?>
      <label class="<?php echo $single_option_container_html_class; ?>">
        <input type="radio" name="<?php echo $field_name_orderby; ?>" <?php echo $checked; ?> value="option_<?php echo $option_index; ?>" class="wcpt-filter-radio"><span><?php echo $option['label']; ?></span>
      </label>
      <?php
    }

    ?>
  </div>

  <?php
  $dropdown_menu = ob_get_clean();
  ?>

	<div class="wcpt-filter-heading">
		<!-- label -->
  	<span class="<?php echo $heading_html_class; ?>"><?php echo $display_type == 'dropdown' ? $selected_label : $heading; ?></span>
	  <!-- icon -->
	  <?php wcpt_icon('chevron-down'); ?>
	</div>

  <?php echo $dropdown_menu; ?>

</div>
