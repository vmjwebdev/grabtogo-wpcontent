<?php

if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Listeo_Core_Search class.
 */
class Listeo_Core_Search
{


	public $found_posts = 0;
	/**
	 * Constructor
	 */
	public function __construct()
	{


		add_action('pre_get_posts', array($this, 'pre_get_posts_listings'), 0);
		add_action('pre_get_posts', array($this, 'remove_products_from_search'), 0);
		// add_filter( 'posts_orderby', array( $this, 'featured_filter' ), 10, 2 );
		// add_filter( 'posts_request', array( $this, 'featured_filter' ), 10, 2 );


		add_filter('posts_results', array($this, 'open_now_results_filter'));
		add_filter('found_posts', array($this, 'open_now_results_filter_pagination'), 1, 2);

		//add_action( 'parse_tax_query', array( $this, 'parse_tax_query_listings' ), 1 );
		add_shortcode('listeo_search_form', array($this, 'output_search_form'));

		add_filter('query_vars', array($this, 'add_query_vars'));

		add_action('parse_query', [$this, 'admin_search_by_category']);
		add_action('restrict_manage_posts',  [$this, 'admin_filter_search_by_category']);

		if (get_option('listeo_search_name_autocomplete')) {
			add_action('wp_print_footer_scripts', array(__CLASS__, 'wp_print_footer_scripts'), 11);
			add_action('wp_ajax_listeo_core_incremental_listing_suggest', array(__CLASS__, 'wp_ajax_listeo_core_incremental_listing_suggest'));
			add_action('wp_ajax_nopriv_listeo_core_incremental_listing_suggest', array(__CLASS__, 'wp_ajax_listeo_core_incremental_listing_suggest'));
		}

		add_action('wp_ajax_nopriv_listeo_get_listings', array($this, 'ajax_get_listings'));
		add_action('wp_ajax_listeo_get_listings', array($this, 'ajax_get_listings'));

		add_action('wp_ajax_nopriv_listeo_get_features_from_category', array($this, 'ajax_get_features_from_category'));
		add_action('wp_ajax_listeo_get_features_from_category', array($this, 'ajax_get_features_from_category'));

		add_action('wp_ajax_nopriv_listeo_get_features_ids_from_category', array($this, 'ajax_get_features_ids_from_category'));
		add_action('wp_ajax_listeo_get_features_ids_from_category', array($this, 'ajax_get_features_ids_from_category'));

		add_action('wp_ajax_nopriv_listeo_get_listing_types_from_categories', array($this, 'ajax_get_listing_types_from_categories'));
		add_action('wp_ajax_listeo_get_listing_types_from_categories', array($this, 'ajax_get_listing_types_from_categories'));

		add_filter('posts_where', array($this, 'listeo_date_range_filter'));
	}

	function admin_filter_search_by_category()
	{
		global $typenow;
		$post_type = 'listing'; // change to your post type
		$taxonomy  = 'listing_category'; // change to your taxonomy
		if ($typenow == $post_type) {
			$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
			$info_taxonomy = get_taxonomy($taxonomy);
			wp_dropdown_categories(array(
				'show_option_all' => sprintf(__('Show all %s', 'listeo_core'), $info_taxonomy->label),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			));
		};
	}

	function admin_search_by_category($query)
	{
		global $pagenow;
		$post_type = 'listing'; // change to your post type
		$taxonomy  = 'listing_category'; // change to your taxonomy
		$q_vars    = &$query->query_vars;
		if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
			$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}


