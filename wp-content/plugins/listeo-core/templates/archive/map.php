
<!-- Map
================================================== -->
<div id="map-container" class="fullwidth-home-map margin-bottom-20">

    <div id="map" data-map-zoom="<?php echo get_option('liteo_map_zoom_global',9); ?>">
        <!-- map goes here -->
    </div>

	<div class="main-search-inner">

		<div class="container">
			<div class="row">
				<div class="col-md-12">
					
					<?php echo do_shortcode('[listeo_search_form action='.get_post_type_archive_link( 'listing' ).' source="home" custom_class="main-search-form"]') ?>
				</div>
			</div>
		</div>

	</div>

    <!-- Scroll Enabling Button -->
	<a href="#" id="scrollEnabling" title="Enable or disable scrolling on map"><?php esc_html_e('Enable Scrolling','listeo_core'); ?></a>
	
</div>
