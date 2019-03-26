<?php
/**
 * Product table shortcode class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Shortcode_Product_Table extends WC_Shortcode_Products {

	public $id;
	public $caching = false;
	public $transient_name;
	public $search_keyword = array( 'title'=> '', 'content'=> '' );
	public $only_loop = false;

	public function __construct( $attributes = array(), $type = 'product_table' ) {
		$this->type       		= $type;
		$this->attributes 		= $this->parse_attributes( $attributes );

	}

	public function get_transient_name(){
		if( ! $this->transient_name ){

			// inital query
			$initial_query =& $GLOBALS['wcpt_table_data']['query'];

			// user query
			$user_query = array();
			foreach( $_GET as $key=> $val ){
				if( FALSE !== strpos( $key, $this->id . '_' ) ){
					$user_query[$key] = $val;
				}
			}

			// user role
			$user = wp_get_current_user();
	    $user_roles = ( array ) $user->roles;

			// merge all 3
			$combined = array_merge( $initial_query, $user_query, $user_roles );
			sort( $combined );

			$this->transient_name = 'wcpt_'. $this->id .'_cache_' . md5( wp_json_encode( $combined ) ) . WC_Cache_Helper::get_transient_version('product_loop');
		}

		return $this->transient_name;

	}

	public function get_cache(){
		if( ! $this->caching ){
			return false;
		}
		return get_transient( $this->get_transient_name() );
	}

	public function set_cache($markup){
		if( ! $this->caching ){
			return;
		}
		set_transient( $this->get_transient_name(), $markup, DAY_IN_SECONDS );
	}

	protected function get_products() {
		// $transient_name = 'wc_loop' . substr( md5( wp_json_encode( $this->query_args ) . $this->type ), 28 ) . WC_Cache_Helper::get_transient_version( 'product_query' );
		// $products       = get_transient( $transient_name );

		// if ( false === $products || ! is_a( $products, 'WP_Query' ) ) {
			$this->query_args = apply_filters( 'wcpt_query_args', $this->query_args );
			$products = new WP_Query( $this->query_args );
		// 	set_transient( $transient_name, $products, DAY_IN_SECONDS * 30 );
		// }

		// Remove ordering query arguments.
		// if ( ! empty( $this->attributes['category'] ) ) {
			WC()->query->remove_ordering_args();
		// }

		return $products;
	}

	protected function product_loop() {

		$table_id         = $this->attributes['id'];
		$this->id 				= $table_id;
		$data 						=& $GLOBALS['wcpt_table_data'];
		$template_folder  = plugin_dir_path( __FILE__ ) . 'templates/';

		do_action('wcpt_before_loop', $this->attributes);

		// lazy load
		if( ! empty( $data['query'] ) && ! empty( $data['query']['sc_attrs'] ) && ! empty( $data['query']['sc_attrs']['lazy_load'] )  ){
			ob_start();
			include( $template_folder . 'lazy-load.php' );
			return ob_get_clean();
		}

		ob_start();

		include( $template_folder . 'container-open.php' );

		wcpt_styles();
		$data['style_items'] = array();

		// print any woocommerce messages
		if( function_exists('wc_print_notices') ){
			wc_print_notices();
		}

		// initial query data from editor
		$GLOBALS['wcpt_user_filters'] = array(
			//-- orderby
			array(
				'filter' 	=> 'orderby',
				'orderby' => ! empty( $data['query']['orderby'] ) ? $data['query']['orderby'] : 'date',
				'order' 	=> ! empty( $data['query']['order'] ) ? $data['query']['order'] : 'DESC',
				'meta_key'=> ! empty( $data['query']['meta_key'] ) && in_array( $data['query']['orderby'], array( 'meta_value_num', 'meta_value' ) ) ? $data['query']['meta_key'] : '',
			),
			//-- out of stock
			array(
				'filter' 	 => 'availability',
				'operator' => ! empty( $data['query']['hide_out_of_stock_items'] ) || ( get_option('woocommerce_hide_out_of_stock_items', 'no') == 'yes' ) ? 'NOT IN' : 'ALSO',
			),
		);

		// pre-selection
		$sc_attrs = $data['query']['sc_attrs'];
		//-- category
		if( ! empty( $sc_attrs['category'] ) ){
			$category = get_terms(array(
				'taxonomy' => 'product_cat',
				'fields' => 'tt_ids',
				'slug'=> explode( ',', $sc_attrs['category'] ),
				'hide_empty' => false,
			));

			$filter_info = array(
				'filter'      => 'category',
				'taxonomy' 	=> 'product_cat',
				'values'      => $category,
				'operator'    => 'IN',
				'clear_label' => '',
				'clear_labels_2' => '',
			);
			wcpt_update_user_filters($filter_info, false);
		}

		//-- search
		if( ! empty( $sc_attrs['_search'] ) ){
			$filter_info = array(
				'filter'    		=> 'search',
				'keyword'				=> $sc_attrs['_search'],
				'values'				=> array($sc_attrs['_search']),
				'target'				=> 'title+content',
				'clear_label' => '',
				'clear_labels_2' => '',
			);
			wcpt_update_user_filters($filter_info, false);
		}

		// use the shortcode attributes to create additional user filters here
		do_action('wcpt_before_apply_user_filters', $data);

		// reset search count
		$GLOBALS['wcpt_search_count'] = 0;

		$GLOBALS['wcpt_nav_later'] = array(); // collects nav elm with placeholders to be processed afterwards
		add_filter( 'wcpt_navigation', array( $this, 'nav_later' ) );

		if( ! $this->only_loop ){
			$nav = wcpt_parse_navigation();
		}

		// apply user filters

		//-- flags
		$user_set_cats = false;

		//-- iterate user nav filters
		if( ! empty( $GLOBALS['wcpt_user_filters'] ) ) {
			foreach( $GLOBALS['wcpt_user_filters'] as &$filter_info ){

				// results per page
				if( $filter_info['filter'] == 'results_per_page' ){
					$this->attributes['limit'] = $filter_info['results'];
				}

				// category
				if( $filter_info['filter'] == 'category' ){
					$this->query_args['tax_query'][] = array(
						'taxonomy' 	=> 'product_cat',
						'field'    	=> 'term_taxonomy_id',
						'terms'			=> $filter_info['values'],
						'operator'	=> $filter_info['operator'],
					);

					$user_set_cats = true;
				}

				// taxonomy
				if( $filter_info['filter'] == 'taxonomy' ){
					$this->query_args['tax_query'][] = array(
						'taxonomy' 	=> $filter_info['taxonomy'],
						'field'    	=> 'term_taxonomy_id',
						'terms'			=> $filter_info['values'],
						'operator'	=> $filter_info['operator'],
					);
				}

				// rating
				if( $filter_info['filter'] == 'rating' ){
					$this->query_args['meta_query'][] = array(
						'key' 		=> '_wc_average_rating',
						'value'		=> array( (int) $filter_info['values'][0], 5 ),
						'compare'	=> 'BETWEEN',
						'type'    => 'NUMERIC',
					);
				}

				// hide out of stock items
				if( $filter_info['filter'] == 'availability' && $filter_info['operator'] == 'NOT IN' ){
					$product_visibility_terms  = wc_get_product_visibility_term_ids();

					$this->query_args['tax_query'][] = array(
						'taxonomy' 	=> 'product_visibility',
						'field'    	=> 'term_taxonomy_id',
						'terms'			=> array( $product_visibility_terms['outofstock'] ),
						'operator'	=> $filter_info['operator'],
					);
				}

				// attribute
				if( $filter_info['filter'] == 'attribute' ){
					$this->query_args['tax_query'][] = array(
						'taxonomy' 	=> $filter_info['taxonomy'],
						'field'    	=> 'term_taxonomy_id',
						'terms'			=>	$filter_info['values'],
						'operator'	=> ! empty( $filter_info['operator'] ) ? $filter_info['operator'] : 'IN',
					);
				}

				// custom field
				if( $filter_info['filter'] == 'custom_field' ){

					if( $filter_info['compare'] == 'BETWEEN' ){
						$arr = array(
							'key' 		=> $filter_info['meta_key'],
							'value'		=> array( $filter_info['min'], $filter_info['max'] ),
							'compare'	=> 'BETWEEN',
							'type'    => empty( $filter_info['field_type'] ) ? 'NUMERIC' : $filter_info['field_type'],
						);

						if( $arr['type'] == 'DECIMAL' ){
							add_filter( 'get_meta_sql', array($this, 'cast_decimal_precision') );
						}

						if( ! $filter_info['max'] ){
							$arr['compare'] = '>=';
							$arr['value'] = (int)$filter_info['min'];
						}

						if( ! $filter_info['min'] ){
							$arr['compare'] = '<=';
							$arr['value'] = (int)$filter_info['max'];
						}

						$this->query_args['meta_query'][] = $arr;

					}else if( $filter_info['compare'] == 'IN' ){
						$this->query_args['meta_query'][] = array(
							'key' 		=> $filter_info['meta_key'],
							'value'		=> $filter_info['values'],
							'compare'	=> 'IN',
						);

					}

				}

				// orderby
				if( $filter_info['filter'] == 'orderby' ){

					// order by a column
					if( ! empty( $_GET[ $data['id'] . '_' . 'orderby' ] ) && substr( $_GET[ $data['id'] . '_' . 'orderby' ], 0, 7 ) == 'column_' ){
						$col_index = substr( $_GET[ $data['id'] . '_' . 'orderby' ], 7 );
						$device = $_GET[ $data['id'] . '_' . 'device' ];
						if( ! in_array( $device, array( 'laptop', 'tablet', 'phone' ) ) ){
							$device = 'laptop';
						}
						$order = strtolower( $_GET[ $data['id'] . '_' . 'order' ] );
						if( ! in_array( $order, array( 'asc', 'desc' ) ) ){
							$order = 'asc';
						}

						$column_sorting = wcpt_get_column_sorting_info( $col_index, $device );

						if( $column_sorting['orderby'] == 'price' && $order == 'desc' ){
							$filter_info['orderby'] = 'price-desc';

						}else{
							$filter_info['orderby'] = $column_sorting['orderby'];

						}

						$filter_info['meta_key'] = $column_sorting['meta_key'];
						$filter_info['order'] = $order;

					}

					if( $filter_info['orderby'] == 'sku' ){
						$filter_info['orderby'] = 'meta_value';
						$filter_info['meta_key'] = '_sku';

					}else if( $filter_info['orderby'] == 'sku_num' ){
						$filter_info['orderby'] = 'meta_value_num';
						$filter_info['meta_key'] = '_sku';

					}else if( $filter_info['orderby'] == 'date' ){
						$filter_info['order'] = 'DESC';

					}

					$this->query_args['orderby'] = $filter_info['orderby'];
					$this->query_args['order'] = $filter_info['order'];

					if( ! empty( $filter_info['meta_key'] ) ){
						$this->query_args['meta_key'] = $filter_info['meta_key'];
					}

				}

				// price range
				if( $filter_info['filter'] == 'price_range' && ( ! empty( $filter_info['min_price'] ) || ! empty( $filter_info['max_price'] ) ) ){

					if( ! $filter_info['min_price'] ){
						unset( $filter_info['min_price'] );
					}

					if( ! $filter_info['max_price'] ){
						unset( $filter_info['max_price'] );
					}

					$meta_query = wc_get_min_max_price_meta_query( $filter_info );
					$meta_query['price_filter'] = true;
					$this->query_args['meta_query']['price_filter'] = $meta_query;

				}

				// on sale
				if( $filter_info['filter'] == 'on_sale' ){
					$this->query_args['post__in'] = wc_get_product_ids_on_sale();

				}

				// search
				if( $filter_info['filter'] == 'search' && ! empty( $filter_info['keyword'] ) ){
					if( empty( $filter_info['target'] ) ){
						$filter_info['target'] = 'title+content';
					}

					switch ($filter_info['target']) {
						case 'title':
							$this->search_keyword['title'] = $filter_info['keyword'];
							break;

						case 'content':
							$this->search_keyword['content'] = $filter_info['keyword'];
							break;

						case 'title+content':
							$this->search_keyword['title'] = $filter_info['keyword'];
							$this->search_keyword['content'] = $filter_info['keyword'];
							break;
					}

					if( $filter_info['target'] == 'custom_fields'  ){
						if( ! empty( $filter_info['custom_fields'] ) ){
							$arr = preg_split('/\r\n|\r|\n/', $filter_info['custom_fields']);
							$mq = array();
							foreach( $arr as $cf ){
								$mq[] = array(
									'key'    => $cf,
									'value'  => $filter_info['keyword'],
									'compare' => 'LIKE'
								);
							}
							if( count( $mq ) > 1 ){
								$mq['relation'] = 'OR';
							}
							$this->query_args['meta_query'][] = $mq;
						}

					}else{
						add_filter( 'posts_where', array( $this, 'search' ) );
					}

				}

			}
		}


		// Categories are essential for the query.
		// Esure they are there regardless of whether user set them or not
		if( ! $user_set_cats ){
			$terms = array();
			if( empty( $data['query']['category'] ) ){
				$terms = get_terms(
					array(
						'taxonomy' => 'product_cat',
						'fields' => 'tt_ids',
						'hide_empty' => false,
					)
				);
			}else{
				$terms = explode( ',', $data['query']['category'] );
			}

			$this->query_args['tax_query'][] = array(
				'taxonomy' 	=> 'product_cat',
				'field'    	=> 'term_taxonomy_id',
				'terms'			=> $terms,
			);
		}

		// force excludes regardless of what user chose
		if( ! empty( $data['query']['sc_attrs']['exclude_category'] ) ){
			$this->query_args['tax_query'][] = array(
				'taxonomy' 	=> 'product_cat',
				'field'    	=> 'name',
				'terms'			=> explode( ',', $data['query']['sc_attrs']['exclude_category'] ),
				'operator' => 'NOT IN',
			);
		}

		// apply pagination
		if( ! empty( $_REQUEST[ $table_id . '_paged' ] ) ){
			$this->query_args['paged'] = (int) $_REQUEST[ $table_id . '_paged' ];
		}

		// parse
		$this->query_args = $this->parse_query_args();

		// parse additional query args string

		if( ! empty( $this->attributes['additional_query_args'] ) ){
			$this->query_args = wp_parse_args( $this->attributes['additional_query_args'], $this->query_args );
		}

		if(
			$this->only_loop
		){
			$products = $GLOBALS['wp_query'];

		}else if(
			! empty( $data['query']['sc_attrs']['product_variations'] ) &&
			function_exists('wcpt_product_variations_query')
		){
		// product variations
			$products = wcpt_product_variations_query($this->query_args);

		}else{
		// regular products
			$products = $this->get_products();

		}

		$this->remove_ordering_args();

		remove_filter( 'posts_where', array( $this, 'search' ) );

		$GLOBALS['wcpt_products'] = $products;

		// print navigation
		if( ! $this->only_loop ){
			echo apply_filters( 'wcpt_navigation', $nav );
		}

		$GLOBALS['wcpt_row_rand'] = rand(0, 100000);

		if( $cache = $this->get_cache() ){
			echo $cache;

		}else{
			ob_start();

			foreach(
				array(
					'laptop' 	=> wcpt_get_device_columns_2('laptop'),
					'tablet' 	=> wcpt_get_device_columns_2('tablet'),
					'phone' 	=> wcpt_get_device_columns_2('phone'),
				) as $device => $columns
			){

				if(
					// ( ! $columns || ! count( $columns ) ) || // device has no columns
					( ! empty( $_GET[ $table_id . '_device' ] ) && $_GET[ $table_id . '_device' ] != $device ) // another device requested
				){
					// "loading device view" screen
					include( $template_folder . 'scroll-wrap-outer-open.php' );
					include( $template_folder . 'scroll-wrap-open.php' );
					wcpt_icon('loader', 'wcpt-device-view-loading-icon');
					include( $template_folder . 'scroll-wrap-close.php' );
					include( $template_folder . 'scroll-wrap-outer-close.php' );

					continue;
				}

				$GLOBALS['wcpt_device'] = $device;

				if ( $products->have_posts() ) {

					do_action( "woocommerce_shortcode_before_{$this->type}_loop", $this->attributes );

					include( $template_folder . 'scroll-wrap-outer-open.php' );
					include( $template_folder . 'scroll-wrap-open.php' );
					include( $template_folder . 'table-open.php' );

					// column headings row
					include( $template_folder . 'heading-row.php' );

					// product rows
					while ( $products->have_posts() ) {
						$products->the_post();

						$GLOBALS['wcpt_row_rand']++;

						// Set custom product visibility when quering hidden products.
						add_action( 'woocommerce_product_is_visible', array( $this, 'set_product_as_visible' ) );

						global $product;

						$product = apply_filters('wcpt_product', $product, $this->attributes);

						ob_start();

						include( $template_folder . 'row-open.php' );
						if( ! empty( $columns ) ){
							foreach( $columns as $column_index => $column ){

								wcpt_parse_style_2($column['cell']);

								include( $template_folder . 'cell-open.php' );
								include( $template_folder . 'cell-value-open.php' );

								echo wcpt_parse_2( $column['cell']['template'], $product );

								include( $template_folder . 'cell-value-close.php' );
								include( $template_folder . 'cell-close.php' );

							}
						}
						include( $template_folder . 'row-close.php' );

						echo apply_filters( 'wcpt_row', ob_get_clean() );

						// Restore product visibility.
						remove_action( 'woocommerce_product_is_visible', array( $this, 'set_product_as_visible' ) );
					}

					woocommerce_reset_loop();
					wp_reset_postdata();

					include( $template_folder . 'table-close.php' );
					include( $template_folder . 'scroll-wrap-close.php' );
					include( $template_folder . 'scroll-wrap-outer-close.php' );

					if( ! empty( $this->attributes['paginate'] ) && ! $this->only_loop ){
						include( $template_folder . 'pagination.php' );
					}

					include( $template_folder . 'loading-screen.php' );

				} else {

					include( $template_folder . 'no-results.php' );

				}

			}

			wcpt_item_styles();

			$markup = ob_get_clean();
			echo $markup;

			$this->set_cache($markup);

		}

		// update cart info
		if( wp_doing_ajax() ){
			?>
			<script type="text/javascript">
				if( typeof wcpt_update_cart_items !== 'undefined' ){
					wcpt_update_cart_items( <?php echo json_encode( WC()->cart->get_cart() ); ?> );
				}
			</script>
			<?php
		}

		// edit table link
		if( current_user_can( 'edit_others_wc_product_tables' ) ){
			?>
			<div class="wcpt-edit-wrapper">
				<a class="wcpt-edit" target="_blank" href="<?php echo get_edit_post_link( $table_id ); ?>">Edit table</a>
			</div>
			<?php
		}

		include( $template_folder . 'container-close.php' );

		if( ! wp_doing_ajax() ){
			include( $template_folder . 'form-loading-screen.php' );
		}

		return ob_get_clean();

	}

	public function order_by_asc_popularity_post_clauses( $args ) {
		global $wpdb;
		$args['orderby'] = "$wpdb->postmeta.meta_value+0 ASC, $wpdb->posts.post_date DESC";
		return $args;
	}

	/**
	 * Remove ordering queries.
	 */
	public function remove_ordering_args() {
		remove_filter( 'posts_clauses', array( $this, 'order_by_asc_popularity_post_clauses' ) );
		remove_filter( 'get_meta_sql', array( $this, 'cast_decimal_precision') );
	}

	protected function parse_query_args() {
		$query_args_essential = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'orderby'							=> 'price',
			'order'								=> 'DESC',
		);

		$query_args = array_merge( $query_args_essential, $this->query_args );

		if( $query_args['orderby'] == 'price-desc' ){
			$query_args['orderby'] = 'price';
			$query_args['order'] = 'DESC';

		}else if( $query_args['orderby'] == 'price' ){
			$query_args['orderby'] = 'price';
			$query_args['order'] = 'ASC';

		}else if( $query_args['orderby'] == 'rating' ){
			$query_args['order'] = 'DESC';

		}

		$ordering_args                = WC()->query->get_catalog_ordering_args( $query_args['orderby'], $query_args['order'] );
		$query_args['orderby']        = $ordering_args['orderby'];
		$query_args['order']          = $ordering_args['order'];

		if ( $ordering_args['meta_key'] ) {
			$query_args['meta_key']       = $ordering_args['meta_key'];
		}

		$query_args['posts_per_page'] = intval( $this->attributes['limit'] );
		if ( 1 < $this->attributes['page'] ) {
			$query_args['paged']          = absint( $this->attributes['page'] );
		}

		if( ! empty( $this->attributes['offset'] ) ){
			$query_args['offset'] = $this->attributes['offset'];
		}

		if( empty( $query_args['tax_query'] ) ){
			$query_args['tax_query']      = array();
		}

		// Visibility.
		if( empty( $this->attributes['include_hidden'] ) ){
			$this->set_visibility_query_args( $query_args );
		}

		// SKUs.
		$this->set_skus_query_args( $query_args );

		// IDs.
		$this->set_ids_query_args( $query_args );

		// Set specific types query args.
		if ( method_exists( $this, "set_{$this->type}_query_args" ) ) {
			$this->{"set_{$this->type}_query_args"}( $query_args );
		}

		// Attributes.
		$this->set_attributes_query_args( $query_args );

		// Categories.
		// $this->set_categories_query_args( $query_args );

		// Tags.
		$this->set_tags_query_args( $query_args );

		$query_args = apply_filters( 'woocommerce_shortcode_products_query', $query_args, $this->attributes, $this->type );

		// Always query only IDs.
		$query_args['fields'] = 'ids';

		return $query_args;
	}

	protected function parse_attributes( $attributes ) {

		if( empty( $GLOBALS['wcpt_table_data']['query']['sc_attrs'] ) ){
			$GLOBALS['wcpt_table_data']['query']['sc_attrs'] = array();
		}

		$GLOBALS['wcpt_table_data']['query']['sc_attrs'] = $attributes;

		// don't want wc sc to process these its own way
		foreach( array( 'attribute', 'custom_field' ) as $key ){
			if( ! empty( $attributes[$key] ) ){
				unset( $attributes[$key] );
			}
		}

		foreach( array( 'name', 'id' ) as $key ){
			if( ! empty( $GLOBALS['wcpt_table_data']['query']['sc_attrs'][$key] ) ){
				unset( $GLOBALS['wcpt_table_data']['query']['sc_attrs'][$key] );
			}
		}

		if( ! defined( 'WCPT_PRO' ) ){
			foreach( $GLOBALS['wcpt_table_data']['query']['sc_attrs'] as $key => $val ){
				if( stristr($key, 'freeze') ){
					unset( $GLOBALS['wcpt_table_data']['query']['sc_attrs'][$key] );
				}
			}
		}

		$attributes = $this->parse_legacy_attributes( $attributes );

		$attributes = shortcode_atts( array(
			'limit'          => '-1',      // Results limit.
			'columns'        => '3',       // Number of columns.
			'rows'           => '',        // Number of rows. If defined, limit will be ignored.
			'orderby'        => 'title',   // menu_order, title, date, rand, price, popularity, rating, or id.
			'order'          => 'ASC',     // ASC or DESC.
			'ids'            => '',        // Comma separated IDs.
			'skus'           => '',        // Comma separated SKUs.
			'category'       => '',        // Comma separated category slugs.
			'nav_category'	 => '',
			'cat_operator'   => 'IN',      // Operator to compare categories. Possible values are 'IN', 'NOT IN', 'AND'.
			'attribute'      => '',        // Single attribute slug.
			'terms'          => '',        // Comma separated term slugs.
			'terms_operator' => 'IN',      // Operator to compare terms. Possible values are 'IN', 'NOT IN', 'AND'.
			'tag'            => '',        // Comma separated tag slugs.
			'visibility'     => 'visible', // Possible values are 'visible', 'catalog', 'search', 'hidden'.
			'class'          => '',        // HTML class.
			'page'           => 1,         // Page for pagination.
			'paginate'       => false,     // Should results be paginated.
			'cache'          => false,      // Should shortcode output be cached.

			'exclude_category'=> '',       // Comma separated category slugs.
			'include_hidden'	=> false,    // Hidden from shop / search.
			'offset'         	=> 0,      	 // Post offset.
			'id'						 	=> 0,				 // Table id.
			'_archive'				=> false,
			'_only_loop'			=> false,
		), $attributes, $this->type );

		// only render loop based on gloal query
		// hide navigation and pagination
		if( $attributes['_only_loop'] ){
			$this->only_loop = true;
		}

		// shortcode attrs
		//-- cache
		$this->caching = !! $attributes['cache'];

		//-- category
		$query =& $GLOBALS['wcpt_table_data']['query'];
		if( $attributes['category'] == '_all'  ){
			$query['category'] = array();

		}

		// nav_category: categories to show in navigation
		if( ! empty( $attributes['nav_category'] ) ){
			// modify the original set of categories
			$query['category'] = get_terms(array(
					'taxonomy' => 'product_cat',
					'fields' => 'tt_ids',
					'name'=> explode( ',', $attributes['nav_category'] ),
					'hide_empty' => false,
			));
		}

		// ### exclude category, ensure it is removed at this level too

		//-- ids
		if( ! empty( $attributes['ids'] ) ){
			$query['ids'] = $attributes['ids'];
		}

		//-- skus
		if( ! empty( $attributes['skus'] ) ){
			$query['skus'] = $attributes['skus'];
		}

		// limit
		if( ! empty( $GLOBALS['wcpt_table_data']['query']['sc_attrs']['limit'] ) ){
			$query['limit'] = (int) $GLOBALS['wcpt_table_data']['query']['sc_attrs']['limit'];
		}

		if( ! empty( $query['category'] ) && is_array( $query['category'] ) ){
			$query['category'] = array_map('intval', array_unique($query['category']));
			// cats need to be comma separated string
			$query['category'] = implode( ',', $query['category'] );
		}

		if( ! empty( $query['original_category'] ) && is_array( $query['original_category'] ) ){
			$query['original_category'] = array_map('intval', array_unique($query['original_category']));
			// cats need to be comma separated string
			$query['original_category'] = implode( ',', $query['original_category'] );
		}

		$attributes = array_merge( $attributes, $query );

		// offset
		if( isset( $GLOBALS['wcpt_table_data']['query']['sc_attrs']['offset'] ) ){
			$attributes['offset'] = (int) $attributes['offset'];
			$attributes['paginate'] = false;
		}

		return apply_filters('wcpt_parse_attributes', $attributes);
	}

	public function nav_later($nav){
		$GLOBALS['wcpt_nav_later_flag'] = true;
		foreach( $GLOBALS['wcpt_nav_later'] as $elm ){
			extract( $elm );
			$markup = wcpt_parse_ctx_2( $element, $elm_tpl, $elm_type, $product );
			$nav = str_replace( $placeholder, $markup, $nav );
		}
		$GLOBALS['wcpt_nav_later_flag'] = false;

		return $nav;
	}

	public function cast_decimal_precision( $array ) {
		$array['where'] = str_replace('DECIMAL','DECIMAL(10,3)',$array['where']);
		return $array;
	}

	public function search( $where_clause ){
		global $wpdb, $wp_query;

		$str = '';

		if( $this->search_keyword['title'] ){
			$like = '%' . $wpdb->esc_like( $this->search_keyword['title'] ) . '%';
			$str .= $wpdb->prepare(" {$wpdb->posts}.post_title LIKE %s ", $like);
		}

		if( $this->search_keyword['content'] ){

			if( $this->search_keyword['title'] ){
				$str .= " OR ";
			}

			$like = '%' . $wpdb->esc_like( $this->search_keyword['content'] ) . '%';
			$str .= $wpdb->prepare(" {$wpdb->posts}.post_content LIKE %s ", $like);
		}

		if( $str ){
			$where_clause .= " AND ( ". $str ." ) ";
		}

		return $where_clause;
	}

}
