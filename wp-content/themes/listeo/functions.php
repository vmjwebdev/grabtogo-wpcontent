<?php

/**
 * listeo functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package listeo
 */


add_action('admin_notices', 'add_taxonomy_top_notice');
if (!function_exists('listeo_setup')) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    // $date_format = get_option('date_format');
    // echo strtotime( date( $date_format, strtotime('+5 days') ) );
    global $wpdb;

    //temp fix for listing author 
    // $ownerusers = get_users( 'role=owner' );
    // foreach ( $ownerusers as $user ) {
    //    $user->add_cap('level_1');
    // }

    function cc_mime_types($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }
    add_filter('upload_mimes', 'cc_mime_types');

    function listeo_setup()
    {


        load_theme_textdomain('listeo', get_template_directory() . '/languages');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        add_theme_support('title-tag');


        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
        /*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(900, 500, true); //size of thumbs
        add_image_size('listeo-avatar', 590, 590);
        add_image_size('listeo-blog-post', 1200, 670);
        add_image_size('listeo-blog-related-post', 577, 866);
        add_image_size('listeo-post-thumb', 150, 150, true);
        remove_theme_support('widgets-block-editor');
        add_editor_style();
        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(array(
            'primary' => esc_html__('Main Menu', 'listeo'),
            'mobile' => esc_html__('Side/Mobile Menu Widget', 'listeo'),
            'dashboard_main' => esc_html__('Dashboard Main Menu', 'listeo'),
            'dashboard_main_guest' => esc_html__('Dashboard Main Menu Guest only', 'listeo'),
            'dashboard_main_owner' => esc_html__('Dashboard Main Menu Owner only', 'listeo'),
            'dashboard_listings' => esc_html__('Dashboard Listings Menu', 'listeo'),
            'dashboard_account' => esc_html__('Dashboard Account Menu', 'listeo'),
            'dashboard_dokan' => esc_html__('Dashboard Dokan Menu', 'listeo'),
            'dashboard_top_menu' => esc_html__('Dashboard Top Menu', 'listeo'),
        ));

        do_action('purethemes-testimonials');
        /*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        // Set up the WordPress core custom background feature.
        add_theme_support('custom-background', apply_filters('listeo_custom_background_args', array(
            'default-color' => 'ffffff',
            'default-image' => '',
        )));

        // Add theme support for selective refresh for widgets.
        add_theme_support('customize-selective-refresh-widgets');

        add_theme_support('woocommerce');
    }
endif;
add_action('after_setup_theme', 'listeo_setup');


/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function listeo_content_width()
{
    $GLOBALS['content_width'] = apply_filters('listeo_content_width', 760);
}
add_action('after_setup_theme', 'listeo_content_width', 0);

/**
 * Register widget area.
 */
function listeo_widgets_init()
{
    register_sidebar(array(
        'name'          => esc_html__('Sidebar', 'listeo'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here.', 'listeo'),
        'before_widget' => '<section id="%1$s" class="widget  margin-top-40 %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    register_sidebar(array(
        'name'          => esc_html__('Shop page sidebar', 'workscout', 'listeo'),
        'id'            => 'sidebar-shop',
        'description'   => '',
        'before_widget' => '<section id="%1$s" class="widget  margin-top-40 %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    register_sidebar(array(
        'id' => 'footer1',
        'name' => esc_html__('Footer 1st Column', 'listeo'),
        'description' => esc_html__('1st column for widgets in Footer', 'listeo'),
        'before_widget' => '<aside id="%1$s" class="footer-widget widget %2$s">',
        'after_widget' => '</aside>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
    register_sidebar(array(
        'id' => 'footer2',
        'name' => esc_html__('Footer 2nd Column', 'listeo'),
        'description' => esc_html__('2nd column for widgets in Footer', 'listeo'),
        'before_widget' => '<aside id="%1$s" class="footer-widget widget %2$s">',
        'after_widget' => '</aside>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
    register_sidebar(array(
        'id' => 'footer3',
        'name' => esc_html__('Footer 3rd Column', 'listeo'),
        'description' => esc_html__('3rd column for widgets in Footer', 'listeo'),
        'before_widget' => '<aside id="%1$s" class="footer-widget widget %2$s">',
        'after_widget' => '</aside>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
    register_sidebar(array(
        'id' => 'footer4',
        'name' => esc_html__('Footer 4th Column', 'listeo'),
        'description' => esc_html__('4th column for widgets in Footer', 'listeo'),
        'before_widget' => '<aside id="%1$s" class="footer-widget widget %2$s">',
        'after_widget' => '</aside>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));

    register_sidebar(array(
        'id' => 'mobilemenu',
        'name' => esc_html__('Mobile Menu widget', 'listeo'),
        'description' => esc_html__('Mobilel Menu area', 'listeo'),
        'before_widget' => '<aside id="%1$s" class="mobile-menu-widget widget %2$s">',
        'after_widget' => '</aside>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));

    if (get_option('pp_listeo_sidebar')) :

        $pp_sidebars = get_option('pp_listeo_sidebar');
        if (!empty($pp_sidebars)) :
            foreach ($pp_sidebars as $pp_sidebar) {

                register_sidebar(array(
                    'name' => esc_html($pp_sidebar["sidebar_name"]),
                    'id' => esc_attr($pp_sidebar["sidebar_id"]),
                    'before_widget' => '<section id="%1$s" class="widget %2$s">',
                    'after_widget'  => '</section>',
                    'before_title'  => '<h3 class="widget-title">',
                    'after_title'   => '</h3>',
                ));
            }
        endif;
    endif;
}
add_action('widgets_init', 'listeo_widgets_init');


add_action('after_switch_theme', 'listeo_setup_options');

function listeo_setup_options()
{
    $activation_date = get_option('listeo_activation_date');
    if (!$activation_date) {
        update_option('listeo_activation_date', time());
    }
}

// add_action('wp_ajax_dynamic_css', 'dynamic_css');
// add_action('wp_ajax_nopriv_dynamic_css', 'dynamic_css');
// function dynamic_css()
// {
// 	require(get_template_directory() . '/css/custom.css.php');
// 	exit;
// }

/**
 * Enqueue scripts and styles.
 */
function listeo_scripts()
{

    $my_theme = wp_get_theme();
    //$ver_num = $my_theme->get( 'Version' );
    $ver_num = '1.9.54';

    wp_register_style('bootstrap', get_template_directory_uri() . '/css/bootstrap-grid.css');
    wp_register_style('listeo-woocommerce', get_template_directory_uri() . '/css/woocommerce.min.css');
    wp_register_style('listeo-iconsmind', get_template_directory_uri() . '/css/icons.css');
    wp_register_style('listeo-dokan', get_template_directory_uri() . '/css/dokan.css');
    wp_register_style('simple-line-icons', get_template_directory_uri() . '/css/simple-line-icons.css');
    wp_register_style('font-awesome-5', get_template_directory_uri() . '/css/all.css');
    wp_register_style('font-awesome-5-shims', get_template_directory_uri() . '/css/v4-shims.min.css');
    wp_enqueue_style('listeo-style', get_stylesheet_uri(), array('bootstrap', 'font-awesome-5', 'font-awesome-5-shims', 'simple-line-icons', 'listeo-woocommerce'), $ver_num);
    if (get_option('listeo_iconsmind') != 'hide') {
        wp_enqueue_style('listeo-iconsmind');
    }
    if (function_exists('dokan_get_sellers')) {
        wp_enqueue_style('listeo-dokan');
    }
    wp_register_style('listeo-dark', get_template_directory_uri() . '/css/dark-mode.css');
    if (get_option('listeo_dark_mode') == 'enable') {

        wp_enqueue_style('listeo-dark', get_template_directory_uri() . '/css/dark-mode.css', array('listeo-style'));
    }
    //wp_register_script( 'chosen-min', get_template_directory_uri() . '/js/chosen.min.js', array( 'jquery' ), $ver_num );
    wp_register_script('select2-min', get_template_directory_uri() . '/js/select2.min.js', array('jquery'), $ver_num);
    wp_register_script('bootstrap-select-min', get_template_directory_uri() . '/js/bootstrap-select.min.js', array('jquery'), $ver_num);
    wp_register_script('counterup-min', get_template_directory_uri() . '/js/counterup.min.js', array('jquery'), $ver_num);
    wp_register_script('jquery-scrollto', get_template_directory_uri() . '/js/jquery.scrollto.js', array('jquery'), $ver_num);
    wp_register_script('datedropper', get_template_directory_uri() . '/js/datedropper.js', array('jquery'), $ver_num);
    wp_register_script('dropzone', get_template_directory_uri() . '/js/dropzone.js', array('jquery'), $ver_num);

    wp_register_script('isotope-min', get_template_directory_uri() . '/js/isotope.min.js', array('jquery'), $ver_num);
    wp_register_script('jquery-counterdown-min', get_template_directory_uri() . '/js/jquery.countdown.min.js', array('jquery'), $ver_num);
    wp_register_script('magnific-popup-min', get_template_directory_uri() . '/js/magnific-popup.min.js', array('jquery'), $ver_num);


    wp_register_script('quantityButtons', get_template_directory_uri() . '/js/quantityButtons.js', array('jquery'), $ver_num);
    wp_register_script('rangeslider-min', get_template_directory_uri() . '/js/rangeslider.min.js', array('jquery'), $ver_num);
    wp_register_script('timedropper', get_template_directory_uri() . '/js/timedropper.js', array('jquery'), $ver_num);
    wp_register_script('tooltips-min', get_template_directory_uri() . '/js/tooltips.min.js', array('jquery'), $ver_num);
    wp_register_script('waypoints-min', get_template_directory_uri() . '/js/waypoints.min.js', array('jquery'), $ver_num);
    wp_register_script('slick-min', get_template_directory_uri() . '/js/slick.min.js', array('jquery'), $ver_num);

    wp_register_script('moment', get_template_directory_uri() . '/js/moment.min.js', array('jquery'), $ver_num);
    wp_register_script('daterangerpicker', get_template_directory_uri() . '/js/daterangepicker.js', array('jquery', 'moment'), $ver_num);
    wp_register_script('flatpickr', get_template_directory_uri() . '/js/flatpickr.js', array('jquery'), $ver_num);
    wp_register_script('bootstrap-slider-min', get_template_directory_uri() . '/js/bootstrap-slider.min.js', array('jquery'), $ver_num);

    //wp_enqueue_script( 'chosen-min' );
    wp_enqueue_script('select2-min');
    // enqueue script https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js

    wp_enqueue_script('bootstrap-select-min');
    wp_enqueue_script('counterup-min');
    wp_enqueue_script('datedropper');
    wp_enqueue_script('dropzone');


    if (is_page_template('template-comming-soon.php')) {
        wp_enqueue_script('jquery-counterdown-min');
    }
    wp_enqueue_script('magnific-popup-min');




    wp_enqueue_script('slick-min');
    wp_enqueue_script('quantityButtons');
    wp_enqueue_script('rangeslider-min');
    wp_enqueue_script('timedropper');
    wp_enqueue_script('jquery-scrollto');
    wp_enqueue_script('tooltips-min');
    wp_enqueue_script('waypoints-min');
    wp_enqueue_script('moment');
    wp_enqueue_script('daterangerpicker');
    wp_enqueue_script('bootstrap-slider-min');
    wp_enqueue_script('flatpickr');
    wp_enqueue_script('listeo-custom', get_template_directory_uri() . '/js/custom.js', array('jquery'), '20220821', true);


    $open_sans_args = array(
        'family' => 'Open+Sans:500,600,700' // Change this font to whatever font you'd like
    );
    wp_register_style('google-fonts-open-sans', add_query_arg($open_sans_args, "//fonts.googleapis.com/css"), array(), null);

    $raleway_args = array(
        'family' => 'Raleway:300,400,500,600,700' // Change this font to whatever font you'd like
    );
    wp_register_style('google-fonts-raleway', add_query_arg($raleway_args, "//fonts.googleapis.com/css"), array(), null);

    wp_enqueue_style('google-fonts-raleway');
    wp_enqueue_style('google-fonts-open-sans');




    $convertedData = listeo_date_time_wp_format();

    // add converented format date to javascript
    wp_localize_script('listeo-custom', 'wordpress_date_format', $convertedData);


    $ajax_url = admin_url('admin-ajax.php', 'relative');
    wp_localize_script(
        'listeo-custom',
        'listeo',
        array(
            'ajaxurl'                 => $ajax_url,
            'theme_url'                => get_template_directory_uri(),
            "menu_back"             => esc_html__("Back", 'listeo'),
        )
    );

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    //wp_enqueue_style('dynamic-css', admin_url('admin-ajax.php') . '?action=dynamic_css', array('listeo-style'));
}
add_action('wp_enqueue_scripts', 'listeo_scripts');


add_action('admin_enqueue_scripts', 'listeo_admin_scripts');
function listeo_admin_scripts($hook)
{

    if ($hook == 'edit-tags.php' || $hook == 'term.php' || $hook == 'post.php' || $hook == 'toplevel_page_listeo_settings' || $hook = 'listeo-core_page_listeo_license') {
        wp_enqueue_style('listeo-admin', get_template_directory_uri() . '/css/admin.css');
        wp_enqueue_style('listeo-icons', get_template_directory_uri() . '/css/all.css');
        wp_enqueue_style('listeo-icons-fav4', get_template_directory_uri() . '/css/fav4-shims.min.css');
        wp_enqueue_style('listeo-iconsmind', get_template_directory_uri() . '/css/icons.css');
        wp_enqueue_script('listeo-icon-selector', get_template_directory_uri() . '/js/iconselector.min.js', array('jquery'), '20180323', true);
    }
}


function listeo_add_editor_styles()
{
    add_editor_style('custom-editor-style.css');
}
add_action('admin_init', 'listeo_add_editor_styles');

/**
 * Load aq_resizer.
 */
require get_template_directory() . '/inc/aq_resize.php';


/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Custom meta-boxes
 */
require get_template_directory() . '/inc/meta-boxes.php';

/*
 * Load the Kirki Fallback class
 */
require get_template_directory() . '/inc/kirki-fallback.php';


/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

function listeo_add_apple_google_pay()
{
    return array(
        'simple',
        'variable',
        'variation',
        'subscription',
        'variable-subscription',
        'subscription_variation',
        'listing_booking',
        'listing_package_subscription',
        'booking',
        'bundle',
        'composite'
    );
}
add_filter('wc_stripe_payment_request_supported_types', 'listeo_add_apple_google_pay');
/**
 * Load TGMPA file.
 */
require get_template_directory() . '/inc/tgmpa.php';



/**
 * Load big map.
 */
require get_template_directory() . '/inc/properties-maps.php';

/**
 * Load woocommerce 
 */
require get_template_directory() . '/inc/woocommerce.php';
/**
 * Load megamenu 
 */
require get_template_directory() . '/inc/megamenu.php';
if (function_exists('dokan_get_sellers')) {
    require get_template_directory() . '/inc/dokan.php';
    require get_template_directory() . '/inc/dokan-widget.php';
}


/**
 * Setup Wizard
 */
require get_template_directory() . '/envato_setup/envato_setup.php';

// Enable shortcodes in text widgets
add_filter('widget_text', 'do_shortcode');

function listeo_disable_admin_bar()
{
    if (current_user_can('administrator')) {
        // user can view admin bar
        show_admin_bar(true); // this line isn't essentially needed by default...
    } else {
        // hide admin bar
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'listeo_disable_admin_bar');


function listeo_new_customer_data($new_customer_data)
{
    $new_customer_data['role'] = 'owner';
    return $new_customer_data;
}
add_filter('woocommerce_new_customer_data', 'listeo_new_customer_data');



function listeo_noindex_for_products()
{
    if (is_singular('product')) {
        global $post;
        if (function_exists('wc_get_product')) {
            $product = wc_get_product($post->ID);
            //listing_booking, listing_package_subscription, listing_package
            if ($product->is_type('listing_booking') || $product->is_type('listing_package_subscription') || $product->is_type('listing_package')) {
                echo '<meta name="robots" content="noindex, follow">';
            }
        }
    }
}

add_action('wp_head', 'listeo_noindex_for_products');

// ,make filter listeo_core_delete_expired_listings  __return_true
//add_filter('listeo_core_delete_expired_listings', '__return_true');



function listeo_register_elementor_locations($elementor_theme_manager)
{

    $elementor_theme_manager->register_location('header');
    $elementor_theme_manager->register_location('footer');
}
add_action('elementor/theme/register_locations', 'listeo_register_elementor_locations');


if (!class_exists("b472b0Base")) {
    require_once get_template_directory() . '/inc/b472b0Base.php';
}
require get_template_directory() . '/inc/licenser.php';




add_filter('woocommerce_get_star_rating_html', 'replace_star_ratings', 10, 3);
function replace_star_ratings($html, $rating, $count)
{
    $html = '<span style="width:' . (($rating / 5) * 100) . '%">';

    if (0 < $count) {
        /* translators: 1: rating 2: rating count */
        $html .= sprintf(_n('%1$s based on %2$s rating', 'Rated %1$s based on %2$s ratings', $count, 'listeo'), '<strong class="rating">' . esc_html($rating) . '</strong>', '<span class="rating">' . esc_html($count) . '</span>');
    } else {
        /* translators: %s: rating */
        $html .= sprintf(esc_html__('%s out of 5', 'listeo'), '<strong class="rating">' . esc_html($rating) . '</strong>');
    }

    $html .= '</span>';
    return $html;
}


/**
 * Automatically set order status to Completed for listing_booking products.
 *
 * @param int $order_id The order ID.
 */
function set_order_status_to_completed_for_listing_booking($order_id)
{
    // Get the order object
    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    // Check if the order contains any listing_booking products
    $has_listing_booking = false;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        if ($product && 'listing_booking' === $product->get_type()) {
            $has_listing_booking = true;
            break; // Exit the loop if a listing_booking product is found
        }
    }

    // Set order status to Completed if it contains listing_booking products
    if ($has_listing_booking) {
        $order->update_status('completed');
    }
}

// Hook this function to the 'woocommerce_thankyou' action
//add_action('woocommerce_thankyou', 'set_order_status_to_completed_for_listing_booking');

// add_filter('submit_listing_form_wp_editor_args', 'customize_editor_toolbar');
// function customize_editor_toolbar($args)
// {
// 	$args['tinymce']['toolbar1'] = 'styleselect, bold,italic,alignleft,aligncenter,alignright,|,bullist,numlist,|,undo,redo';
// 	return $args;
// }

// add_filter('listeo_core_service_timepicker_value', 'default_time');
// function default_time($time)
// {
// 	//return current time	
// 	return date('H:i');

// }

// use filter listeo_submit_page to change url of add listing button
// add_filter('listeo_submit_page', 'listeo_submit_page_change');
// function listeo_submit_page_change($submit_page)
// {
// 	$submit_page = 99; // where 99 is the page id you want to use	
// 	return $submit_page;
// }




function listeo_admin_custom_currency_change($currency_symbol)
{
    if (get_option('listeo_currency_custom')) {
        return get_option('listeo_currency_custom');
    } else {
        return $currency_symbol;
    }
}
add_filter('listeo_core_currency_symbol', 'listeo_admin_custom_currency_change');


//add_filter('woocommerce_available_payment_gateways', 'remove_dokan_payment_gateways_for_booking_products');
function remove_dokan_payment_gateways_for_booking_products($available_gateways)
{
    if (get_option('disable_dokan_stripe_payment_on_boookings')) {


        // Check if we are on the "pay for order" page
        if (is_checkout_pay_page()) {
            // Get the current order
            $order_id = absint(get_query_var('order-pay'));
            $order = wc_get_order($order_id);

            // Check if the order contains any products
            if ($order && count($order->get_items()) > 0) {
                foreach ($order->get_items() as $item) {
                    // Check if the product is of type "booking"
                    $product = $item->get_product();
                    if ($product && $product->is_type('listing_booking')) {
                        // Remove the Cash on Delivery payment gateway
                        unset($available_gateways['dokan_stripe_express']);
                        unset($available_gateways['dokan-stripe-connect']);
                        break; // No need to continue checking if we found a booking product
                    }
                }
            }
        }
    }

    return $available_gateways;
}

add_action('listeo/single-listing/after_content', 'listeo_single_listing_after_content');
function listeo_single_listing_after_content()
{
    global $post;
    $listing_id = $post->ID;
    //get custom field id soundcloud
    $soundcloud = get_post_meta($listing_id, 'soundcloud', true);
    if ($soundcloud) {
        echo '<div class="soundcloud-embed">';
        echo do_shortcode('[soundcloud url="' . $soundcloud . '"]');
        echo '</div>';
    }
}


//add shortcode for soundcloud
function listeo_soundcloud_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'url' => '',
    ), $atts, 'soundcloud');
    $url = $atts['url'];
    if ($url) {
        return '<iframe width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=' . $url . '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe>';
    }
}
add_shortcode('soundcloud', 'listeo_soundcloud_shortcode');


add_filter('get_terms', 'ts_get_subcategory_terms', 10, 3);
function ts_get_subcategory_terms($terms, $taxonomies, $args)
{
    $new_terms = array();
    // if it is a product category and on the shop page
    if (!is_array($taxonomies)) {
        return $terms;
    }
    if (in_array('product_cat', $taxonomies) && !is_admin() && is_shop()) {
        foreach ($terms as $key => $term) {
            if (!in_array($term->slug, array('listeo-booking'))) { //pass the slug name here
                $new_terms[] = $term;
            }
        }
        $terms = $new_terms;
    }
    return $terms;
}


// $listing_id = 560;
// $date = '2025-01-21';
// $first_available = Listeo_Core_Bookings_Calendar::get_first_available_hour($listing_id, $date);

// $listing_id = 560;
// $date = '2025-01-21';
// $available_slots = Listeo_Core_Bookings_Calendar::get_available_hours_between_bookings($listing_id, $date);

// foreach ($available_slots as $slot) {
// 	echo "Available from: " . date('H:i', strtotime($slot['start']));
// 	echo " to: " . date('H:i', strtotime($slot['end'])) . "\n";
// }
// var_dump($first_available);


// get wpbd prefix

//add action for listeo_archive_split_before_title that displays the h2 title based on searched query, for exampl if location is Miami and keyword is "car" then the title will be "Car Rentals in Miami"
//add_action('listeo_archive_split_before_title', 'listeo_archive_split_before_title');
function listeo_archive_split_before_title()
{
    //get search query

    //get location
    $location = get_query_var('location_search');
    $keyword = get_query_var('keyword_search');
    //get category
    $category = get_query_var('tax-listing_category');
    $region = get_query_var('tax-region');
    if (is_array($category)) {
        $category = $category[0];
        // get category name
        $category = get_term_by('slug', $category, 'listing_category');
    } else {
        $category = get_term_by('slug', $category, 'listing_category');
    }
    if (is_array($region)) {
        $region = $region[0];

        // get region name
        $region = get_term_by('slug', $region, 'region');
    } else {
        $region = get_term_by('slug', $region, 'region');
    }

    // display title (Car Rentals in Miami) based on search query, check if location or region or category are set, there could be multiple combinations
    $title = '';
    if ($keyword) {
        $title .= $keyword;
    }
    if ($category) {
        $title .= ' in ' . $category->name;
    }
    if ($location) {
        $title .= ' in ' . $location;
    }
    if ($region) {
        $title .= ' in ' . $region->name;
    }
    echo '<h2>' . $title . '</h2>';
}


function add_taxonomy_top_notice()
{
    $screen = get_current_screen();

    // Replace 'your_taxonomy' with your taxonomy name
    if (in_array($screen->taxonomy, array('event_category', 'service_category', 'rental_category'))) {
        // For both add new and edit screens
        echo '<div class="listeo-admin-test-api" style="margin: 10px 0;"><p>';
        echo 'Check how categories and listing type taxonomies can be used in Listeo. <a href="https://docs.purethemes.net/listeo/knowledge-base/how-main-and-sub-categories-work/">Learn more</a>';
        echo '</p></div>';
    }
    if (in_array($screen->taxonomy, array('listing_feature'))) {
        // For both add new and edit screens
        echo '<div class="listeo-admin-test-api" style="margin: 10px 0;"><p>';
        echo 'Check how categories and features can be used in Listeo. <a href=https://docs.purethemes.net/listeo/knowledge-base/categories-and-features/">Learn more</a>';
        echo '</p></div>';
    }
}

remove_action('woocommerce_order_item_meta_start', 'dokan_attach_vendor_name', 10, 2);