	function listeo_date_range_filter($where)
	{

		global $wpdb;
		global $wp_query;
		if (!isset($wp_query) || !method_exists($wp_query, 'get'))
			return;

		$date_range = get_query_var('date_range');

		if (!empty($date_range)) :
			//TODO replace / with - if first is day - month- year
			$dates = explode(' - ', $date_range);
			//setcookie('listeo_date_range', $date_range, time()+31556926);
			$date_start = $dates[0];
			$date_end = $dates[1];

			$date_start_object = DateTime::createFromFormat('!' . listeo_date_time_wp_format_php(), $date_start);
			$date_end_object = DateTime::createFromFormat('!' . listeo_date_time_wp_format_php(), $date_end);

			if (!$date_start_object || !$date_end_object) {
				return $where;
			}
			$format_date_start 	= esc_sql($date_start_object->format("Y-m-d H:i:s"));
			//$format_date_end 	= esc_sql($date_end_object->modify('+23 hours 59 minutes 59 seconds')->format("Y-m-d H:i:s"));
			$format_date_end = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($format_date_end)));



			// $where .= $GLOBALS['wpdb']->prepare(  " AND {$wpdb->prefix}posts.ID ".
			//     'NOT IN ( '.
			//         'SELECT listing_id '.
			//         "FROM {$wpdb->prefix}bookings_calendar ".
			//         'WHERE 
			//     	(( %s > date_start AND %s < date_end ) 
			//     	OR 
			//     	( %s > date_start AND %s < date_end ) 
			//     	OR 
			//     	( date_start >= %s AND date_end <= %s ))
			//     	AND type = "reservation" AND NOT status="cancelled" AND NOT status="expired"
			//     	GROUP BY listing_id '.
			//     ' ) ', $format_date_start, $format_date_start, $format_date_end,  $format_date_end, $format_date_start, $format_date_end );
			$where .= $GLOBALS['wpdb']->prepare(
				" AND {$wpdb->prefix}posts.ID NOT IN ( 
                SELECT DISTINCT listing_id 
                FROM {$wpdb->prefix}bookings_calendar 
                WHERE 
                (
                    (date_start < %s AND date_end > %s) 
                    OR (date_start < %s AND date_end > %s) 
                    OR (date_start >= %s AND date_end <= %s)
                    OR (date_start = %s AND date_end = %s)
                )
                AND type = 'reservation' 
                AND status NOT IN ('cancelled', 'expired')
            )",
				$format_date_end,
				$format_date_start,
				$format_date_start,
				$format_date_start,
				$format_date_start,
				$format_date_end,
				$format_date_start,
				$format_date_end
			);

		endif;

		return $where;
	}

	public function remove_products_from_search($query)
	{

		/* check is front end main loop content */
		if (is_admin() || !$query->is_main_query()) return;

		/* check is search result query */
		if ($query->is_search()) {
			if (isset($_GET['post_type']) && $_GET['post_type'] == 'product') {
			} else {
				$post_type_to_remove = 'product';
				/* get all searchable post types */
				$searchable_post_types = get_post_types(array('exclude_from_search' => false));

				/* make sure you got the proper results, and that your post type is in the results */
				if (is_array($searchable_post_types) && in_array($post_type_to_remove, $searchable_post_types)) {
					/* remove the post type from the array */
					unset($searchable_post_types[$post_type_to_remove]);
					/* set the query to the remaining searchable post types */
					$query->set('post_type', $searchable_post_types);
				}
			}
		}
	}


	public function open_now_results_filter($posts)
	{

		if (isset($_GET['open_now'])) {
			$filtered_posts = array();

			foreach ($posts as $post) {
				if (listeo_check_if_open($post)) {
					$filtered_posts[] = $post;
				}
			}
			$this->found_posts = count($filtered_posts);;
			return $filtered_posts;
		}

		return $posts;
	}

	function open_now_results_filter_pagination($found_posts, $query)
	{
		if (isset($_GET['open_now'])) {
			// Define the homepage offset...
			$found_posts = $this->found_posts;
		}
		return $found_posts;
	}


	static function wp_print_footer_scripts()
	{
?>
		<script type="text/javascript">
			(function($) {
				$(document).ready(function() {

					$('#keyword_search.title-autocomplete').autocomplete({

						source: function(req, response) {
							$.getJSON('<?php echo admin_url('admin-ajax.php'); ?>' + '?callback=?&action=listeo_core_incremental_listing_suggest', req, response);
						},
						select: function(event, ui) {
							window.location.href = ui.item.link;
						},
						minLength: 3,
					});
				});

			})(this.jQuery);
		</script><?php
				}

				static function wp_ajax_listeo_core_incremental_listing_suggest()
				{

					$suggestions = array();
					$posts = get_posts(array(
						's' => $_REQUEST['term'],
						'post_type'     => 'listing',
					));
					global $post;
					$results = array();
					foreach ($posts as $post) {
						setup_postdata($post);
						$suggestion = array();
						$suggestion['label'] =  html_entity_decode($post->post_title, ENT_QUOTES, 'UTF-8');
						$suggestion['link'] = get_permalink($post->ID);

						$suggestions[] = $suggestion;
					}
					// JSON encode and echo
					$response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";
					echo $response;
					// Don't forget to exit!
					exit;
				}

				public function add_query_vars($vars)
				{

					$new_vars = $this->build_available_query_vars();
					$vars = array_merge($new_vars, $vars);
					return $vars;
				}

				public static function build_available_query_vars()
				{
					$query_vars = array();
					$taxonomy_objects = get_object_taxonomies('listing', 'objects');
					foreach ($taxonomy_objects as $tax) {
						array_push($query_vars, 'tax-' . $tax->name);
					}



					$service = Listeo_Core_Meta_Boxes::meta_boxes_service();
					foreach ($service['fields'] as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					$location = Listeo_Core_Meta_Boxes::meta_boxes_location();

					foreach ($location['fields'] as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					$event = Listeo_Core_Meta_Boxes::meta_boxes_event();
					foreach ($event['fields']  as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					$prices = Listeo_Core_Meta_Boxes::meta_boxes_prices();
					foreach ($prices['fields']  as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					$contact = Listeo_Core_Meta_Boxes::meta_boxes_contact();

					foreach ($contact['fields']  as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					$rental = Listeo_Core_Meta_Boxes::meta_boxes_rental();
					foreach ($rental['fields']  as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					$classifieds = Listeo_Core_Meta_Boxes::meta_boxes_classifieds();
					foreach ($classifieds['fields']  as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					$custom = Listeo_Core_Meta_Boxes::meta_boxes_custom();
					foreach ($custom['fields']  as $key => $field) {
						array_push($query_vars, $field['id']);
					}
					array_push($query_vars, '_price_range');
					array_push($query_vars, '_listing_type');
					//array_push($query_vars, '_verified');
					array_push($query_vars, '_price');
					array_push($query_vars, '_max_guests');
					array_push($query_vars, 'rating-filter');
					array_push($query_vars, '_min_guests');
					array_push($query_vars, '_instant_booking');
					return $query_vars;
				}

				public function pre_get_posts_listings($query)
				{

					if (is_admin() || ! $query->is_main_query()) {
						return $query;
					}
					if (!is_admin() && $query->is_main_query() && is_post_type_archive('listing')) {
						$per_page = get_option('listeo_listings_per_page', 10);
						$query->set('posts_per_page', $per_page);
						$query->set('post_type', 'listing');
						$query->set('post_status', 'publish');
					}

					if (is_tax('listing_category') || is_tax('service_category') || is_tax('event_category') || is_tax('rental_category') || is_tax('listing_feature')  || is_tax('region')) {

						$per_page = get_option('listeo_listings_per_page', 10);
						$query->set('posts_per_page', $per_page);
					}

					if (is_post_type_archive('listing') || is_author() || is_tax('listing_category') || is_tax('listing_feature') || is_tax('event_category') || is_tax('service_category') || is_tax('rental_category') || is_tax('region')) {

						$ordering_args = Listeo_Core_Listing::get_listings_ordering_args();

						if (isset($ordering_args['meta_key']) && $ordering_args['meta_key'] != '_featured') {
							$query->set('meta_key', $ordering_args['meta_key']);
						}

						$query->set('orderby', $ordering_args['orderby']);
						$query->set('order', $ordering_args['order']);

						$keyword = get_query_var('keyword_search');

						$date_range =  (isset($_REQUEST['date_range'])) ? sanitize_text_field($_REQUEST['date_range']) : '';

						$keyword_search = get_option('listeo_keyword_search', 'search_title');
						$search_mode = get_option('listeo_search_mode', 'exact');
						// make wp_query show only listings that have _event_date meta field value in future	



						$keywords_post_ids = array();
						$location_post_ids = array();
						if ($search_mode == 'fibosearch') {
						} else {
							if ($search_mode == 'relevance') {
								if ($keyword) {

									// Combine title, content, and meta searches
									$search_terms = array_map('trim', explode('+', $keyword));
									$search_string = implode(' ', $search_terms);
									global $wpdb;
									$post_ids = $wpdb->get_col($wpdb->prepare(
										"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
											WHERE meta_key = 'keywords' 
											AND meta_value LIKE %s",
										'%' . $wpdb->esc_like($keyword) . '%'
									));
									if(!empty($post_ids)){
										$keywords_post_ids = $post_ids;
							//clear default search
										$query->set('s', ''); // Clear the default search
									} else {
									
									// Set search parameters for wp_query
										$query->set('s', $search_string);
									}
								}
							} else if ($search_mode == 'searchwp') {
								// Use SearchWP to get post IDs matching the keyword
								// check if class SearchWP is exists
								if (!class_exists('SearchWP')) {
									return;
								}
								
								$searchwp_query = new \SearchWP\Query($keyword, [
									'engine'         => 'default',       // Replace with your engine name if different
									'fields'         => 'ids',           // Retrieve only the post IDs
									'posts_per_page' => -1,              // Get all matching posts
									'post_type'      => ['listing'],     // Limit search to 'listing' post type
								]);

								$keywords_post_ids = $searchwp_query->get_results();

								if (empty($keywords_post_ids)) {
									$keywords_post_ids = array(0); // No matching posts
								}
							} else if ($search_mode == 'exact' || $search_mode == 'approx') {
								

									if ($keyword) {
										global $wpdb;
										// Trim and explode keywords
										if ($search_mode == 'exact') {
											$keywords = array_map('trim', explode('+', $keyword));
										} else {
											$keywords = array_map('trim', explode(' ', $keyword));
										}

										// Setup SQL
										$posts_keywords_sql    = array();
										$postmeta_keywords_sql = array();
										// Loop through keywords and create SQL snippets
										foreach ($keywords as $keyword) {

											# code...
											if (strlen($keyword) > 2) {

												// Create post meta SQL
												if ($keyword_search == 'search_title') {

													$postmeta_keywords_sql[] = " meta_value LIKE '%" . esc_sql($keyword) . "%' AND meta_key IN ('listeo_subtitle','listing_title','listing_description','keywords') ";
												} else {
													$postmeta_keywords_sql[] = " meta_value LIKE '%" . esc_sql($keyword) . "%'";
												}

												// Create post title and content SQL
												$posts_keywords_sql[]    = " post_title LIKE '%" . esc_sql($keyword) . "%' OR post_content LIKE '%" . esc_sql($keyword) . "%' ";
											}
										}


										// Construct the final SQL queries using AND between different keywords
										if (!empty($postmeta_keywords_sql)) {
											$post_ids_meta = $wpdb->get_col("
								SELECT DISTINCT post_id FROM {$wpdb->postmeta}
								WHERE " . implode(' AND ', $postmeta_keywords_sql) . "
							");
										} else {
											$post_ids_meta = array();
										}

										if (!empty($posts_keywords_sql)) {
											$post_ids_posts = $wpdb->get_col("
								SELECT ID FROM {$wpdb->posts}
								WHERE " . implode(' AND ', $posts_keywords_sql) . "
								AND post_type = 'listing'
							");
										} else {
											$post_ids_posts = array();
										}


										// Merge and filter duplicates
										$keywords_post_ids = array_unique(array_merge($post_ids_meta, $post_ids_posts));
										if (empty($keywords_post_ids)) {
											$keywords_post_ids = array(0);
										}
									}
								
							}
						}
						$keywords_post_ids = apply_filters('listeo_search_keywords_post_ids', $keywords_post_ids, $keyword, $search_mode);
						
						$location = get_query_var('location_search');

						if ($location) {

							$radius = get_query_var('search_radius');
							if (empty($radius) && get_option('listeo_radius_state') == 'enabled') {
								$radius = get_option('listeo_maps_default_radius');
							}
							$radius_type = get_option('listeo_radius_unit', 'km');
							$geocoding_provider = get_option('listeo_geocoding_provider', 'google');
							if ($geocoding_provider == 'google') {
								$radius_api_key = get_option('listeo_maps_api_server');
							} else {
								$radius_api_key = get_option('listeo_geoapify_maps_api_server');
							}

							if (!empty($location) && !empty($radius) && !empty($radius_api_key)) {

								//search by google

								$latlng = listeo_core_geocode($location);

								$nearbyposts = listeo_core_get_nearby_listings($latlng[0], $latlng[1], $radius, $radius_type);

								listeo_core_array_sort_by_column($nearbyposts, 'distance');
								$location_post_ids = array_unique(array_column($nearbyposts, 'post_id'));

								if (empty($location_post_ids)) {
									$location_post_ids = array(0);
								}
							} else {

								//search by text
								global $wpdb;
								// Trim and explode keywords
								$locations = array_map('trim', explode(',', $location));

								// Setup SQL
								$posts_locations_sql = array();
								$postmeta_locations_sql = array();
								// Loop through keywords and create SQL snippets

								if (get_option('listeo_search_only_address', 'off') == 'on') {
									// Directly using the location with sensitivity to special characters
									$postmeta_locations_sql[] = $wpdb->prepare("meta_value LIKE %s AND meta_key = '_address'", '%' . $wpdb->esc_like($locations[0]) . '%');
									$postmeta_locations_sql[] = $wpdb->prepare("meta_value LIKE %s AND meta_key = '_friendly_address'", '%' . $wpdb->esc_like($locations[0]) . '%');
								} else {
									// Create post meta SQL
									$postmeta_locations_sql[] = $wpdb->prepare("meta_value LIKE %s", '%' . $wpdb->esc_like($locations[0]) . '%');
									// Create post title and content SQL
									$posts_locations_sql[] = $wpdb->prepare("post_title LIKE %s OR post_content LIKE %s", '%' . $wpdb->esc_like($locations[0]) . '%', '%' . $wpdb->esc_like($locations[0]) . '%');
								}

								// Get post IDs from post meta search
								$post_ids = $wpdb->get_col("
    SELECT DISTINCT post_id FROM {$wpdb->postmeta}
    WHERE " . implode(' OR ', $postmeta_locations_sql) . "
");

								// Merge with post IDs from post title and content search
								if (get_option('listeo_search_only_address', 'off') == 'on') {
									$location_post_ids = array_merge($post_ids, array(0));
								} else {
									$location_post_ids = array_merge($post_ids, $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts}
        WHERE (" . implode(' OR ', $posts_locations_sql) . ")
        AND post_type = 'listing'
        AND post_status = 'publish'
    "), array(0));
								}
							}
						}

						if (sizeof($keywords_post_ids) != 0 && sizeof($location_post_ids) != 0) {
							$post_ids = array_intersect($keywords_post_ids, $location_post_ids);
							if (!empty($post_ids)) {
								$query->set('post__in', $post_ids);
							} else {

								$query->set('post__in', array(0));
							}
						} else if (sizeof($keywords_post_ids) != 0 && sizeof($location_post_ids) == 0) {
							
							$query->set('post__in', $keywords_post_ids);
						} else if (sizeof($keywords_post_ids) == 0 && sizeof($location_post_ids) != 0) {

							$query->set('post__in', $location_post_ids);
						}
			

						if (! empty($post_ids)) {
							$query->set('post__in', $post_ids);
						}

						$query->set('post_type', 'listing');
						$args = array();
						$tax_query = array();

						$tax_query = array(
							'relation' => get_option('listeo_taxonomy_or_and', 'AND')
						);
						$taxonomy_objects = get_object_taxonomies('listing', 'objects');

						foreach ($taxonomy_objects as $tax) {
							$get_tax = get_query_var('tax-' . $tax->name);

							if (is_array($get_tax)) {

								$tax_query[$tax->name] = array('relation' => get_option('listeo_' . $tax->name . 'search_mode', 'OR'));

								foreach ($get_tax as $key => $value) {
									array_push($tax_query[$tax->name], array(
										'taxonomy' =>   $tax->name,
										'field'    =>   'slug',
										'terms'    =>   $value,

									));
								}
							} else {

								if ($get_tax) {

									$term = get_term_by('slug', $get_tax, $tax->name);
									if ($term) {
										array_push($tax_query, array(
											'taxonomy' =>  $tax->name,
											'field'    =>  'slug',
											'terms'    =>  $term->slug,
											'operator' =>  'IN'
										));
									}
								}
							}
						}

						// exlcude posts that are from ads
						// ads

						$category = get_query_var('tax-listing_category');
						$feature = get_query_var('tax-listing_feature');
						$region = get_query_var('tax-region');
						// region might be array, so we need to check if it is array
						if (is_array($region)) {
							$region = $region[0];
						}
						if (is_array($category)) {
							$category = $category[0];
						}
						if (is_array($feature)) {
							$feature = $feature[0];
						}


						$ad_filter = array(
							'listing_category' 	=> $category,
							'listing_feature'	=> $feature,
							'region' 			=> $region,
							'address' 			=> $location,
						);


						// get posts from ad
						$ads_ids = listeo_get_ids_listings_for_ads('search', $ad_filter);


						if (!empty($ads_ids)) {
							$query->set('post__not_in', $ads_ids);
						}




						$query->set('tax_query', $tax_query);

						$available_query_vars = $this->build_available_query_vars();

						$meta_queries = array();

						foreach ($available_query_vars as $key => $meta_key) {

							if (substr($meta_key, 0, 4) == "tax-") {
								continue;
							}
							if ($meta_key == '_price_range') {
								continue;
							}
							if ($meta_key == 'rating-filter') {
								// if (isset($args['rating-filter']) && $args['rating-filter'] != 'any') {
								$meta = get_query_var($meta_key);
								if ($meta && $meta != 'any') {
									$meta_queries[] = array(
										'key'     => 'listeo-avg-rating',
										'value'   => $meta,
										'compare' => '>='
									);
								}

								// }
								continue;
							}




							if (!empty($meta_min) && !empty($meta_max)) {

								$meta_queries[] = array(
									'key' =>  substr($meta_key, 0, -4),
									'value' => array($meta_min, $meta_max),
									'compare' => 'BETWEEN',
									'type' => 'NUMERIC'
								);
								$meta_max = false;
								$meta_min = false;
							} else if (!empty($meta_min) && empty($meta_max)) {
								$meta_queries[] = array(
									'key' =>  substr($meta_key, 0, -4),
									'value' => $meta_min,
									'compare' => '>=',
									'type' => 'NUMERIC'
								);
								$meta_max = false;
								$meta_min = false;
							} else if (empty($meta_min) && !empty($meta_max)) {
								$meta_queries[] = array(
									'key' =>  substr($meta_key, 0, -4),
									'value' => $meta_max,
									'compare' => '<=',
									'type' => 'NUMERIC'
								);
								$meta_max = false;
								$meta_min = false;
							}

							if ($meta_key == '_price') {
								$meta = get_query_var('_price_range');
								if (!empty($meta) && $meta != -1) {

									$range = array_map('absint', explode(',', $meta));

									$meta_queries[] = array(
										'relation' => 'OR',
										array(
											'relation' => 'OR',
											array(
												'key' => '_price_min',
												'value' => $range,
												'compare' => 'BETWEEN',
												'type' => 'NUMERIC',
											),
											array(
												'key' => '_price_max',
												'value' => $range,
												'compare' => 'BETWEEN',
												'type' => 'NUMERIC',
											),
											array(
												'key' => '_classifieds_price',
												'value' => $range,
												'compare' => 'BETWEEN',
												'type' => 'NUMERIC',
											),

										),
										array(
											'relation' => 'AND',
											array(
												'key' => '_price_min',
												'value' => $range[0],
												'compare' => '<=',
												'type' => 'NUMERIC',
											),
											array(
												'key' => '_price_max',
												'value' => $range[1],
												'compare' => '>=',
												'type' => 'NUMERIC',
											),

										),
									);
								}
							} else {
								if (substr($meta_key, -4) == "_min" || substr($meta_key, -4) == "_max") {
									continue;
								}

								if ($meta_key == '_max_guests') {
									$meta = get_query_var($meta_key);

									if ($meta && $meta != -1) {

										$meta_queries[] = array(
											'key' =>  '_max_guests',
											'value' => $meta,
											'compare' => '>=',
											'type' => 'NUMERIC'
										);
									}
								} else {

									$meta = get_query_var($meta_key);

									if ($meta && $meta != -1) {
										if (is_array($meta)) {
											$meta_queries[] = array(
												'key'     => $meta_key,
												'value'   => array_keys($meta),
											);
										} else {
											$meta_queries[] = array(
												'key'     => $meta_key,
												'value'   => $meta,
											);
										}
									}
								}
							}
						}

				


						$listing_type = get_query_var('_listing_type');
						if ($date_range && $listing_type == 'event') {
							//check to apply only for events
							$dates = explode(' - ', $date_range);
							//var_dump($dates);
							$date_start_obj = DateTime::createFromFormat(listeo_date_time_wp_format_php() . ' H:i:s', $dates[0] . ' 00:00:00');

							if ($date_start_obj) {
								$date_start = $date_start_obj->getTimestamp();
							} else {
								$date_start = false;
							}

							$date_end_obj = DateTime::createFromFormat(listeo_date_time_wp_format_php() . ' H:i:s', $dates[1] . ' 23:59:59');

							if ($date_end_obj) {
								$date_end = $date_end_obj->getTimestamp();
							} else {
								$date_end = false;
							}

							if ($date_start && $date_end) {

								$meta_queries[] = array(
									'relation' => 'OR',
									array(
										'key' => '_event_date_timestamp',
										'value' => array($date_start, $date_end),
										'compare' => 'BETWEEN',
										'type' => 'NUMERIC'
									),
									array(
										'key' => '_event_date_end_timestamp',
										'value' => array($date_start, $date_end),
										'compare' => 'BETWEEN',
										'type' => 'NUMERIC'
									),

								);
							}
						}


						// var_dump($meta_queries);
						if (isset($ordering_args['meta_key']) && $ordering_args['meta_key'] == '_featured') {


							$query->set('order', 'ASC DESC');
							$query->set('orderby', 'meta_value date');
							$query->set('meta_key', '_featured');
						}

						if (isset($ordering_args['listeo_key']) && $ordering_args['listeo_key'] == '_event_date_timestamp') {
							//  $query->set('order', ' DESC');
							// //$query->set('meta_key', '_event_date_timestamp');
							// $query->set('orderby', 'meta_value_num ID');
							// $meta_queries[] =  array(
							// 	'relation' => 'OR', 
							// 	array(
							// 		'key' => '_event_date_timestamp',
							// 		'value' => current_time( 'timestamp' ),
							// 		'compare' => '>',
							// 		'type' => 'numeric'
							// 	),
							// 	array(
							// 		'key' => '_event_date_timestamp', // Include an empty check for the key itself
							// 		'compare' => 'NOT EXISTS',
							// 	),

							// );



							$query->set('meta_query', [
								'relation' => 'OR',
								'has_event_date' => [
									'key' => '_event_date_timestamp',
									'value' => current_time('timestamp'),
									'compare' => '>',
									'type' => 'numeric'
								],
								'no_event_date' => [
									'key' => '_event_date_timestamp',
									'compare' => 'NOT EXISTS',
								],
							]);

							$query->set('orderby', [
								'has_event_date' => 'DESC',
								'event_date_distance' => 'ASC',
								'date' => 'DESC',
							]);

							$query->set('meta_type', 'NUMERIC');
							$query->set('listeo_custom_event_order', true);
							// Custom ordering function
							add_filter('posts_orderby', 'listeo_custom_posts_orderby', 10, 2);
							// add_filter('posts_orderby', function ($orderby, $wp_query) use ($current_timestamp) {
							// 	if ($wp_query->is_main_query()) {
							// 		global $wpdb;
							// 		$orderby = "
							//     CASE
							//         WHEN {$wpdb->postmeta}.meta_key = '_event_date_timestamp' THEN 1
							//         ELSE 0
							//     END DESC,
							//     ABS({$wpdb->postmeta}.meta_value - $current_timestamp) ASC,
							//     {$wpdb->posts}.post_date DESC
							// ";
							// 	}
							// 	return $orderby;
							// }, 10, 2);
						}

						if (isset($args['rating-filter']) && $args['rating-filter'] != 'any') {

							$query_args['meta_query'][] = array(
								'key'     => 'listeo-avg-rating',
								'value'   => $args['rating-filter'],
								'compare' => '>='
							);
						}

						if (!empty($meta_queries)) {
							$query->set('meta_query', array(
								'relation' => 'AND',
								$meta_queries
							));
						}
					}
					// 	ini_set('xdebug.var_display_max_depth', '10');
					//  ini_set('xdebug.var_display_max_children', '256');
					//  ini_set('xdebug.var_display_max_data', '1024');
					// echo '<pre>';
					// var_dump($query);
					// echo '</pre>';		
					return $query;
				} /*eof function*/


				public function ajax_get_listings()
				{


					global $wp_post_types;

					$template_loader = new Listeo_Core_Template_Loader;

					$location  	= (isset($_REQUEST['location_search'])) ? sanitize_text_field(stripslashes($_REQUEST['location_search'])) : '';
					$keyword   	= (isset($_REQUEST['keyword_search'])) ? sanitize_text_field(stripslashes($_REQUEST['keyword_search'])) : '';
					$radius   	= (isset($_REQUEST['search_radius'])) ?  sanitize_text_field(stripslashes($_REQUEST['search_radius'])) : '';
					$rating   	= (isset($_REQUEST['rating-filter'])) ?  sanitize_text_field(stripslashes($_REQUEST['rating-filter'])) : '';


					$orderby   	= (isset($_REQUEST['orderby'])) ?  sanitize_text_field(stripslashes($_REQUEST['orderby'])) : '';
					$order   	= (isset($_REQUEST['order'])) ?  sanitize_text_field(stripslashes($_REQUEST['order'])) : '';

					$style   	= sanitize_text_field(stripslashes($_REQUEST['style']));
					$grid_columns  = sanitize_text_field(stripslashes($_REQUEST['grid_columns']));
					$per_page   = sanitize_text_field(stripslashes($_REQUEST['per_page']));
					$date_range =  (isset($_REQUEST['date_range'])) ? sanitize_text_field($_REQUEST['date_range']) : '';


					$region   	= (isset($_REQUEST['tax-region'])) ?  sanitize_text_field($_REQUEST['tax-region']) : '';
					$category   	= (isset($_REQUEST['tax-listing_category'])) ?  sanitize_text_field($_REQUEST['tax-listing_category']) : '';
					$feature   	= (isset($_REQUEST['tax-listing_feature'])) ?  sanitize_text_field($_REQUEST['tax-listing_feature']) : '';


					$map_bounds = array();
					if (isset($_REQUEST['map_bounds']) && is_array($_REQUEST['map_bounds'])) {
						$map_bounds = array_map('sanitize_text_field', $_REQUEST['map_bounds']);
					}
					$search_by_map_move = (isset($_REQUEST['search_by_map_move'])) ? sanitize_text_field($_REQUEST['search_by_map_move']) : '';

					$date_start = '';
					$date_end = '';

					if ($date_range) {

						$dates = explode(' - ', $date_range);
						$date_start = $dates[0];
						$date_end = $dates[1];

						// $date_start = esc_sql ( date( "Y-m-d H:i:s", strtotime(  $date_start )  ) );
						//    $date_end = esc_sql ( date( "Y-m-d H:i:s", strtotime( $date_end ) )  );

					}

					if (empty($per_page)) {
						$per_page = get_option('listeo_listings_per_page', 10);
					}

					$query_args = array(
						'ignore_sticky_posts'    => 1,
						'post_type'         => 'listing',
						'orderby'           => $orderby,
						'order'             =>  $order,
						'offset'            => (absint($_REQUEST['page']) - 1) * absint($per_page),
						'location'   		=> $location,
						'keyword'   		=> $keyword,
						'search_radius'   	=> $radius,
						'rating-filter'   	=> $rating,
						'posts_per_page'    => $per_page,
						'date_start'    	=> $date_start,
						'date_end'    		=> $date_end,
						'tax-region'    		=> $region,
						'tax-listing_feature'   => $feature,
						'tax-listing_category'  => $category,
						'map_bounds' => $map_bounds,
						'search_by_map_move' => $search_by_map_move

					);

					$query_args['listeo_orderby'] = (isset($_REQUEST['listeo_core_order'])) ? sanitize_text_field($_REQUEST['listeo_core_order']) : false;

					$taxonomy_objects = get_object_taxonomies('listing', 'objects');
					foreach ($taxonomy_objects as $tax) {
						if (isset($_REQUEST['tax-' . $tax->name])) {
							$query_args['tax-' . $tax->name] = $_REQUEST['tax-' . $tax->name];
						}
					}

					$available_query_vars = $this->build_available_query_vars();
					foreach ($available_query_vars as $key => $meta_key) {

						if (isset($_REQUEST[$meta_key]) && $_REQUEST[$meta_key] != -1) {

							$query_args[$meta_key] = $_REQUEST[$meta_key];
						}
					}




					// add meta boxes support

					$orderby = isset($_REQUEST['listeo_core_order']) ? $_REQUEST['listeo_core_order'] : 'date';


					// if ( ! is_null( $featured ) ) {
					// 	$featured = ( is_bool( $featured ) && $featured ) || in_array( $featured, array( '1', 'true', 'yes' ) ) ? true : false;
					// }


					$listings = Listeo_Core_Listing::get_real_listings(apply_filters('listeo_core_output_defaults_args', $query_args));
					$result = array(
						'found_listings'    => $listings->have_posts(),
						'max_num_pages' => $listings->max_num_pages,
					);

					ob_start();
					if ($result['found_listings']) {
						$style_data = array(
							'style' 		=> $style,
							//				'class' 		=> $custom_class, 
							//'in_rows' 		=> $in_rows, 
							'grid_columns' 	=> $grid_columns,
							'max_num_pages'	=> $listings->max_num_pages,
							'counter'		=> $listings->found_posts
						);
						//$template_loader->set_template_data( $style_data )->get_template_part( 'listings-start' ); 
					?>
			<div class="loader-ajax-container" style="">
				<div class="loader-ajax"></div>
			</div>


			<?php
						// get posts from ad
						$ad_filter = array(
							'listing_category' 	=> $category,
							'listing_feature'	=> $feature,
							'region' 			=> $region,
							'address' 			=> $location,
						);

						// get posts from ad
						$ads = listeo_get_ids_listings_for_ads('search', $ad_filter);

						if (!empty($ads)) {
							$ad_posts_count = count($ads);
							$ad_posts_index = 0;
							$ads_args = array(
								'post_type' => 'listing',
								'post_status' => 'publish',
								'posts_per_page' => 4,
								'orderby' => 'rand',

								'post__in' => $ads,
							);
							$ads_query = new \WP_Query($ads_args);

							if ($ads_query->have_posts()) {
								while ($ads_query->have_posts()) {
									$ads_query->the_post();
									$ad_posts_index++;
									$ad_data = array(
										'ad' => true,
										'ad_id' => get_the_ID(),
									);
									// merge ad data with style data
									$stylead_data = array_merge($style_data, $ad_data);
									$template_loader->set_template_data($stylead_data)->get_template_part('content-listing', $style);
								}
							}
							// reset post data
							wp_reset_postdata();
						}
						while ($listings->have_posts()) {
							$listings->the_post();

							$template_loader->set_template_data($style_data)->get_template_part('content-listing', $style);
						}
			?>
			<div class="clearfix"></div>
			</div>
		<?php
						//$template_loader->set_template_data( $style_data )->get_template_part( 'listings-end' ); 
					} else {
		?>
			<div class="loader-ajax-container">
				<div class="loader-ajax"></div>
			</div>
			<?php
						$template_loader->get_template_part('archive/no-found');
			?><div class="clearfix"></div>
			<?php
					}

					$result['html'] = ob_get_clean();
					$result['pagination'] = listeo_core_ajax_pagination($listings->max_num_pages, absint($_REQUEST['page']));

					wp_send_json($result);
				}

				public function ajax_get_features_from_category()
				{

					$categories  = (isset($_REQUEST['cat_ids'])) ? $_REQUEST['cat_ids'] : '';

					$panel  =  (isset($_REQUEST['panel'])) ? $_REQUEST['panel'] : '';
					$success = true;
					$clean_data = array();
					ob_start();
					$clean_data[] = array(
						'text' => __('Any feature', 'listeo_core'),
						'id' =>  '0',
					);

					if ($categories) {
						$features = array();

						foreach ($categories as $category) {
							if (is_numeric($category)) {
								$cat_object = get_term_by('id', $category, 'listing_category');
							} else {
								$cat_object = get_term_by('slug', $category, 'listing_category');
							}
							if ($cat_object) {
								$features_temp = get_term_meta($cat_object->term_id, 'listeo_taxonomy_multicheck', true);
								if ($features_temp) {
									$features = array_merge($features, $features_temp);
								}
								$features = array_unique($features);
							}
						}


						if ($features) {
							if ($panel != 'false') { ?>
					<div class="panel-checkboxes-container">
						<?php
								$groups = array_chunk($features, 4, true);

								foreach ($groups as $group) { ?>

							<?php foreach ($group as $feature) {
										$feature_obj = get_term_by('slug', $feature, 'listing_feature');
										if (!$feature_obj) {
											continue;
										}
										$clean_data[] = array(
											'text' => $feature_obj->name,
											'id' =>  $feature,
										);

							?>
								<div class="panel-checkbox-wrap">
									<input form="listeo_core-search-form" id="<?php echo esc_html($feature) ?>" value="<?php echo esc_html($feature) ?>" type="checkbox" name="tax-listing_feature[<?php echo esc_html($feature); ?>]">
									<label for="<?php echo esc_html($feature) ?>"><?php echo $feature_obj->name; ?></label>
								</div>
							<?php } ?>


						<?php } ?>

					</div>
					<?php } else {

								foreach ($features as $feature) {
									$feature_obj = get_term_by('slug', $feature, 'listing_feature');
									if (!$feature_obj) {
										continue;
									}
									$clean_data[] = array(
										'text' => $feature_obj->name,
										'id' =>  $feature,
									);
					?>
						<input form="listeo_core-search-form" id="<?php echo esc_html($feature) ?>" value="<?php echo esc_html($feature) ?>" type="checkbox" name="tax-listing_feature[<?php echo esc_html($feature); ?>]">
						<label for="<?php echo esc_html($feature) ?>"><?php echo $feature_obj->name; ?></label>
					<?php }
							}
						} else {
							if ($cat_object && isset($cat_object->name)) {
								$success = false; ?>
					<div class="notification notice <?php if ($panel) {
														echo "col-md-12";
													} ?>">
						<p>
							<?php printf(__('Category "%s" doesn\'t have any additional filters', 'listeo_core'), $cat_object->name)  ?>

						</p>
					</div>
				<?php } else {
								$success = false; ?>
					<div class="notification warning">
						<p><?php esc_html_e('Please choose category to display filters', 'listeo_core') ?></p>
					</div>
			<?php }
						}
					} else {
						$success = false; ?>
			<div class="notification warning">
				<p><?php esc_html_e('Please choose category to display filters', 'listeo_core') ?></p>
			</div>
			<?php }

					$result['output'] = ob_get_clean();
					$result['data'] = $clean_data;
					$result['success'] = $success;
					wp_send_json($result);
				}

				public function ajax_get_features_ids_from_category()
				{

					$categories  = isset($_REQUEST['cat_ids']) ? $_REQUEST['cat_ids'] : false;
					$panel  =  $_REQUEST['panel'];
					$selected  =  isset($_REQUEST['selected']) ? $_REQUEST['selected'] : false;
					$listing_id  =  isset($_REQUEST['listing_id']) ? $_REQUEST['listing_id'] : false;
					$success = true;
					if (!$selected) {
						if ($listing_id) {
							$selected_check = wp_get_object_terms($listing_id, 'listing_feature', array('fields' => 'ids'));
							if (! empty($selected_check)) {
								if (! is_wp_error($selected_check)) {
									$selected = $selected_check;
								}
							}
						}
					};
					ob_start();

					if ($categories) {

						$features = array();
						foreach ($categories as $category) {
							if (is_numeric($category)) {
								$cat_object = get_term_by('id', $category, 'listing_category');
							} else {
								$cat_object = get_term_by('slug', $category, 'listing_category');
							}

							if ($cat_object) {
								$features_temp = get_term_meta($cat_object->term_id, 'listeo_taxonomy_multicheck', true);
								if ($features_temp) {
									foreach ($features_temp as $key => $value) {
										$features[] = $value;
									}
								}

								// if($features_temp) {
								// 	$features = $features + $features_temp;
								// }
							}
						}

						$features = array_unique($features);

						if ($features) {
							if ($panel != 'false') { ?>
					<div class="panel-checkboxes-container">
						<?php
								$groups = array_chunk($features, 4, true);

								foreach ($groups as $group) { ?>

							<?php foreach ($group as $feature) {
										$feature_obj = get_term_by('slug', $feature, 'listing_feature');
										if (!$feature_obj) {
											continue;
										}

							?>
								<div class="panel-checkbox-wrap">
									<input form="listeo_core-search-form" value="<?php echo esc_html($feature_obj->term_id) ?>" type="checkbox" id="in-listing_feature-<?php echo esc_html($feature_obj->term_id) ?>" name="tax_input[listing_feature][]">
									<label for="in-listing_feature-<?php echo esc_html($feature_obj->term_id) ?>"><?php echo $feature_obj->name; ?></label>
								</div>
							<?php } ?>


						<?php } ?>

					</div>
					<?php } else {


								foreach ($features as $feature) {
									$feature_obj = get_term_by('slug', $feature, 'listing_feature');
									if (!$feature_obj) {
										continue;
									}
					?>
						<input <?php if ($selected) checked(in_array($feature_obj->term_id, $selected)); ?>value="<?php echo esc_html($feature_obj->term_id) ?>" type="checkbox" id="in-listing_feature-<?php echo esc_html($feature_obj->term_id) ?>" name="tax_input[listing_feature][]">
						<label id="label-in-listing_feature-<?php echo esc_html($feature_obj->term_id) ?>" for="in-listing_feature-<?php echo esc_html($feature_obj->term_id) ?>"><?php echo $feature_obj->name; ?></label>
					<?php }
							}
						} else {
							if ($cat_object) {


								if ($cat_object->name) {
									$success = false; ?>
						<div class="notification notice <?php if ($panel) {
															echo "col-md-12";
														} ?>">
							<p>
								<?php printf(__('Category "%s" doesn\'t have any additional filters', 'listeo_core'), $cat_object->name)  ?>

							</p>
						</div>
					<?php
								}
							} else {
								$success = false; ?>
					<div class="notification warning">
						<p><?php esc_html_e('Please choose category to display filters', 'listeo_core') ?></p>
					</div>
			<?php }
						}
					} else {
						$success = false; ?>
			<div class="notification warning">
				<p><?php esc_html_e('Please choose category to display filters', 'listeo_core') ?></p>
			</div>
		<?php }

					$result['output'] = ob_get_clean();
					$result['success'] = $success;
					wp_send_json($result);
				}

				public function ajax_get_listing_types_from_categories()
				{
					$categories  = isset($_REQUEST['cat_ids']) ? $_REQUEST['cat_ids'] : false;

					$success = true;
					$types = array();

					if ($categories) {


						foreach ($categories as $category) {
							if (is_numeric($category)) {
								$cat_object = get_term_by('id', $category, 'listing_category');
							} else {
								$cat_object = get_term_by('slug', $category, 'listing_category');
							}

							if ($cat_object) {
								$types_temp = get_term_meta($cat_object->term_id, 'listeo_taxonomy_type', true);
								if ($types_temp) {
									foreach ($types_temp as $key => $value) {
										$types[] = $value;
									}
								}
							}
						}
					}
					$result['output'] = $types;
					$result['success'] = $success;
					wp_send_json($result);
				}

				//sidebar
				public static function get_search_fields()
				{


					$currency_abbr = get_option('listeo_currency');

					$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
					$search_fields = array(

						'keyword_search' => array(
							'placeholder'	=> __('What are you looking for?', 'listeo_core'),
							'key'			=> 'keyword_search',
							'class'			=> 'col-md-12',
							'name'			=> 'keyword_search',
							'priority'		=> 1,
							'place'			=> 'main',
							'type' 			=> 'text',
						),
						'category' => array(
							'placeholder'	=> __('All Categories', 'listeo_core'),
							'key'			=> '_category',
							'class'			=> 'col-md-12 ',
							'name'			=> 'tax-listing_category',
							'priority'		=> 2,
							'place'			=> 'main',
							'type' 			=> 'drilldown-taxonomy',
							'taxonomy' 		=> 'listing_category',
						),
						'date_range' => array(
							'placeholder'	=> __('Check-In - Check-Out', 'listeo_core'),
							'key'			=> '_date_range',
							'name'			=> 'date_range',
							'type' 			=> 'date-range',
							'place'			=> 'main',
							'class'			=> 'col-md-12',
							'priority'		=> 3,
						),

						'location_search' => array(
							'placeholder'	=> __('Location', 'listeo_core'),
							'key'			=> 'location_search',
							'class'			=> 'col-md-12',
							'css_class'		=> 'input-with-icon location',
							'name'			=> 'location_search',
							'priority'		=> 4,
							'place'			=> 'main',
							'type' 			=> 'location',
						),


						'radius' => array(
							'placeholder' 	=> __('Radius around selected destination', 'listeo_core'),
							'key'			=> 'search_radius',
							'class'			=> 'col-md-12',
							'css_class'		=> 'margin-top-30',
							'name'			=> 'search_radius',
							'priority'		=> 5,
							'place'			=> 'main',
							'type' 			=> 'radius',
							'max' 			=> '100',
							'min' 			=> '1',
						),




						'rating' => array(
							'placeholder' 	=> __('Rating', 'listeo_core'),
							'key'			=> '_rating',
							'class'			=> 'col-md-12',
							'name'			=> '_rating',
							'priority'		=> 6,
							'place'			=> 'main',
							'type' 			=> 'rating',


						),
						'price_range' => array(
							'placeholder' 	=> __('Price Filter', 'realteo'),
							'key'			=> '_price',
							'class'			=> 'col-md-12',
							'css_class'		=> '',
							'name'			=> '_price',
							'priority'		=> 7,
							'place'			=> 'main',
							'type' 			=> 'slider',
							'max' 			=> 'auto',
							'min' 			=> 'auto',
							'unit' 			=> $currency_symbol,

						),


						'features' => array(
							'placeholder' 	=> __('Features', 'listeo_core'),
							'key'			=> '_features',
							'class'			=> 'col-md-12',
							'name'			=> 'tax-listing_feature',
							'priority'		=> 8,
							'options'		=> array(),
							'place'			=> 'adv',
							'type' 			=> 'multi-checkbox',
							'taxonomy' 		=> 'listing_feature',
							'dynamic' 		=> (get_option('listeo_dynamic_features') == "on") ? "yes" : "no",
						),
					);

					$fields = listeo_core_sort_by_priority(apply_filters('listeo_core_search_fields', $search_fields));

					return $fields;
				}

				public static function get_search_fields_half()
				{

					$search_fields = array(

						'keyword_search' => array(
							'placeholder'	=> __('What are you looking for?', 'listeo_core'),
							'key'			=> 'keyword_search',
							'class'			=> 'col-fs-6',
							'name'			=> 'keyword_search',
							'priority'		=> 1,
							'place'			=> 'main',
							'type' 			=> 'text',
						),
						'location_search' => array(
							'placeholder'	=> __('Location', 'listeo_core'),
							'key'			=> 'location_search',
							'class'			=> 'col-fs-6',
							'css_class'		=> 'input-with-icon location',
							'name'			=> 'location_search',
							'priority'		=> 1,
							'place'			=> 'main',
							'type' 			=> 'location',
						),
						'category' => array(
							'placeholder'	=> __('Categories', 'listeo_core'),
							'key'			=> '_category',
							'name'			=> 'tax-listing_category',
							'type' 			=> 'multi-checkbox-row',
							'place'			=> 'panel',
							'taxonomy' 		=> 'listing_category',
						),
						'features' => array(
							'placeholder'	=> __('More Filters', 'listeo_core'),
							'key'			=> '_category',
							'name'			=> 'tax-listing_feature',
							'type' 			=> 'multi-checkbox-row',
							'place'			=> 'panel',
							'taxonomy' 		=> 'listing_feature',
							'dynamic' 		=> (get_option('listeo_dynamic_features') == "on") ? "yes" : "no",
						),
						'radius' => array(
							'placeholder'	=> __('Distance Radius', 'listeo_core'),
							'key'			=> 'search_radius',
							'name'			=> 'search_radius',
							'type' 			=> 'radius',
							'place'			=> 'panel',
							'max' 			=> '100',
							'min' 			=> '1',
						),
						'price' => array(
							'placeholder'	=> __('Price Filter', 'listeo_core'),
							'key'			=> '',
							'name'			=> '_price',
							'type' 			=> 'slider',
							'place'			=> 'panel',
							'max' 			=> 'auto',
							'min' 			=> 'auto',

						),
						'rating' => array(
							'placeholder' 	=> __('Rating', 'listeo_core'),
							'key'			=> '_rating',
							'name'			=> '_rating',
							'place'			=> 'panel',
							'type' 			=> 'rating',
						),

						'submit' => array(
							'class'			=> 'button fs-map-btn right',
							'open_row'		=> false,
							'close_row'		=> false,
							'place'			=> 'panel',
							'name' 			=> 'submit',
							'type' 			=> 'submit',
							'placeholder'	=> __('Search', 'listeo_core'),
						),
					);
					if (is_post_type_archive('listing')) {
						$top_buttons_conf = get_option('listeo_listings_top_buttons_conf');
						if($top_buttons_conf) {
							
							if (get_option('pp_listings_top_layout') != 'half') {
								if (!in_array('filters', $top_buttons_conf)) {
									unset($search_fields['features']);
									unset($search_fields['category']);
								}
								if (!in_array('radius', $top_buttons_conf)) {
									unset($search_fields['radius']);
								}
							}
						}
						// 	'filters' (length=7)
						// 2 => string 'radius'

					}

					return apply_filters('listeo_core_search_fields_half', $search_fields);
				}

				public static function get_search_fields_home()
				{

					$search_fields = array(
						// 'order' => array(
						// 	'placeholder'	=> __( 'Hidden order', 'listeo_core' ),
						// 	'key'			=> 'listeo_core_order',
						// 	'name'			=> 'listeo_core_order',
						//    	'place'			=> 'main',
						// 	'type' 			=> 'hidden',
						// ),	
						// 'search_radius' => array(
						// 	'placeholder'	=> __( 'Radius hidde', 'listeo_core' ),
						// 	'key'			=> 'search_radius',
						// 	'name'			=> 'search_radius',
						//    	'place'			=> 'main',
						// 	'type' 			=> 'hidden',
						// ),	
						'keyword_search' => array(
							'placeholder'	=> __('What are you looking for?', 'listeo_core'),
							'key'			=> 'keyword_search',
							'name'			=> 'keyword_search',
							'priority'		=> 1,
							'place'			=> 'main',
							'type' 			=> 'text',
						),
						'location_search' => array(
							'placeholder'	=> __('Location', 'listeo_core'),
							'key'			=> 'location_search',
							'name'			=> 'location_search',
							'priority'		=> 2,
							'place'			=> 'main',
							'type' 			=> 'location',
						),
						'category' => array(
							'placeholder'	=> __('All Categories', 'listeo_core'),
							'key'			=> '_category',
							'name'			=> 'tax-listing_category',
							'type' 			=> 'drilldown-taxonomy',
							'place'			=> 'main',
							'taxonomy' 		=> 'listing_category',

						),


					);

					return apply_filters('listeo_core_search_fields_home', $search_fields);
				}
				public static function get_search_fields_header()
				{

					$search_fields = array(

						'keyword_search' => array(
							'placeholder'	=> __('What are you looking for?', 'listeo_core'),
							'key'			=> 'keyword_search',
							'name'			=> 'keyword_search',
							'priority'		=> 1,
							'place'			=> 'main',
							'type' 			=> 'text',
						),
						'location_search' => array(
							'placeholder'	=> __('Location', 'listeo_core'),
							'key'			=> 'location_search',
							'name'			=> 'location_search',
							'priority'		=> 2,
							'place'			=> 'main',
							'type' 			=> 'location',
						),
						'category' => array(
							'placeholder'	=> __('All Categories', 'listeo_core'),
							'key'			=> '_category',
							'name'			=> 'tax-listing_category',
							'type' 			=> 'select-taxonomy',
							'place'			=> 'main',
							'taxonomy' 		=> 'listing_category',

						),


					);

					return apply_filters('listeo_core_search_fields_header', $search_fields);
				}

				public static function get_search_fields_home_box()
				{
					$currency_abbr = get_option('listeo_currency');

					$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);

					$search_fields = array(
						'location_search' => array(
							'placeholder'	=> __('Location', 'listeo_core'),
							'key'			=> 'location_search',
							'name'			=> 'location_search',
							'priority'		=> 2,
							'place'			=> 'main',
							'type' 			=> 'location',
						),
						'date_range' => array(
							'placeholder'	=> __('Check-In - Check-Out', 'listeo_core'),
							'key'			=> '_date_range',
							'name'			=> 'date_range',
							'type' 			=> 'date-range',
							'place'			=> 'main',
						),
						'price_range' => array(
							'placeholder' 	=> __('Price Filter', 'realteo'),
							'key'			=> '_price',
							'css_class'		=> '',
							'name'			=> '_price',
							'priority'		=> 4,
							'place'			=> 'main',
							'type' 			=> 'slider',
							'max' 			=> 'auto',
							'min' 			=> 'auto',
							'unit' 			=> $currency_symbol,
							'state'			=> 'on'
						),



					);

					return apply_filters('listeo_core_search_fields_homebox', $search_fields);
				}


				public function output_search_form($atts = array())
				{
					extract($atts = shortcode_atts(apply_filters('listeo_core_output_defaults', array(
						'source'			=> 'sidebar', // home/sidebar/split
						'wrap_with_form'	=> 'yes',
						'custom_class' 		=> '',
						'action'			=> '',
						'more_trigger'		=> 'yes',
						'more_text_open'	=> __('Additional Features', 'listeo_core'),
						'more_text_close'	=> __('Additional Features', 'listeo_core'),
						'more_custom_class' => ' margin-bottom-10 margin-top-30',
						'more_trigger_style' => 'relative',
						'ajax_browsing'		=> get_option('listeo_ajax_browsing'),
						'dynamic_filters' 	=> (get_option('listeo_dynamic_features') == "on") ? "on" : "off",
						'dynamic_taxonomies' => (get_option('listeo_dynamic_taxonomies') == "on") ? "on" : "off",

					)), $atts));

					switch ($source) {

						case 'search_on_home_page':
						case 'home':
							$source = 'search_on_home_page';
							$form_type = 'fullwidth';
							$search_fields = $this->get_search_fields_home();
							//fix for panel slider for search
							if (isset($search_fields['_price'])) {
								$search_fields['_price']['place'] = 'panel';
							}

							if (isset($search_fields['search_radius'])) {
								$search_fields['search_radius']['place'] = 'panel';
							}
							break;

						case 'sidebar_search':
						case 'sidebar':
							$source = 'sidebar_search';
							$search_fields = $this->get_search_fields();
							$form_type = 'sidebar';
							break;

						case 'search_on_half_map':
						case 'half':
							$source = 'search_on_half_map';
							$search_fields = $this->get_search_fields_half();
							$form_type = 'split';
							break;

						case 'search_on_homebox_page':
						case 'homebox':
							$source = 'search_on_homebox_page';
							$search_fields = $this->get_search_fields_home_box();
							$form_type = 'boxed';

							break;
						case 'search_in_header':
						case 'header':
							$source = 'search_in_header';
							$search_fields = $this->get_search_fields_header();
							$form_type = 'fullwidth';
							if (isset($search_fields['_price'])) {
								$search_fields['_price']['place'] = 'panel';
							}

							if (isset($search_fields['search_radius'])) {
								$search_fields['search_radius']['place'] = 'panel';
							}

							break;
						default:
							$options = get_option("listeo_{$source}_form_fields");
							$search_fields = $options ? $options : $this->get_search_fields_home();
							break;
					}

					$forms = get_option('listeo_search_forms', array());

					$default_forms = listeo_get_default_search_forms();

					if (array_key_exists($source, $default_forms)) {
						$default_form = true;
					} else {
						$form_type = $forms[$source]['type'];
					}

					if (isset($search_fields['tax-listing_feature'])) {
						$search_fields['tax-listing_feature']['dynamic'] = (get_option('listeo_dynamic_features') == "on") ? "yes" : "no";
					}
					if (isset($search_fields['features'])) {
						$search_fields['features']['dynamic'] = (get_option('listeo_dynamic_features') == "on") ? "yes" : "no";
					}

					$ajax = ($ajax_browsing == 'on') ? 'ajax-search' : get_option('listeo_ajax_browsing');
					if ($ajax_browsing == 'on') {
						if (isset($search_fields['submit'])) {
							unset($search_fields['submit']);
						}
					}

					if (!get_option('listeo_maps_api_server') && !get_option('listeo_geoapify_maps_api_server')) {

						unset($search_fields['radius']);
						unset($search_fields['search_radius']);
					}

					if ($form_type == 'fullwidth') {
						if (isset($search_fields['price_range'])) {
							$search_fields['price_range']['place'] = 'panel';
						}
						if (isset($search_fields['_price'])) {
							$search_fields['_price']['place'] = 'panel';
						}

						if (isset($search_fields['search_radius'])) {
							$search_fields['search_radius']['place'] = 'panel';
						}

						if (isset($search_fields['_rating'])) {
							//var_dump($search_fields['rating']);
							$search_fields['_rating']['place'] = 'panel';
						}
						foreach ($search_fields as $key => $value) {
							if (in_array($value['type'], array('multi-checkbox', 'multi-checkbox-row'))) {
								$search_fields[$key]['place'] = 'panel';
							}
							//place = panel
						}
					}

					//var_dump($search_fields);
					$template_loader = new Listeo_Core_Template_Loader;

					//$action = get_post_type_archive_link( 'listing' );

					if (is_author()) {
						$author = get_queried_object();
						$author_id = $author->ID;
						$action = get_author_posts_url($author_id);
					}
					// 

					//change source to type
					ob_start();
					if ($wrap_with_form == 'yes') { ?>
			<form action="<?php echo $action; ?>" id="listeo_core-search-form" class="listeo-form-<?php echo esc_attr($source);
																									if ($dynamic_filters == 'on') {
																										echo esc_attr(' dynamic');
																									}  ?> <?php if ($dynamic_taxonomies == 'on') {
																																																	echo esc_attr('dynamic-taxonomies');
																																																}  ?>  <?php echo esc_attr($custom_class) ?> <?php echo esc_attr($ajax) ?>" method="GET">
			<?php }
					if (in_array($form_type, array('fullwidth'))) { ?>
				<div class="main-search-input">
					<?php }

					$more_trigger = false;
					$panel_trigger = false;
					foreach ($search_fields as $key => $value) {
						if ((isset($value['place']) && $value['place'] == 'adv')) {
							$more_trigger = 'yes';
						}
						if ((isset($value['place']) && $value['place'] == 'panel')) {
							$panel_trigger = 'yes';
						}
					}
					//count main fields
					$count = 0;
					foreach ($search_fields as $key => $value) {
						if (isset($value['place']) && $value['place'] == 'main') {
							$count++;
						}
					}
					$temp_count = 0;
					foreach ($search_fields as $key => $value) {

						if (in_array($form_type, array('fullwidth', 'boxed')) && $value['type'] != 'hidden') { ?>
						<div class="main-search-input-item <?php
															switch ($value['type']) {
																case 'slider':
																	echo 'slider_type';
																	break;
																case 'rating':
																	echo 'listeo-rating-filter';
																	break;

																default:
																	echo esc_attr($value['type']);
																	break;
															}
															?>">
							<?php }

						if (isset($value['place']) && $value['place'] == 'main') {

							//displays search form

							if ($form_type == 'split') {

								if ($temp_count == 0) {
									echo '<div class="row with-forms split-top-inputs">';
								}
								$temp_count++;
								$template_loader->set_template_data($value)->get_template_part('search-form/' . $value['type']);
								if ($temp_count == $count) {
									echo '</div>';
								}
							} else {

								if ($form_type == 'sidebar') {
									echo '<div class="row with-forms" id="listeo-search-form_' . $value['name'] . '">';
								}
								$template_loader->set_template_data($value)->get_template_part('search-form/' . $value['type']);
								if ($form_type == 'sidebar') {
									echo '</div>';
								}
							}


							if ($value['type'] == 'radius') { ?>
								<!-- <div class="row with-forms">
							<div class="col-md-12">
								<span class="panel-disable" data-disable="<?php echo esc_attr_e('Disable Radius', 'listeo_core'); ?>" data-enable="<?php echo esc_attr_e('Enable Radius', 'listeo_core'); ?>"><?php esc_html_e('Disable Radius', 'listeo_core'); ?></span>
							</div>
						</div> -->

							<?php }
						}

						if (in_array($form_type, array('fullwidth', 'boxed'))) {
							//fix for price on home search

							if (isset($value['place']) && $value['place'] == 'panel') {
							?>
								<?php
								//if value type is drilldown-taxonomy, don't show it in panel

								if ($value['type'] == 'drilldown_taxonomy') { ?>


									$template_loader->set_template_data( $value )->get_template_part( 'search-form/'.$value['type']);


									<?php } else {

									if (isset($value['type']) && $value['type'] != 'submit') { ?>
										<!-- Panel Dropdown -->
										<div class="panel-dropdown <?php if ($value['type'] == 'multi-checkbox-row') {
																		echo "wide";
																	}
																	if ($value['type'] == 'radius') {
																		echo 'radius-dropdown';
																	} ?> " id="<?php echo esc_attr($value['name']); ?>-panel">
											<a href="#"><?php echo esc_html($value['placeholder']); ?></a>
											<div class="panel-dropdown-content <?php if ($value['type'] == 'multi-checkbox-row') {
																					echo "checkboxes";
																				} ?> <?php if (isset($value['dynamic']) && $value['dynamic'] == 'yes') {
																																								echo esc_attr('dynamic');
																																							} ?>">
											<?php }

										$template_loader->set_template_data($value)->get_template_part('search-form/' . $value['type']);

										if (isset($value['type']) && $value['type'] != 'submit') { ?>
												<!-- Panel Dropdown -->
												<div class="panel-buttons">
													<?php if ($value['type'] == 'radius') { ?>
														<span class="panel-disable" data-disable="<?php echo esc_attr_e('Disable', 'listeo_core'); ?>" data-enable="<?php echo esc_attr_e('Enable', 'listeo_core'); ?>"><?php esc_html_e('Disable', 'listeo_core'); ?></span>
													<?php } else { ?>
														<span class="panel-cancel"><?php esc_html_e('Close', 'listeo_core'); ?></span>
													<?php } ?>

													<button class="panel-apply"><?php esc_html_e('Apply', 'listeo_core'); ?></button>
												</div>
											</div>
										</div>
							<?php }
									}
								}
							}
							if (in_array($form_type, array('fullwidth', 'boxed'))  && $value['type'] != 'hidden') { ?>
						</div>
				<?php }
						}
				?>

				<?php if ($more_trigger == 'yes') : ?>
					<!-- More Search Options -->
					<a href="#" class="more-search-options-trigger <?php echo esc_attr($more_custom_class) ?>" data-open-title="<?php echo esc_attr($more_text_open) ?>" data-close-title="<?php echo esc_attr($more_text_close) ?>"></a>
					<?php if ($more_trigger_style == "over") : ?>
						<div class="more-search-options ">
							<div class="more-search-options-container">
							<?php else: ?>
								<div class="more-search-options relative">
								<?php endif; ?>

								<?php foreach ($search_fields as $key => $value) {
									if ($value['place'] == 'adv') {

										$template_loader->set_template_data($value)->get_template_part('search-form/' . $value['type']);
									}
								} ?>
								<?php if ($more_trigger_style == "over") : ?>
								</div>
							<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ($form_type != 'fullwidth' && $panel_trigger == 'yes') { ?>
							<div class="row">
								<?php echo ($form_type == 'split') ? '<div class="col-fs-12 panel-wrapper">' : '<div class="col-md-12  panel-wrapper">'; {  ?>
									<?php
									foreach ($search_fields as $key => $value) {
										if ($form_type != 'fullwidth' && isset($value['place']) && $value['place'] == 'panel') {
									?>

											<?php
											//if value type is drilldown-taxonomy, don't show it in panel
											if ($value['type'] == 'drilldown-taxonomy') { ?>
												<div class="drilldown-menu-panel">
													<?php $template_loader->set_template_data($value)->get_template_part('search-form/' . $value['type']); ?>
												</div>
												<?php } else {
												if (isset($value['type']) && !in_array($value['type'], array('submit', 'sortby'))) {

												?>
													<!-- Panel Dropdown -->
													<div class="panel-dropdown <?php if ($value['type'] == 'multi-checkbox-row') {
																					echo "wide";
																				}
																				if ($value['type'] == 'radius') {
																					echo 'radius-dropdown';
																				} ?> " id="<?php echo esc_attr($value['name']); ?>-panel">
														<a href="#"><?php echo esc_html($value['placeholder']); ?></a>
														<div class="panel-dropdown-content <?php if ($value['type'] == 'multi-checkbox-row') {
																								echo "checkboxes";
																							} ?> <?php if (isset($value['dynamic']) && $value['dynamic'] == 'yes') {
																																											echo esc_attr('dynamic');
																																										} ?>">
														<?php }

													$template_loader->set_template_data($value)->get_template_part('search-form/' . $value['type']);
												}
												if (isset($value['type']) && !in_array($value['type'], array('submit', 'sortby', 'drilldown-taxonomy'))) { ?>
														<!-- Panel Dropdown -->
														<div class="panel-buttons">
															<?php if ($value['type'] == 'radius') { ?>
																<span class="panel-disable" data-disable="<?php echo esc_attr_e('Disable', 'listeo_core'); ?>" data-enable="<?php echo esc_attr_e('Enable', 'listeo_core'); ?>"><?php esc_html_e('Disable', 'listeo_core'); ?></span>
															<?php } else { ?>
																<span class="panel-cancel"><?php esc_html_e('Close', 'listeo_core'); ?></span>
															<?php } ?>

															<button class="panel-apply"><?php esc_html_e('Apply', 'listeo_core'); ?></button>
														</div>
														</div>
													</div>
										<?php }
											}
										} ?>

							</div>
						</div>
				<?php }
							} ?>
				<input type="hidden" name="action" value="listeo_get_listings" />
				<!-- More Search Options / End -->
				<?php if ($form_type == 'sidebar' && $ajax_browsing != 'on') {	?>
					<button class="button fullwidth margin-top-30"><?php esc_html_e('Search', 'listeo_core') ?></button>
				<?php } ?>

				<?php if (in_array($form_type, array('fullwidth'))) { ?>
					<button class="button"><?php esc_html_e('Search', 'listeo_core') ?></button>
				</div>
			<?php } ?>
			<?php if (in_array($form_type, array('boxed'))) { ?>
				<button class="button"><?php esc_html_e('Search', 'listeo_core') ?></button>

			<?php } ?>
			<?php if ($wrap_with_form == 'yes') { ?>
			</form>
<?php }
					//if ajax

					$output = ob_get_clean();
					echo $output;
				}



				public static function get_min_meta_value($meta_key = '', $type = '')
				{
					$transient_name = 'min_meta_value_' . $meta_key . '_' . $type;
					// Check if the transient exists and is not expired
					$cached_value = get_transient($transient_name);
					if ($cached_value !== false) {
						return $cached_value;
					}
					global $wpdb;
					$result = false;
					if (!empty($type)) {
						$type_query = 'AND ( m1.meta_key = "_listing_type" AND m1.meta_value = "' . $type . '")';
					} else {
						$type_query = false;
					}
					if ($meta_key):

						$result = $wpdb->get_var(
							$wpdb->prepare("
		            SELECT min(m2.meta_value + 0)
		            FROM $wpdb->posts AS p
		            INNER JOIN $wpdb->postmeta AS m1 ON ( p.ID = m1.post_id )
					INNER JOIN $wpdb->postmeta AS m2  ON ( p.ID = m2.post_id )
					WHERE
					p.post_type = 'listing'
					AND p.post_status = 'publish'
					$type_query
					AND ( m2.meta_key IN ( %s, %s, %s)  ) AND m2.meta_value != ''
		        ", $meta_key . '_min', $meta_key . '_max', '_classifieds_price')
						);

					endif;
					set_transient($transient_name, $result, 86400);

					return $result;
				}

				public static function get_max_meta_value($meta_key = '', $type = '')
				{
					$transient_name = 'max_meta_value_' . $meta_key . '_' . $type;
					// Check if the transient exists and is not expired
					$cached_value = get_transient($transient_name);
					if ($cached_value !== false) {
						return $cached_value;
					}
					global $wpdb;
					$result = false;
					if (!empty($type)) {
						$type_query = 'AND ( m1.meta_key = "_listing_type" AND m1.meta_value = "' . $type . '")';
					} else {
						$type_query = false;
					}
					if ($meta_key):

						$result = $wpdb->get_var(
							$wpdb->prepare("
		            SELECT max(m2.meta_value + 0)
		            FROM $wpdb->posts AS p
		            INNER JOIN $wpdb->postmeta AS m1 ON ( p.ID = m1.post_id )
					INNER JOIN $wpdb->postmeta AS m2  ON ( p.ID = m2.post_id )
					WHERE
					p.post_type = 'listing'
					AND p.post_status = 'publish'
					$type_query
					AND ( m2.meta_key IN ( %s, %s, %s)  ) AND m2.meta_value != ''
		        ", $meta_key . '_min', $meta_key . '_max', '_classifieds_price')
						);

					endif;
					set_transient($transient_name, $result, 86400);

					return $result;
				}
			}
