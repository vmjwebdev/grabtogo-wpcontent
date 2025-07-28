<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Listeo_Core {

	/**
	 * The single instance of Listeo_Core.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	public $post_types;
	public $meta_boxes;
	public $listing;
	public $reviews;
	public $submit;
	public $search;
	public $users;
	public $bookmarks;
	public $activites_log;
	public $messages;
	public $calendar;
	public $calendar_view;
	public $emails;
	public $commissions;
	public $payouts;
	public $ical;
	public $coupons;
	public $stripe;
	public $sitehealth;
	public $stats;
	public $chart;
	public $claims;
	public $ads;
	public $qr;
	public $reports;
	//public $claims;
		
	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.9.14' ) {
		$this->_version = $version;
		
		$this->_token = 'listeo_core';

		// Load plugin environment variables
		$this->file = $file;
		
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$this->script_suffix =  '.min';
		register_activation_hook( $this->file, array( $this, 'install' ) );


		define( 'LISTEO_CORE_ASSETS_DIR', trailingslashit( $this->dir ) . 'assets' );
		define( 'LISTEO_CORE_ASSETS_URL', esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) ) );

		// Add translation loading to init hook
		add_action('init', array($this, 'load_plugin_textdomain'), 0);
		add_action('init', array($this, 'load_localisation'), 1);
		
		include( 'class-listeo-core-post-types.php' );
		include( 'class-listeo-core-meta-boxes.php' );
		include( 'class-listeo-core-listing.php' );
		include( 'class-listeo-core-reviews.php' );
		include( 'class-listeo-core-submit.php' );
		include( 'class-listeo-core-shortcodes.php' );
		include( 'class-listeo-core-search.php' );
		include( 'class-listeo-core-users.php' );
		include( 'class-listeo-core-bookmarks.php' );
		include( 'class-listeo-core-coupons.php' );
		include( 'class-listeo-core-activities-log.php' );
		include( 'class-listeo-core-calendar.php' );
		include( 'class-listeo-core-emails.php' );
		include( 'class-listeo-core-messages.php' );
		include( 'class-listeo-core-bookings-calendar.php' );
		include( 'class-listeo-core-calendar-view.php' );
		include( 'class-listeo-core-commissions.php' );
		include( 'class-listeo-core-payouts.php' );
		include( 'class-listeo-core-claim-listings.php' );
		include( 'class-listeo-core-ads.php' );
		include( 'class-listeo-core-bookings-admin.php' );
		include( 'class-listeo-core-stats.php' );
		include( 'class-listeo-core-chart.php' );
		include( 'class-listeo-stripe-connect.php' );
		include( 'class-listeo-core-site-health.php' );
		include( 'class-listeo-core-qr.php' );
		include( 'class-listeo-core-report-listing.php' );
		//include( 'class-icalreader.php' );
		include( 'ical/listeo-core-ical.php' );
		include( 'class-listeo-core-ical.php' );
		// include( 'class-listeo-core-compare.php' );
		
		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		add_action( 'wp_ajax_handle_dropped_media', array( $this, 'listeo_core_handle_dropped_media' ));
		add_action( 'wp_ajax_nopriv_handle_dropped_media', array( $this, 'listeo_core_handle_dropped_media' ));
		add_action( 'wp_ajax_nopriv_handle_delete_media',  array( $this, 'listeo_core_handle_delete_media' ));
		add_action( 'wp_ajax_handle_delete_media',  array( $this, 'listeo_core_handle_delete_media' ));

		add_filter('cron_schedules',array( $this, 'listeo_cron_schedules'));

		add_action('wp_ajax_listingAutocompleteSearch',array( $this, 'listing_autocomplete_search')); 
		// Load API for generic admin functions
		// if ( is_admin() ) {
		// 	$this->admin = new Listeo_Core_Admin_API();
		// }
		
		$this->post_types 	= Listeo_Core_Post_Types::instance();
		$this->meta_boxes 	= new Listeo_Core_Meta_Boxes();
		$this->listing 		= new Listeo_Core_Listing();
		$this->reviews 		= new Listeo_Core_Reviews();
		//$this->submit 		= Listeo_Core_Submit::instance();
		
		$this->search 		= new Listeo_Core_Search();
		$this->users 		= new Listeo_Core_Users();
		$this->bookmarks 	= new Listeo_Core_Bookmarks();
		$this->activites_log = new Listeo_Core_Activities_Log();
		$this->messages 	= new Listeo_Core_Messages();
		$this->calendar 	= Listeo_Core_Calendar::instance();
		$this->calendar_view 	= Listeo_Core_Calendar_View::instance();
		$this->emails 		= Listeo_Core_Emails::instance();
		$this->commissions 	= Listeo_Core_Commissions::instance();
		$this->payouts 		= Listeo_Core_Payouts::instance();
		$this->ical 		= Listeo_Core_iCal::instance();
		$this->coupons 		= new Listeo_Core_Coupons();
		$this->stripe 		= new ListeoStripeConnect();
		$this->sitehealth 		= new Listeo_Core_Site_Health();
		$this->claims 		= new Listeo_Core_Claim_Listings();
		$this->ads 		= new Listeo_Core_Ads();
		$this->qr 		= new Listeo_Core_QR();
		$this->reports = Listeo_Core_Report_Feature::get_instance();
		if(get_option('listeo_stats_status')) {
			$this->stats 		= new Listeo_Core_Stats();
			$this->chart 		= new Listeo_Core_Chart();
		}
		
		
		// Handle localisation
		// $this->load_plugin_textdomain();
		// add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'init', array( $this, 'image_size' ) );
		add_action( 'init', array( $this, 'register_sidebar' ) );
		
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 1 );

		add_filter( 'template_include', array( $this, 'listing_templates' ) );

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 13 );
		add_action( 'plugins_loaded', array( $this, 'listeo_core_update_db_1_3_2' ), 13 );
		add_action( 'plugins_loaded', array( $this, 'listeo_core_update_db_1_5_18' ), 13 );
		add_action( 'plugins_loaded', array( $this, 'listeo_core_update_db_1_5_19' ), 13 );

		add_action( 'admin_notices', array( $this, 'google_api_notice' ));


		add_action('wp_head',  array( $this, 'listeo_og_image' ));

		// Schedule cron jobs
		self::maybe_schedule_cron_jobs();
		

	} // End __construct ()
	  
	/**
	 * Widgets init
	 */
	public function widgets_init() {
		include( 'class-listeo-core-widgets.php' );
	}



	public function include_template_functions() {
		include( LISTEO_PLUGIN_DIR.'/listeo-core-template-functions.php' );
		include( LISTEO_PLUGIN_DIR.'/includes/paid-listings/listeo-core-paid-listings-functions.php' );
		
		
	}

	/* handles single listing and archive listing view */
	public static function listing_templates( $template ) {
		$post_type = get_post_type();  
		$custom_post_types = array( 'listing' );
		
		$template_loader = new Listeo_Core_Template_Loader;
		if ( in_array( $post_type, $custom_post_types ) ) {
			
			if ( is_archive() && !is_author() ) {

				$template = $template_loader->locate_template('archive-' . $post_type . '.php');

				return $template;
			}

			if ( is_single() ) {
				$gallery_type = get_option('listeo_gallery_type','grid');
				if($gallery_type == 'grid'){
					$template = $template_loader->locate_template('single-' . $post_type . '-gallery-grid.php');
				} else {
					$template = $template_loader->locate_template('single-' . $post_type . '.php');
				}

				
				return $template;
			}
		}

		if( is_tax( 'listing_category' ) || is_tax('region') || is_tax('event_category') || is_tax('rental_category') || is_tax('service_category') || is_tax('classifieds_category') || is_tax('listing_feature') ){
			$template = $template_loader->locate_template('archive-listing.php');
		}

		if( is_post_type_archive( 'listing' ) ){

			$template = $template_loader->locate_template('archive-listing.php');

		}
		

		return $template;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
		if (get_option('listeo_otp_status') && !is_user_logged_in() && get_option('users_can_register')) { 
			wp_register_style( $this->_token . '-intltelinput.css', esc_url( $this->assets_url ) . 'css/intltelinput.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-intltelinput.css' );
		}
		

	} // End enqueue_styles ()



	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts() {
		
		// wp_register_script(	'dropzone', esc_url( $this->assets_url ) . 'js/dropzone.js', array( 'jquery' ), $this->_version, true );
		wp_register_script(	'uploads', esc_url( $this->assets_url ) . 'js/uploads.min.js', array( 'jquery' ), $this->_version, true );
		wp_register_script(	'ajaxsearch', esc_url( $this->assets_url ) . 'js/ajax.search.min.js', array( 'jquery' ), $this->_version, true );
		//wp_register_script('intlTelInput', esc_url( $this->assets_url ) . 'js/intlTelInput.min.js', array( 'jquery' ), $this->_version, true );
		
		wp_register_script( $this->_token . '-leaflet-markercluster', esc_url( $this->assets_url ) . 'js/leaflet.markercluster.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-leaflet-geocoder', esc_url( $this->assets_url ) . 'js/control.geocoder.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-leaflet-search', esc_url( $this->assets_url ) . 'js/leaflet-search.src.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-leaflet-bing-layer', esc_url( $this->assets_url ) . 'js/leaflet-bing-layer.min.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-leaflet-google-maps', esc_url( $this->assets_url ) . 'js/leaflet-googlemutant.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-leaflet-tilelayer-here', esc_url( $this->assets_url ) . 'js/leaflet-tilelayer-here.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-leaflet-gesture-handling', esc_url( $this->assets_url ) . 'js/leaflet-gesture-handling.min.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-leaflet', esc_url( $this->assets_url ) . 'js/listeo.leaflet.js', array( 'jquery' ), $this->_version );

		wp_register_script( $this->_token . '-recaptchav3', esc_url( $this->assets_url ) . 'js/recaptchav3.js', array( 'jquery' ), $this->_version );
		
		wp_register_script( $this->_token . '-google-autocomplete', esc_url( $this->assets_url ) . 'js/listeo.google.autocomplete.js', array( 'jquery' ), $this->_version );
		wp_register_script($this->_token . '-chart-min', esc_url($this->assets_url) . '/js/chart.min.js', array('jquery'), $this->_version);
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-bookings', esc_url( $this->assets_url ) . 'js/bookings.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-drilldown', esc_url( $this->assets_url ) . 'js/drilldown.js', array( 'jquery' ), $this->_version );
		wp_register_script( $this->_token . '-submit-listing', esc_url( $this->assets_url ) . 'js/submit-listing.js', array( 'jquery' ), $this->_version );
		// localize script -submit-listing
	
		wp_register_script( $this->_token . '-categories-split-slider', esc_url( $this->assets_url ) . 'js/categories.split.slider.js', array( 'jquery' ), $this->_version );		
 
		wp_register_script( $this->_token . '-pwstrength-bootstrap-min', esc_url( $this->assets_url ) . 'js/pwstrength-bootstrap.min.js', array( 'jquery' ), $this->_version );

		wp_register_script(	'markerclusterer', esc_url( $this->assets_url )  . '/js/markerclusterer.js', array( 'jquery' ), $this->_version );
		wp_register_script( 'infobox-min', esc_url( $this->assets_url )  . '/js/infobox.min.js', array( 'jquery' ), $this->_version  );
		wp_register_script( 'jquery-geocomplete-min',esc_url( $this->assets_url )  . '/js/jquery.geocomplete.min.js', array( 'jquery','maps' ), $this->_version  );
		wp_register_script( 'maps', esc_url( $this->assets_url )  . '/js/maps.js', array( 'jquery','listeo-custom','markerclusterer' ), $this->_version  );



		$map_provider = get_option( 'listeo_map_provider');
		$maps_api_key = get_option( 'listeo_maps_api' );


		if($map_provider != "none"):
			
			wp_enqueue_script( 'leaflet.js', esc_url( $this->assets_url ) . 'js/leaflet.js');

			if( $map_provider == 'bing'){
				
				wp_enqueue_script($this->_token . '-leaflet-bing-layer');
				
			}
			
			if( $map_provider == 'here' ){
				wp_enqueue_script($this->_token . '-leaflet-tilelayer-here');
			}
			
			if( $map_provider == 'google' ){
				wp_enqueue_script( 'google-maps', 'https://maps.google.com/maps/api/js?key='.$maps_api_key. '&libraries=places&callback=Function.prototype' );
			}

			wp_enqueue_script( $this->_token . '-leaflet-google-maps');
			wp_enqueue_script( $this->_token . '-leaflet-geocoder' );
			wp_enqueue_script( $this->_token . '-leaflet-markercluster' );
			wp_enqueue_script( $this->_token . '-leaflet-gesture-handling' );
			wp_enqueue_script( $this->_token . '-leaflet' );

			if( get_option('listeo_map_address_provider') == 'google') {
				wp_enqueue_script( 'google-maps', 'https://maps.google.com/maps/api/js?key='.$maps_api_key. '&libraries=places&callback=Function.prototype' );
				wp_enqueue_script( $this->_token . '-google-autocomplete' );	
			};

		else:
			wp_localize_script(  $this->_token . '-frontend' , 'listeomap',
				    array(
				    	'address_provider'	=> 'off',
				        )
				    );
		endif;




		$recaptcha_status = get_option('listeo_recaptcha');
		$recaptcha_version = get_option('listeo_recaptcha_version');

		$recaptcha_sitekey3 = get_option('listeo_recaptcha_sitekey3');
		if(is_user_logged_in()){
			$recaptcha_status = false;

		}
		if(!empty($recaptcha_status) && $recaptcha_version == 'v3' && !empty($recaptcha_sitekey3)){
			wp_enqueue_script( 'google-recaptcha-listeo', 'https://www.google.com/recaptcha/api.js?render='.trim($recaptcha_sitekey3));	
			wp_enqueue_script( $this->_token . '-recaptchav3' );
		}
		if(!empty($recaptcha_status) && $recaptcha_version == 'v2'){
			wp_enqueue_script( 'google-recaptcha-listeo', 'https://www.google.com/recaptcha/api.js' );
		}
		if(!empty($recaptcha_status) && $recaptcha_version == 'hcaptcha'){
			$hcaptcha_sitekey = get_option('listeo_hcaptcha_sitekey');
			if (!empty($hcaptcha_sitekey)) {
				wp_enqueue_script('hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), null, true);
			}
		}
		if(!is_user_logged_in()){
		 	wp_enqueue_script(  $this->_token . '-pwstrength-bootstrap-min' );
		}

		$_price_min =  $this->get_min_all_listing_price('');
		$_price_max =  $this->get_max_all_listing_price('');


		$ajax_url = admin_url( 'admin-ajax.php', 'relative' );
		$currency = get_option( 'listeo_currency' );
		$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency,false); 
		
		
		$localize_array = array(
				'ajax_url'                	=> $ajax_url,
				'payout_not_valid_email_msg'  => esc_html__('The email address is not valid. Please add a valid email address.', 'listeo_core'),
				'is_rtl'                  	=> is_rtl() ? 1 : 0,
				'lang'                    	=> defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '', // WPML workaround until this is standardized
				'_price_min'		    	=> $_price_min,
				'_price_max'		    	=> $_price_max,
				'currency'		      		=> apply_filters('listeo_core_currency_symbol',get_option( 'listeo_currency' )),
				'currency_position'		    => get_option( 'listeo_currency_postion' ),
				'currency_symbol'		    => apply_filters('listeo_core_currency_symbol',esc_attr($currency_symbol)),
				'submitCenterPoint'		    => get_option( 'listeo_submit_center_point','52.2296756,21.012228700000037' ),
				'centerPoint'		      	=> get_option( 'listeo_map_center_point','52.2296756,21.012228700000037' ),
				'country'		      		=> get_option( 'listeo_maps_limit_country' ),
				'upload'					=> admin_url( 'admin-ajax.php?action=handle_dropped_media' ),
  				'delete'					=> admin_url( 'admin-ajax.php?action=handle_delete_media' ),
  				'color'						=> get_option('pp_main_color','#274abb' ), 
  				'dictDefaultMessage'		=> esc_html__("Drop files here to upload","listeo_core"),
				'dictFallbackMessage' 		=> esc_html__("Your browser does not support drag'n'drop file uploads.","listeo_core"),
				'dictFallbackText' 			=> esc_html__("Please use the fallback form below to upload your files like in the olden days.","listeo_core"),
				'dictFileTooBig' 			=> esc_html__("File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.","listeo_core"),
				'dictInvalidFileType' 		=> esc_html__("You can't upload files of this type.","listeo_core"),
				'dictResponseError'		 	=> esc_html__("Server responded with {{statusCode}} code.","listeo_core"),
				'dictCancelUpload' 			=> esc_html__("Cancel upload","listeo_core"),
				'dictCancelUploadConfirmation' => esc_html__("Are you sure you want to cancel this upload?","listeo_core"),
				'dictRemoveFile' 			=> esc_html__("Remove file","listeo_core"),
				'dictMaxFilesExceeded' 		=> esc_html__("You can not upload any more files.","listeo_core"),
				'areyousure' 				=> esc_html__("Are you sure?","listeo_core"),
				'maxFiles' 					=> get_option('listeo_max_files',10),
				'maxFilesize' 				=> get_option('listeo_max_filesize',2),
				'clockformat' 				=> (get_option('listeo_clock_format','12') == '24') ? true : false,
				'prompt_price'				=> esc_html__('Set price for this date','listeo_core'),
				'menu_price'				=> esc_html__('Price (optional)','listeo_core'),
				'menu_desc'					=> esc_html__('Description','listeo_core'),
				'menu_title'				=> esc_html__('Title','listeo_core'),
				"applyLabel"				=> esc_html__( "Apply",'listeo_core'),
		        "cancelLabel" 				=> esc_html__( "Cancel",'listeo_core'),
		        "clearLabel" 				=> esc_html__( "Clear",'listeo_core'),
		        "fromLabel"					=> esc_html__( "From",'listeo_core'),
		        "toLabel" 					=> esc_html__( "To",'listeo_core'),
		        "customRangeLabel" 			=> esc_html__( "Custom",'listeo_core'),
		        "mmenuTitle" 				=> esc_html__( "Menu",'listeo_core'),
		        "pricingTooltip" 			=> esc_html__( "Click to make this item bookable in booking widget",'listeo_core'),
		        "today" 					=> esc_html__( "Today",'listeo_core'),
		        "yesterday" 				=> esc_html__( "Yesterday",'listeo_core'),
		        "last_7_days" 				=> esc_html__( "Last 7 Days",'listeo_core'),
		        "last_30_days" 				=> esc_html__( "Last 30 Days",'listeo_core'),
		        "this_month" 				=> esc_html__( "This Month",'listeo_core'),
		        "last_month" 				=> esc_html__( "Last Month",'listeo_core'),
		        "map_provider" 				=> get_option('listeo_map_provider','osm'),
		        "address_provider" 			=> get_option('listeo_map_address_provider','osm'),
		        "mapbox_access_token" 		=> get_option('listeo_mapbox_access_token'),
		        "mapbox_retina" 			=> get_option('listeo_mapbox_retina'),
		        "mapbox_style_url" 			=> get_option('listeo_mapbox_style_url') ? get_option('listeo_mapbox_style_url') : 'https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}@2x?access_token=',
		        "bing_maps_key" 			=> get_option('listeo_bing_maps_key'),
		        "thunderforest_api_key" 	=> get_option('listeo_thunderforest_api_key'),
		        "here_app_id" 				=> get_option('listeo_here_app_id'),
		        "here_app_code" 			=> get_option('listeo_here_app_code'),
		        "maps_reviews_text" 		=> esc_html__('reviews','listeo_core'),
		        "maps_noreviews_text" 		=> esc_html__('Not rated yet','listeo_core'),
				'map_bounds_search' => get_option('listeo_map_bounds_search', 'on'),
		        "category_title" 			=> esc_html__('Category Title','listeo_core'),
  				"day_short_su" => esc_html_x("Su", 'Short for Sunday', 'listeo_core'),
	            "day_short_mo" => esc_html_x("Mo", 'Short for Monday','listeo_core'),
	            "day_short_tu" => esc_html_x("Tu", 'Short for Tuesday','listeo_core'),
	            "day_short_we" => esc_html_x("We", 'Short for Wednesday','listeo_core'),
	            "day_short_th" => esc_html_x("Th", 'Short for Thursday','listeo_core'),
	            "day_short_fr" => esc_html_x("Fr", 'Short for Friday','listeo_core'),
	            "day_short_sa" => esc_html_x("Sa", 'Short for Saturday','listeo_core'),
	            "radius_state" => get_option('listeo_radius_state'),
	            "maps_autofit" => get_option('listeo_map_autofit','on'),
	            "maps_autolocate" 	=> get_option('listeo_map_autolocate'),
	            "maps_zoom" 		=> (!empty(get_option('listeo_map_zoom_global'))) ? get_option('listeo_map_zoom_global') : 9,
	            "maps_single_zoom" 	=> (!empty(get_option('listeo_map_zoom_single'))) ? get_option('listeo_map_zoom_single') : 9,
	            "autologin" 	=> get_option('listeo_autologin'),
				'required_fields' 	=> esc_html__('Please fill all required  fields','listeo_core'),
				'exceed_guests_limit' => esc_html__('The total number of adults and children cannot exceed the maximum guest limit','listeo_core'),
	            "no_results_text" 	=> esc_html__('No results match','listeo_core'),
	            "no_results_found_text" 	=> esc_html__('No results found','listeo_core'),
	            "placeholder_text_single" 	=> esc_html__('Select an Option','listeo_core'),
	            "placeholder_text_multiple" => esc_html__('Select Some Options ','listeo_core'),
	            "january" => esc_html__("January",'listeo_core'),
		        "february" => esc_html__("February",'listeo_core'),
		        "march" => esc_html__("March",'listeo_core'),
				"april" => esc_html__("April",'listeo_core'),
		        "may" => esc_html__("May",'listeo_core'),
		        "june" => esc_html__("June",'listeo_core'),
		        "july" => esc_html__("July",'listeo_core'),
		        "august" => esc_html__("August",'listeo_core'),
		        "september" => esc_html__("September",'listeo_core'),
		        "october" => esc_html__("October",'listeo_core'),
		        "november" => esc_html__("November",'listeo_core'),
		        "december" => esc_html__("December",'listeo_core'),
		        "opening_time" => esc_html__("Opening Time",'listeo_core'),
		        "closing_time" => esc_html__("Closing Time",'listeo_core'),
		        "remove" => esc_html__("Remove",'listeo_core'),
				"extra_services_options_type" => get_option('listeo_extra_services_options_type', array()),
		        "onetimefee" => esc_html__("One time fee",'listeo_core'),
		        "bookable_quantity_max" => esc_html__("Max quantity",'listeo_core'),
		        "multiguest" => esc_html__("Multiply by guests",'listeo_core'),
		        "multidays" => esc_html__("Multiply by days",'listeo_core'),
		        "multiguestdays" => esc_html__("Multiply by guest & days",'listeo_core'),
		        "quantitybuttons" => esc_html__("Quantity Buttons",'listeo_core'),
		        "booked_dates" => esc_html__("Those dates are already booked",'listeo_core'),
		        "replied" => esc_html__("Replied",'listeo_core'),
		        "recaptcha_status" 			=> $recaptcha_status,
	            "recaptcha_version" 		=> $recaptcha_version,
	            "recaptcha_sitekey3" 		=> trim($recaptcha_sitekey3),
				'hcaptcha_sitekey'      => trim(get_option('listeo_hcaptcha_sitekey')),
				"elementor_single_gallery" => esc_html__("Gallery", 'listeo_core'),
				"elementor_single_overview" => esc_html__("Overview", 'listeo_core'),
				"elementor_single_details" => esc_html__("Details", 'listeo_core'),
				"elementor_single_pricing" => esc_html__("Pricing", 'listeo_core'),
				"elementor_single_store" => esc_html__("Store", 'listeo_core'),
				"elementor_single_video" => esc_html__("Video", 'listeo_core'),
				"elementor_single_location" => esc_html__("Location", 'listeo_core'),
				"elementor_single_faq" => esc_html__("FAQ", 'listeo_core'),
				"elementor_single_reviews" => esc_html__("Reviews", 'listeo_core'),
				"elementor_single_map" => esc_html__("Location", 'listeo_core'),
				"otp_status" => get_option('listeo_otp_status','on'),
				'start_time_label' => esc_html__('Start Time','listeo_core'),
				'end_time_label' => esc_html__('End Time','listeo_core'),
				'back' => esc_html__('Back','listeo_core'),
				'search' => esc_html__('Search','listeo_core'),
				'copytoalldays' => esc_html__('Copy to all days','listeo_core'),
				'selectimefirst' => esc_html__('Please select time first','listeo_core'),
				'unblock' => esc_html__('Unblock','listeo_core'),
				'block' => esc_html__('Block','listeo_core'),
				'setprice' => esc_html__('Set Price','listeo_core'),
				'one_date_selected' => esc_html__('1 date selected','listeo_core'),
				'dates_selected' => esc_html__(' date(s) selected','listeo_core'),
				'enterPrice' => __('Enter price for', 'listeo_core'),
				'leaveBlank' => __('Leave blank to remove price', 'listeo_core'),

		);
		$criteria_fields = listeo_get_reviews_criteria();
		
		$loc_critera = array();
		foreach ($criteria_fields as $key => $value) {
			$loc_critera[] = $key;
		};
		if(!empty($loc_critera)){
			$localize_array['review_criteria'] = implode(',',$loc_critera);	
		}
		
		wp_localize_script(  $this->_token . '-frontend', 'listeo_core', $localize_array);

		wp_enqueue_script( 'jquery-ui-core' );
		
		wp_enqueue_script( 'jquery-ui-autocomplete' );

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'uploads' );
		if(get_option('listeo_ajax_browsing','on') == 'on'){
			wp_enqueue_script( 'ajaxsearch' );	
		}
		
		
		wp_enqueue_script( $this->_token . '-frontend' );
		wp_enqueue_script( $this->_token . '-bookings' );
		wp_enqueue_script( $this->_token . '-drilldown' );

		$submitpage = get_option('listeo_submit_page');
		// if current page is submit page, enqueue submit-listing.js
		
		if($submitpage && is_page($submitpage)){
			
			wp_enqueue_script( $this->_token . '-submit-listing' );
			// Enqueue FullCalendar core
			wp_enqueue_script(
				'fullcalendar-core',
				'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js',
				array('jquery'),
				'5.11.3',
				true
			);

			// Enqueue FullCalendar styles
			wp_enqueue_style(
				'fullcalendar-style',
				'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css',
				array(),
				'5.11.3'
			);
			$language = get_option('listeo_calendar_view_lang', 'en');

			if ($language != 'en') {
				wp_enqueue_script('listeo-core-fullcalendar-lang', LISTEO_CORE_URL . 'assets/js/locales/' . $language . '.js', array('jquery' ), 1.0, true);
			}
			$data = array(
				'language'   => $language,
			);
			wp_localize_script($this->_token . '-submit-listing', 'listeoCal', $data);
		}
	
		
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		wp_register_script( $this->_token . '-settings', esc_url( $this->assets_url ) . 'js/settings' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-settings' );
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.min.js', array( 'jquery', 'jquery-ui-autocomplete',  'jquery-ui-dialog'), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
		

		$map_provider = get_option( 'listeo_map_provider');
		$maps_api_key = get_option( 'listeo_maps_api' );
		if( get_option('listeo_map_address_provider') == 'google') {
			if($maps_api_key) {
				wp_enqueue_script( 'google-maps', 'https://maps.google.com/maps/api/js?key='.$maps_api_key.'&libraries=places' );	
				wp_register_script( $this->_token . '-admin-maps', esc_url( $this->assets_url ) . 'js/admin.maps' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
				wp_enqueue_script( $this->_token . '-admin-maps' );
			
			}
		} else {
			wp_enqueue_script( 'leaflet.js', esc_url( $this->assets_url ) . 'js/leaflet.js');
			wp_enqueue_script( 'leaflet-geocoder',esc_url( $this->assets_url ) . 'js/control.geocoder.js');
			wp_register_script( $this->_token . '-admin-leaflet', esc_url( $this->assets_url ) . 'js/admin.leaflet' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-admin-leaflet' );
			
		}
		wp_enqueue_script('jquery-ui-datepicker');
		if(function_exists('listeo_date_time_wp_format')) {
			$convertedData = listeo_date_time_wp_format();
	        // add converented format date to javascript
	        wp_localize_script(  $this->_token . '-admin', 'wordpress_date_format', $convertedData );
        }

         wp_localize_script(  $this->_token . '-admin', 'listeo_admin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce('autocompleteSearchNonce'),
            'pp_cancel_payout_confirmation_msg' => esc_html__('Are you sure to cancel the automatic commission that was sent previously by using PayPal Payout?', 'listeo')
        ] );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'listeo_core', false, dirname( plugin_basename( $this->file ) ) . '/languages/' );

	} // End load_localisation ()

	//subscription
	public function init_plugin() {


		$this->submit 		= new Listeo_Core_Submit();
		if ( class_exists( 'WC_Product_Subscription' ) ) {
		include( 'paid-listings/class-listeo-core-paid-subscriptions.php' );			
			include_once( 'paid-listings/class-listeo-core-paid-subscriptions-product.php' );
			include_once( 'paid-listings/class-wc-product-listing-package-subscription.php' );
			

		}


	}

	/**
	 * Adds image sizes
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function image_size () {
		add_image_size('listeo-gallery', 1200, 0, true);
		add_image_size('listeo-listing-grid', 520, 397, true);
		add_image_size('listeo_core-avatar', 590, 590, true);
		add_image_size('listeo_core-preview', 200, 200, true);

	} // End load_localisation ()

	public function register_sidebar () {

		register_sidebar( array(
			'name'          => esc_html__( 'Single listing sidebar', 'listeo_core' ),
			'id'            => 'sidebar-listing',
			'description'   => esc_html__( 'Add widgets here.', 'listeo_core' ),
			'before_widget' => '<div id="%1$s" class="listing-widget widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title margin-bottom-35">',
			'after_title'   => '</h3>',
		) );

		register_sidebar( array(
			'name'          => esc_html__( 'Listings sidebar', 'listeo_core' ),
			'id'            => 'sidebar-listings',
			'description'   => esc_html__( 'Add widgets here.', 'listeo_core' ),
			'before_widget' => '<div id="%1$s" class="listing-widget widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title margin-bottom-35">',
			'after_title'   => '</h3>',
		) );		



	} // End load_localisation ()


	function get_min_listing_price($type) {
		global $wpdb;
		$result = $wpdb->get_var(
	    $wpdb->prepare("
	            SELECT min(m2.meta_value + 0)
	            FROM $wpdb->posts AS p
	            INNER JOIN $wpdb->postmeta AS m1 ON ( p.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta AS m2  ON ( p.ID = m2.post_id )
				WHERE
				p.post_type = 'listing'
				AND p.post_status = 'publish'
				AND ( m1.meta_key = '_offer_type' AND m1.meta_value = %s )
				AND ( m2.meta_key = '_price'  ) AND m2.meta_value != ''
	        ", $type )
	    ) ;

	    return $result;
	}	

	function get_max_listing_price($type) {
		global $wpdb;
		$result = $wpdb->get_var(
	    $wpdb->prepare("
	            SELECT max(m2.meta_value + 0)
	            FROM $wpdb->posts AS p
	            INNER JOIN $wpdb->postmeta AS m1 ON ( p.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta AS m2  ON ( p.ID = m2.post_id )
				WHERE
				p.post_type = 'listing'
				AND p.post_status = 'publish'
				AND ( m1.meta_key = '_offer_type' AND m1.meta_value = %s )
				AND ( m2.meta_key = '_price'  ) AND m2.meta_value != ''
	        ", $type )
	    ) ;
	   

	    return $result;
	}	

	function get_min_all_listing_price() {
		global $wpdb;
		$result = $wpdb->get_var(
	    "	SELECT min(m2.meta_value + 0)
	            FROM $wpdb->posts AS p
	            INNER JOIN $wpdb->postmeta AS m1 ON ( p.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta AS m2  ON ( p.ID = m2.post_id )
				WHERE
				p.post_type = 'listing'
				AND p.post_status = 'publish'
				AND ( m2.meta_key = '_price'  ) AND m2.meta_value != ''
	        "
	    ) ;

	    return $result;
	}	

	function get_max_all_listing_price() {
		global $wpdb;
		$result = $wpdb->get_var(
	   "
	            SELECT max(m2.meta_value + 0)
	            FROM $wpdb->posts AS p
	            INNER JOIN $wpdb->postmeta AS m1 ON ( p.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta AS m2  ON ( p.ID = m2.post_id )
				WHERE
				p.post_type = 'listing'
				AND p.post_status = 'publish'
				AND ( m2.meta_key = '_price'  ) AND m2.meta_value != ''
	        "
	    ) ;
	   

	    return $result;
	}




	function listeo_core_handle_delete_media(){

	    if( isset($_REQUEST['media_id']) ){
	        $post_id = absint( $_REQUEST['media_id'] );
	       // $status = wp_delete_attachment($post_id, true);
		   $status = true;
	        if( $status )
	            echo json_encode(array('status' => 'OK'));
	        else
	            echo json_encode(array('status' => 'FAILED'));
	    }
	    wp_die();
	}


	function listeo_core_handle_dropped_media() {
	    status_header(200);

	    $upload_dir = wp_upload_dir();
	    $upload_path = $upload_dir['path'] . DIRECTORY_SEPARATOR;
//	    $num_files = count($_FILES['file']['tmp_name']);

	    $newupload = 0;

	    if ( !empty($_FILES) ) {
	        $files = $_FILES;
	        foreach($files as $file) {
	            $newfile = array (
	                    'name' => $file['name'],
	                    'type' => $file['type'],
	                    'tmp_name' => $file['tmp_name'],
	                    'error' => $file['error'],
	                    'size' => $file['size']
	            );

	            $_FILES = array('upload'=>$newfile);
	            foreach($_FILES as $file => $array) {
	                $newupload = media_handle_upload( $file, 0 );
	            }
	        }
	    }

	    echo $newupload;    
	    wp_die();
	}

		
		function google_api_notice() {
		
		$map_provider = get_option( 'listeo_map_provider');
		$maps_api_key = get_option( 'listeo_maps_api' );
		if($map_provider == 'google') {

			if(empty($maps_api_key)) {
			    ?>
			    <div class="error notice">
					<p><?php echo esc_html_e('Please configure Google Maps API key to use all Listeo features.') ?> <a href="http://www.docs.purethemes.net/listeo/knowledge-base/getting-google-maps-api-key/"><?php esc_html_e('Check here how to do it.','listeo_core') ?></a></p>
			    	
			        
			    </div>
			    <?php
			}
		}
	}

	function listeo_og_image(){
	    if( is_singular('listing') ) {
	    	
	    	global $post;
	    	
	    	$gallery = (array) get_post_meta( $post->ID, '_gallery', true );
			
			if(!empty($gallery)){
				$ids = array_keys($gallery);
				if(!empty($ids[0])){ 
					$image =  wp_get_attachment_image_url($ids[0],'listeo-listing-grid'); 
				}	
			} else { 
				$image = get_listeo_core_placeholder_image(); 
			}
			if(empty($image)){
				$image = get_the_post_thumbnail_url(get_the_ID(),'full') ;
			}
	       
	        echo '<meta property="og:image" content="'. $image .'" />';
	    }
	}
	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		// $domain = 'listeo_core';

		// $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		// $loaded = load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		// if(!$loaded) {
		// 	load_textdomain($domain, WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo');
		// }

		// load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );

		$domain = 'listeo_core';
		$locale = apply_filters('plugin_locale', determine_locale(), $domain);

		unload_textdomain($domain);

		// Try to load from the languages directory first
		if (load_textdomain($domain, WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo')) {
			return true;
		}

		// Load from plugin languages folder
		return load_plugin_textdomain(
			$domain,
			false,
			dirname(plugin_basename($this->file)) . '/languages/'
		);
	} // End load_plugin_textdomain ()

	/**
	 * Main Listeo_Core Instance
	 *
	 * Ensures only one instance of Listeo_Core is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Listeo_Core()
	 * @return Main Listeo_Core instance
	 */
	public static function instance ( $file = '', $version = '1.2.1' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?','listeo_core' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?','listeo_core' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
		$this->init_user_roles();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	* Schedule cron jobs for Listeo_Core events.
	*/
	public static function maybe_schedule_cron_jobs() {
		
		if ( ! wp_next_scheduled( 'listeo_core_check_for_expired_listings' ) ) {
			wp_schedule_event( time(), 'hourly', 'listeo_core_check_for_expired_listings' );
		}

		if ( ! wp_next_scheduled( 'listeo_core_check_for_expired_bookings' ) ) {
			wp_schedule_event( time(), '5min', 'listeo_core_check_for_expired_bookings' );
		}


		if ( ! wp_next_scheduled( 'listeo_core_check_for_new_messages' ) ) {
			wp_schedule_event( time(), '30min', 'listeo_core_check_for_new_messages' );
		}

		if ( ! wp_next_scheduled( 'listeo_core_check_for_upcoming_payments' ) ) {
			wp_schedule_event( time(), '5min', 'listeo_core_check_for_upcoming_payments' );
		}
		if ( ! wp_next_scheduled( 'listeo_core_check_for_upcoming_booking' ) ) {
			wp_schedule_event( time(), 'hourly', 'listeo_core_check_for_upcoming_booking' );
		}
		if ( ! wp_next_scheduled( 'listeo_core_check_for_past_booking' ) ) {
			wp_schedule_event( time(), 'hourly', 'listeo_core_check_for_past_booking' );
		}

		
		// if (!wp_next_scheduled('cleanup_ad_stats_hook')) {
		// 	wp_schedule_event(time(), 'daily', 'cleanup_ad_stats_hook');
		// }

		//wp_clear_scheduled_hook('cleanup_ad_stats_hook');
		
	}

	function listeo_cron_schedules($schedules){
	    if(!isset($schedules["5min"])){
	        $schedules["5min"] = array(
	            'interval' => 5*60,
	            'display' => __('Once every 5 minutes'));
	    }
	    if(!isset($schedules["30min"])){
	        $schedules["30min"] = array(
	            'interval' => 30*60,
	            'display' => __('Once every 30 minutes'));
	    }
	    if(!isset($schedules["every_week"])){
		    $schedules['every_week'] = array(
	            'interval'  => 604800, //604800 seconds in 1 week
	            'display'   => esc_html__( 'Every Week', 'listeo_core' )
	    	);
	 	}
	    return $schedules;
	}

	function init_user_roles(){
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
 
		if ( is_object( $wp_roles ) ) {
				remove_role( 'owner' );
				add_role( 'owner', __( 'Owner', 'listeo_core' ), array(
					'read'                 => true,
					'upload_files'         => true,
					'edit_listing'         => true,
					//'edit_posts'         => true,
					'read_listing'         => true,
					'delete_listing'       => true,
					'edit_listings'        => true,
					'delete_listings'      => true,
					'edit_listings'        => true,
					'assign_listing_terms' => true,
					'dokandar'                  => true,
				'edit_shop_orders'          => true,
				'edit_product'              => true,
				'read_product'              => true,
				'delete_product'            => true,
				'edit_products'             => true,
				'publish_products'          => true,
				'read_private_products'     => true,
				'delete_products'           => true,
				'delete_products'           => true,
				'delete_private_products'   => true,
				'delete_published_products' => true,
				'delete_published_products' => true,
				'edit_private_products'     => true,
				'edit_published_products'   => true,
				'manage_product_terms'      => true,
				'delete_product_terms'      => true,
				'assign_product_terms'      => true,
			) );

			if (class_exists('WeDevs_Dokan')) :

				$capabilities = [];
				$all_cap      = dokan_get_all_caps();

				foreach ($all_cap as $key => $cap) {
					$capabilities = array_merge($capabilities, array_keys($cap));
				}

				foreach ($capabilities as $key => $capability) {
					$wp_roles->add_cap('owner', $capability);
				}
				
			endif;
			$capabilities = array(
				'core' => array(
					'manage_listings'
				),
				'listing' => array(
					"edit_listing",
					"read_listing",
					"delete_listing",
					"edit_listings",
					"edit_others_listings",
					"publish_listings",
					"read_private_listings",
					"delete_listings",
					"delete_private_listings",
					"delete_published_listings",
					"delete_others_listings",
					"edit_private_listings",
					"edit_published_listings",
					"manage_listing_terms",
					"edit_listing_terms",
					"delete_listing_terms",
					"assign_listing_terms"
				));

				add_role( 'guest', __( 'Guest', 'listeo_core' ), array(
						'read'  => true,
				) );

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
				}
			}
		}

	}
	
	//Add support1.3.1
	function listeo_core_update_db_1_3_2() {
		$db_option = get_option( 'listeo_core_db_version', '1.3.1' );
		if ( $db_option && version_compare( $db_option, '1.3.2', '<' ) ) {
			global $wpdb;

			$sql = "ALTER TABLE `{$wpdb->prefix}listeo_core_conversations` ADD `notification` VARCHAR(10) DEFAULT 'sent' AFTER `last_update`";
			$wpdb->query( $sql );

			update_option( 'listeo_core_db_version', '1.3.2' );
		}
	}

	function listeo_core_update_db_1_5_18() {
		$db_option = get_option( 'listeo_core_db_version', '1.3.2' );
		if ( $db_option && version_compare( $db_option, '1.5.18', '<' ) ) {
			global $wpdb;

			$sql = "ALTER TABLE `{$wpdb->prefix}listeo_core_user_packages` 
			ADD   package_option_booking int(1) NULL,
			ADD	  package_option_reviews int(1) NULL,
			ADD	  package_option_gallery int(1) NULL,
			ADD	  package_option_gallery_limit bigint(20) NULL,
			ADD	  package_option_social_links int(1) NULL,
			ADD	  package_option_opening_hours int(1) NULL,
			ADD	  package_option_pricing_menu int(1) NULL,
			ADD	  package_option_video int(1) NULL,
			ADD	  package_option_coupons int(1) NULL";
			$wpdb->query( $sql );

			update_option( 'listeo_core_db_version', '1.5.18' );
		}
	}

	function listeo_core_update_db_1_5_19() {
		$db_option = get_option( 'listeo_core_db_version', '1.5.18' );
		if ( $db_option && version_compare( $db_option, '1.5.19', '<' ) ) {
			global $wpdb;

			$sql = "ALTER TABLE `{$wpdb->prefix}listeo_core_user_packages` 
			ADD	  package_option_pricing_menu int(1) NULL";
			$wpdb->query( $sql );

			update_option( 'listeo_core_db_version', '1.5.19' );
		}
	}

	function listing_autocomplete_search()
	{
		check_ajax_referer('autocompleteSearchNonce', 'security');
		$search_term = $_REQUEST['term'];
		if (!isset($_REQUEST['term'])) {
			echo json_encode([]);
		}
		$suggestions = [];
		$query = new WP_Query([
			's' => $search_term,
			'posts_per_page' => -1,
			'post_type' => 'listing',
		]);
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$suggestions[] = [
					'id' => get_the_ID(),
					'label' => get_the_title(),
					'link' => get_the_permalink()
				];
			}
			wp_reset_postdata();
		}
		echo json_encode($suggestions);
		wp_die();
	}

}