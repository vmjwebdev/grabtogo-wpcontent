<?php
$template_loader = new Listeo_Core_Template_Loader;
?>
<?php $content_layout = get_option('pp_listings_layout', 'list');

$sidebar_status = get_option('pp_listings_split-sidebar-status', 'hide');
?>
<!-- Page Content
================================================== -->
<div class="full-page-container full-page-jobs">

	<div class="full-page-sidebar hidden-sidebar <?php if ($sidebar_status == 'show') { ?>enabled-sidebar<?php } ?>">
		<div class="full-page-sidebar-inner">
			<div class="sidebar-container">
				<div class="filter-button-container">
					<button class="enable-filters-button">
						<i class="enable-filters-button-icon"></i>
						<span class="show-text"><?php esc_html_e('Show Filters', 'workscout') ?></span>
						<span class="hide-text"><?php esc_html_e('Hide Filters', 'workscout') ?></span>
					</button>
				</div>
				<?php $template_loader->get_template_part('sidebar-listeo'); ?>
			</div>
		</div>
	</div>
	<!-- Full Page Sidebar / End -->


	<!-- Full Page Content -->
	<div class="full-page-content-container">

		<div class="sticky-filter-button">
			<div class="filter-button-container">
				<button class="enable-filters-button <?php if ($sidebar_status == 'show') { ?>active<?php } ?>">
					<i class="enable-filters-button-icon"></i>
					<span class="show-text"><?php esc_html_e('Show Filters', 'workscout') ?></span>
					<span class="hide-text"><?php esc_html_e('Hide Filters', 'workscout') ?></span>
				</button>
			</div>

		</div>
		<div class="full-page-content-inner">

			<!-- Filters Container -->
			<div class="filters-container">

				<!-- Page Title
				<h3 class="filters-headline">
					<?php $count_jobs = wp_count_posts('listing', 'readable');
					printf(_n('We have <strong class="count_jobs">%s</strong> <strong class="job_text">listing</strong> that could be the right fit!', 'We have <strong class="count_jobs">%s</strong> <strong class="job_text">listings</strong> that could be the right fit!', $count_jobs->publish, 'workscout'), $count_jobs->publish); ?>
				</h3> -->


				<!-- Enable Filters Button -->
				<div class="filter-button-container">
					<button class="enable-filters-button <?php if ($sidebar_status == 'show') { ?>active<?php } ?>">
						<i class="enable-filters-button-icon"></i>
						<span class="show-text"><?php esc_html_e('Show Filters', 'workscout') ?></span>
						<span class="hide-text"><?php esc_html_e('Hide Filters', 'workscout') ?></span>
					</button>
					<div class="slider-container">
						<button class="nav-button prev hidden">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
							</svg>
						</button>

						<div class="category-slider" id="categorySlider">
							<!-- Category Items will be inserted here via JavaScript -->
						</div>

						<button class="nav-button next">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
							</svg>
						</button>
					</div>

					<?php wp_enqueue_script('listeo_core-categories-split-slider');
					wp_localize_script('listeo_core-categories-split-slider', 'sliderData', [
						'categories' => json_decode(listeo_get_slider_split_categories_json(), true),
					]); ?>
				</div>

			</div>
			<!-- Filters Container / End -->


			<!-- Full Page Job Listings Container -->
			<div class="full-page-job-listings-container">
				<div class="row fs-switcher" style="padding: 0px;">


					<?php $top_buttons = get_option('listeo_listings_top_buttons');

					if ($top_buttons == 'enable') {
						$top_buttons_conf = get_option('listeo_listings_top_buttons_conf');
						if (is_array($top_buttons_conf) && !empty($top_buttons_conf)) {

							if (($key = array_search('radius', $top_buttons_conf)) !== false) {
								unset($top_buttons_conf[$key]);
							}
							if (($key = array_search('filters', $top_buttons_conf)) !== false) {
								unset($top_buttons_conf[$key]);
							}
							$list_top_buttons = implode("|", $top_buttons_conf);
						} else {
							$list_top_buttons = '';
						}
					?>

						<?php do_action('listeo_before_archive', $content_layout, $list_top_buttons); ?>

					<?php
					} ?>

				</div>
				<?php

				switch ($content_layout) {
					case 'list':
					case 'grid':
						$container_class = $content_layout . '-layout';
						break;

					case 'compact':
						$container_class = $content_layout;
						break;

					default:
						$container_class = 'list-layout';
						break;
				}

				$data = '';
				if ($content_layout == 'grid') {
					// if ( $sidebar_side == 'full-width'){
					// 	$data .= 'data-grid_columns="3"';
					// } else {
					//	$data .= 'data-grid_columns="2"';
					//}

				}
				$category = get_query_var('tax-listing_category');
				//check if it's a category  archive page
				if (is_tax('listing_category')) {
					$term = get_queried_object();
					$category = $term->slug;
				}
				$listing_type = get_query_var('_listing_type');


				$region = get_query_var('tax-region');
				if (is_tax('region')) {
					$term = get_queried_object();
					$region = $term->slug;
				}
				$feature = get_query_var('tax-listing_feature');
				if (is_tax('listing_feature')) {
					$term = get_queried_object();
					$feature = $term->slug;
				}
				if (is_array($region)) {
					$region = $region[0];
				}
				if (is_array($category)) {
					$category = $category[0];
				}
				if (is_array($feature)) {
					$feature = $feature[0];
				}
				$data .= ' data-region="' . $region . '" ';
				$data .= ' data-_listing_type="' . $listing_type . '" ';
				$data .= ' data-category="' . $category . '" ';
				$data .= ' data-feature="' . $feature . '" ';
				$data .= ' data-service-category="' . get_query_var('tax-service_category') . '" ';
				$data .= ' data-rental-category="' . get_query_var('tax-rental_category') . '" ';
				$data .= ' data-event-category="' . get_query_var('tax-event_category') . '" ';
				$data .= ' data-classifieds-category="' . get_query_var('tax-classifieds_category') . '" ';
				$orderby_value = isset($_GET['listeo_core_order']) ? (string) $_GET['listeo_core_order']  : get_option('listeo_sort_by', 'date');
				?>
				<!-- Listings -->
				<div data-grid_columns="2" <?php echo $data; ?> data-orderby="<?php echo $orderby_value;  ?>" data-style="<?php echo esc_attr($content_layout) ?>" class="listings-container <?php echo esc_attr($container_class) ?>" id="listeo-listings-container">
					<div class="loader-ajax-container">
						<div class="loader-ajax"></div>
					</div>
					<?php




					$ad_filter = array(
						'listing_category' 	=> $category,
						'listing_feature'	=> $region,
						'region' 			=> $feature,
					);

					// get posts from ad
					$ads = listeo_get_ids_listings_for_ads('search', $ad_filter);

					// if no ads, don't show them
					if (!empty($ads)) {


						$ad_posts_count = count($ads);
						$ad_posts_index = 0;

						$ads_args = array(
							'post_type' => 'listing',
							'post_status' => 'publish',
							'posts_per_page' => 2,
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
								switch ($content_layout) {
									case 'list':
										$template_loader->set_template_data($ad_data)->get_template_part('content-listing');
										break;

									case 'grid':
										echo '<div class="col-lg-6 col-md-12"> ';
										$template_loader->set_template_data($ad_data)->get_template_part('content-listing-grid');
										echo '</div>';
										break;

									case 'compact':
										echo '<div class="col-lg-6 col-md-12"> ';
										$template_loader->set_template_data($ad_data)->get_template_part('content-listing-compact');
										echo '</div>';
										break;

									default:
										$template_loader->set_template_data($ad_data)->get_template_part('content-listing');
										break;
								}
							}
						}
						// reset post data
						wp_reset_postdata();
					}
					/* Start the Loop */

					if (have_posts()) :


						/* Start the Loop */
						while (have_posts()) : the_post();

							switch ($content_layout) {
								case 'list':
									$template_loader->get_template_part('content-listing');
									break;

								case 'grid':
									echo '<div class="col-lg-6 col-md-12"> ';
									$template_loader->get_template_part('content-listing-grid');
									echo '</div>';
									break;

								case 'compact':
									echo '<div class="col-lg-6 col-md-12"> ';
									$template_loader->get_template_part('content-listing-compact');
									echo '</div>';
									break;

								default:
									$template_loader->get_template_part('content-listing');
									break;
							}

						endwhile;


					else :

						$template_loader->get_template_part('archive/no-found');

					endif; ?>

					<div class="clearfix"></div>
				</div>
				<?php $ajax_browsing = get_option('listeo_ajax_browsing'); ?>
				<div class="pagination-container margin-top-45 margin-bottom-60 row  <?php if (isset($ajax_browsing) && $ajax_browsing == 'on') {
																							echo esc_attr('ajax-search');
																						} ?>">
					<nav class="pagination col-md-12">
						<?php
						if ($ajax_browsing == 'on') {
							global $wp_query;
							$pages = $wp_query->max_num_pages;
							echo listeo_core_ajax_pagination($pages, 1);
						} else 
							if (function_exists('wp_pagenavi')) {
							wp_pagenavi(array(
								'next_text' => '<i class="fa fa-chevron-right"></i>',
								'prev_text' => '<i class="fa fa-chevron-left"></i>',
								'use_pagenavi_css' => false,
							));
						} else {
							the_posts_navigation();
						} ?>
					</nav>
				</div>
				<?php if (term_description()) { ?>
					<div class="row term-description" style="    padding: 15px 55px 25px;
 ">
						<?php echo term_description(); ?>
					</div>
				<?php } ?>
				<div class="copyrights margin-top-0"><?php $copyrights = get_option('pp_copyrights', '&copy; Theme by Purethemes.net. All Rights Reserved.');

														if (function_exists('icl_register_string')) {
															icl_register_string('Copyrights in footer', 'copyfooter', $copyrights);
															echo icl_t('Copyrights in footer', 'copyfooter', $copyrights);
														} else {
															echo wp_kses($copyrights, array('a' => array('href' => array(), 'title' => array()), 'br' => array(), 'em' => array(), 'strong' => array(),));
														} ?>

				</div>
			</div>
			<!-- Full Page Job Listings Container / End -->

		</div>
	</div>
	<!-- Full Page Content / End -->


	<!-- Full Page Map -->
	<div class="full-page-map-container  map-fixed">
		<!-- Preloader -->
		<!-- Map -->
		<div id="map-container" class="">
			<div id="map" class="split-map" data-map-zoom="<?php echo get_option('listeo_map_zoom_global', 9); ?>" data-map-scroll="true">
				<!-- map goes here -->
			</div>

		</div>
	</div>
	<!-- Full Page Map / End -->

</div>
<?php

get_footer('empty'); ?>