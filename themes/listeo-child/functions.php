<?php
/**
 * GrabToGo optimized child theme functions.php
 */

// ============ Enqueue Styles & Scripts ============
function grabtogo_enqueue_assets() {
    // jQuery
    wp_enqueue_script( 'jquery' );

    // Parent style & Google Fonts
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'grabtogo-fonts',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
        [],
        null
    );

    // Custom CSS
    $css_path = get_stylesheet_directory() . '/assets/css/custom.css';
    if ( file_exists( $css_path ) ) {
        wp_enqueue_style(
            'grabtogo-custom-css',
            get_stylesheet_directory_uri() . '/assets/css/custom.css',
            [],
            filemtime( $css_path )
        );
    }

    // Custom JS + AJAX URL
    $js_path = get_stylesheet_directory() . '/assets/js/custom.js';
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script(
            'grabtogo-custom-js',
            get_stylesheet_directory_uri() . '/assets/js/custom.js',
            [ 'jquery' ],
            filemtime( $js_path ),
            true
        );
        wp_localize_script(
            'grabtogo-custom-js',
            'gtg_ajax',
            [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
        );
    }
}
add_action( 'wp_enqueue_scripts', 'grabtogo_enqueue_assets' );


// ============ Redirect /my-account/ → /my-profile/ ============
function grabtogo_redirect_my_account() {
    if ( is_page( 'my-account' ) ) {
        wp_redirect( home_url( '/my-profile/' ) );
        exit;
    }
}
add_action( 'template_redirect', 'grabtogo_redirect_my_account' );


// ============ AJAX: Send OTP ============
function gtg_send_otp() {
    $email = sanitize_email( $_POST['email'] );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( 'Invalid email.' );
    }

    $otp = rand( 100000, 999999 );
    set_transient( 'gtg_otp_' . md5( $email ), $otp, 10 * MINUTE_IN_SECONDS );
    wp_mail( $email, 'Your OTP for GrabToGo', "Your OTP is: $otp\n\nExpires in 10 minutes." );
    wp_send_json_success( 'OTP sent.' );
}
add_action( 'wp_ajax_gtg_send_otp', 'gtg_send_otp' );
add_action( 'wp_ajax_nopriv_gtg_send_otp', 'gtg_send_otp' );


// ============ AJAX: Verify OTP ============
function gtg_verify_otp() {
    $email   = sanitize_email( $_POST['email'] );
    $entered = sanitize_text_field( $_POST['otp'] );
    $stored  = get_transient( 'gtg_otp_' . md5( $email ) );

    if ( $entered == $stored ) {
        set_transient( 'gtg_otp_verified_' . md5( $email ), true, 30 * MINUTE_IN_SECONDS );
        wp_send_json_success( 'OTP verified.' );
    }

    wp_send_json_error( 'Incorrect OTP.' );
}
add_action( 'wp_ajax_gtg_verify_otp', 'gtg_verify_otp' );
add_action( 'wp_ajax_nopriv_gtg_verify_otp', 'gtg_verify_otp' );


// ============ Shortcode: Vendor Registration Form ============
function grabtogo_vendor_registration_form_shortcode() {
    ob_start(); ?>
    <form method="POST" enctype="multipart/form-data" id="gtg-reg-form" class="gtg-registration-form" style="max-width:500px;margin:auto;background:#fff;padding:30px;border-radius:12px;">
        <h3 style="text-align:center;">Vendor Registration</h3>

        <!-- Email & OTP -->
        <label>Email*</label>
        <input type="email" name="gtg_email" id="gtg_email" required>
        <button type="button" id="send_otp_btn">Send OTP</button>

        <div id="otp_section" style="display:none;">
            <label>Enter OTP*</label>
            <input type="text" id="gtg_otp_input">
            <button type="button" id="verify_otp_btn">Verify OTP</button>
        </div>
        <div id="otp_status"></div>

        <!-- Names -->
        <label>First Name*</label>
        <input type="text" name="gtg_first_name" id="gtg_first_name" required>
        <label>Last Name*</label>
        <input type="text" name="gtg_last_name" id="gtg_last_name" required>

        <!-- Password & Shop Details -->
        <label>Password*</label>
        <div class="gtg-password-container">
            <input type="password" name="gtg_password" required>
            <i class="fa-solid fa-eye gtg-toggle-password" title="Show/Hide Password"></i>
        </div>

        <label>Shop Name*</label>
        <input type="text" name="gtg_shop_name" required>
        <label>Owner Name*</label>
        <input type="text" name="gtg_owner_name" required>

        <!-- Address -->
        <label>Shop Address*</label>
        <input type="text" name="gtg_shop_address" required>
        <label>City*</label>
        <input type="text" name="gtg_shop_city" required>

        <!-- State -->
        <?php if ( class_exists( 'WC_Countries' ) ) : 
            $states = ( new WC_Countries() )->get_states( 'IN' );
            woocommerce_form_field( 'gtg_shop_state', [
                'type'     => 'select',
                'label'    => __( 'State*', 'grabtogo' ),
                'required' => true,
                'options'  => array_merge( [ '' => __( 'Select a state', 'grabtogo' ) ], $states ),
            ], '' );
        endif; ?>

        <label>Postcode*</label>
        <input type="text" name="gtg_shop_postcode" required>
        <input type="hidden" name="gtg_country" value="IN">

        <!-- Contact & GST -->
        <label>Phone Number (WhatsApp)*</label>
        <input type="text" name="gtg_whatsapp" required>
        <label>GST Number*</label>
        <input type="text" name="gtg_gst_number" required>
        <label>Upload GST Document*</label>
        <input type="file" name="gtg_document" accept=".jpg,.jpeg,.png,.pdf" required>

        <!-- Referral -->
        <label>Agent Name (if referred)</label>
        <input type="text" name="gtg_agent_name">

        <!-- Submit -->
        <button type="submit" name="gtg_register_submit" id="gtg_submit_btn" disabled>
            Register
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'grabtogo_vendor_registration_form', 'grabtogo_vendor_registration_form_shortcode' );


// ============ Registration Handler ============
function grabtogo_handle_registration() {
    if ( ! isset( $_POST['gtg_register_submit'] ) ) {
        return;
    }

    // OTP & existence checks
    $email  = sanitize_email( $_POST['gtg_email'] );
    if ( ! get_transient( 'gtg_otp_verified_' . md5( $email ) ) ) {
        wp_die( 'Please verify your email with OTP first.' );
    }
    if ( email_exists( $email ) ) {
        wp_die( 'Email already exists.' );
    }

    // Collect & sanitize
    $first_name  = sanitize_text_field( $_POST['gtg_first_name'] );
    $last_name   = sanitize_text_field( $_POST['gtg_last_name'] );
    $password    = $_POST['gtg_password'];
    $shop_name   = sanitize_text_field( $_POST['gtg_shop_name'] );
    $owner_name  = sanitize_text_field( $_POST['gtg_owner_name'] );
    $address     = sanitize_text_field( $_POST['gtg_shop_address'] );
    $city        = sanitize_text_field( $_POST['gtg_shop_city'] );
    $state       = sanitize_text_field( $_POST['gtg_shop_state'] );
    $postcode    = sanitize_text_field( $_POST['gtg_shop_postcode'] );
    $whatsapp    = sanitize_text_field( $_POST['gtg_whatsapp'] );
    $gst_number  = sanitize_text_field( $_POST['gtg_gst_number'] );
    $agent_name  = sanitize_text_field( $_POST['gtg_agent_name'] );

    // Create user
    $user_id = wp_create_user( $email, $password, $email );
    wp_update_user([
        'ID'           => $user_id,
        'display_name' => $shop_name,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
    ]);

    // Save usermeta
    $meta_map = [
        'gtg_shop_name'     => $shop_name,
        'gtg_owner_name'    => $owner_name,
        'gtg_shop_address'  => $address,
        'gtg_shop_city'     => $city,
        'gtg_shop_state'    => $state,
        'gtg_shop_postcode' => $postcode,
        'gtg_whatsapp'      => $whatsapp,
        'gtg_gst_number'    => $gst_number,
        'gtg_agent_name'    => $agent_name,
    ];
    foreach ( $meta_map as $key => $val ) {
        update_user_meta( $user_id, $key, $val );
    }

    // WooCommerce billing sync
    $wc_map = [
        'billing_first_name' => $first_name,
        'billing_last_name'  => $last_name,
        'billing_address_1'  => $address,
        'billing_city'       => $city,
        'billing_state'      => $state,
        'billing_postcode'   => $postcode,
        'billing_country'    => 'IN',
        'billing_phone'      => $whatsapp,
        'billing_company'    => $shop_name,
    ];
    foreach ( $wc_map as $key => $val ) {
        update_user_meta( $user_id, $key, $val );
    }

    // Handle GST doc upload
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $move = wp_handle_upload( $_FILES['gtg_document'], [ 'test_form' => false ] );
    if ( isset( $move['file'] ) && ! isset( $move['error'] ) ) {
        $attach_id = wp_insert_attachment([
            'post_mime_type' => $move['type'],
            'post_title'     => sanitize_file_name( $move['file'] ),
            'post_status'    => 'inherit',
        ], $move['file'] );
        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $move['file'] ) );
        update_user_meta( $user_id, 'gtg_uploaded_document', $attach_id );
    }

    // Dokan store setup
    $slug = sanitize_title( $shop_name );
    update_user_meta( $user_id, 'dokan_store_name', $shop_name );
    update_user_meta( $user_id, 'store_name',       $shop_name );
    update_user_meta( $user_id, 'dokan_store_url',  $slug );
    update_user_meta( $user_id, 'dokan_profile_settings', [
        'store_name'  => $shop_name,
        'store_url'   => $slug,
        'phone'       => $whatsapp,
        'address'     => [
            'street_1' => $address,
            'city'     => $city,
            'zip'      => $postcode,
            'state'    => $state,
            'country'  => 'IN',
        ],
        'document_id' => $attach_id ?? '',
        'gst_number'  => $gst_number,
    ]);

    // Emails
    wp_mail(
        'info@grabtogo.in',
        'New Vendor Registration',
        "Shop: $shop_name\nOwner: $owner_name\nEmail: $email\nPhone: $whatsapp\nAddress: $address, $city, $state, $postcode\nGST: $gst_number\nAgent: $agent_name",
        [ 'Content-Type: text/plain; charset=UTF-8' ]
    );
    wp_mail(
        $email,
        'Thanks for registering with GrabToGo',
        "Hi $shop_name,\n\nThanks for registering. We’ll review your document shortly and email you when you can continue onboarding.\n\n– Team GrabToGo"
    );

    // Redirect
    wp_redirect( home_url( '/thank-you-registration/' ) );
    exit;
}
add_action( 'template_redirect', 'grabtogo_handle_registration' );


// ============ WooCommerce Checkout Tweaks ============
function grabtogo_checkout_fields_tweak( $fields ) {
    unset( $fields['billing']['billing_last_name'] );
    if ( isset( $fields['billing']['billing_state'] ) ) {
        $fields['billing']['billing_state']['required'] = false;
    }
    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'grabtogo_checkout_fields_tweak' );


// ============ Auto-fill Listing Title ============
function grabtogo_autofill_listing_title_script() {
    if ( ! is_page( 'add-listing' ) ) {
        return;
    }
    $store = get_user_meta( get_current_user_id(), 'dokan_store_name', true );
    if ( $store ) {
        ?>
        <script>
        jQuery(function($){
            $('input[name="listing_title"]').val('<?php echo esc_js( $store ); ?>').prop('readonly',true);
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'grabtogo_autofill_listing_title_script' );


// ============ Auto-fill Other Listing Fields ============
function grabtogo_get_vendor_active_listing_data( $user_id ) {
    $q = new WP_Query([
        'post_type'      => 'listing',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'posts_per_page' => 1,
    ]);
    if ( $q->have_posts() ) {
        $p = $q->posts[0];
        return [
            '_job_logo'         => get_post_meta( $p->ID, '_job_logo', true ),
            '_phone'            => get_post_meta( $p->ID, '_phone', true ),
            '_email'            => get_post_meta( $p->ID, '_email', true ),
            '_website'          => get_post_meta( $p->ID, '_website', true ),
            '_facebook'         => get_post_meta( $p->ID, '_facebook', true ),
            '_instagram'        => get_post_meta( $p->ID, '_instagram', true ),
            '_whatsapp'         => get_post_meta( $p->ID, '_whatsapp', true ),
            '_job_location'     => get_post_meta( $p->ID, '_job_location', true ),
            'geolocation_lat'   => get_post_meta( $p->ID, 'geolocation_lat', true ),
            'geolocation_long'  => get_post_meta( $p->ID, 'geolocation_long', true ),
            '_job_address'      => get_post_meta( $p->ID, '_job_address', true ),
            '_friendly_address' => get_post_meta( $p->ID, '_friendly_address', true ),
            '_region'           => get_post_meta( $p->ID, '_region', true ),
        ];
    }
    return [];
}
function grabtogo_autofill_listing_fields_script() {
    if ( ! is_page( 'add-listing' ) ) {
        return;
    }
    $data = grabtogo_get_vendor_active_listing_data( get_current_user_id() );
    if ( $data ) {
        ?>
        <script>
        jQuery(function($){
            <?php foreach ( $data as $field => $val ) : ?>
            <?php if ( $val ) : ?>
                $('input[name="<?php echo esc_js( $field ); ?>"]').val('<?php echo esc_js( $val ); ?>');
            <?php endif; ?>
            <?php endforeach; ?>
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'grabtogo_autofill_listing_fields_script' );

add_action( 'user_register', function( $user_id ) {
    $user = get_userdata( $user_id );
    error_log( 'NEW USER ROLES: ' . print_r( $user->roles, true ) );
}, 20 );

add_action( 'set_user_role', function( $user_id ) {
    $user = get_userdata( $user_id );
    error_log( 'ROLE CHANGE ROLES: ' . print_r( $user->roles, true ) );
}, 20 );

// ============ VENDOR STORIES FEATURE ============

// Register Custom Post Type: vendor_story
function grabtogo_register_vendor_stories_cpt() {
    register_post_type( 'vendor_story', [
        'labels' => [
            'name'          => 'Vendor Stories',
            'singular_name' => 'Vendor Story',
            'add_new'       => 'Add Story',
            'add_new_item'  => 'Add New Story',
            'edit_item'     => 'Edit Story',
            'new_item'      => 'New Story',
            'view_item'     => 'View Story',
            'search_items'  => 'Search Stories',
            'not_found'     => 'No stories found',
            'all_items'     => 'All Stories'
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'edit.php?post_type=listing',
        'supports'      => [ 'title', 'editor', 'thumbnail', 'author' ],
        'meta_box_cb'   => false,
        'capability_type' => 'post',
        'map_meta_cap'  => true,
    ]);
}
add_action( 'init', 'grabtogo_register_vendor_stories_cpt' );

// Add story meta boxes
function grabtogo_add_story_meta_boxes() {
    add_meta_box(
        'story_details',
        'Story Details',
        'grabtogo_story_meta_box_callback',
        'vendor_story',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'grabtogo_add_story_meta_boxes' );

function grabtogo_story_meta_box_callback( $post ) {
    wp_nonce_field( 'story_meta_box', 'story_meta_box_nonce' );
    
    $vendor_id = get_post_meta( $post->ID, '_story_vendor_id', true );
    $city = get_post_meta( $post->ID, '_story_city', true );
    $expiry = get_post_meta( $post->ID, '_story_expiry', true );
    $media_url = get_post_meta( $post->ID, '_story_media_url', true );
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="story_vendor_id">Vendor ID:</label></th>';
    echo '<td><input type="number" id="story_vendor_id" name="story_vendor_id" value="' . esc_attr( $vendor_id ) . '" /></td></tr>';
    
    echo '<tr><th><label for="story_city">City:</label></th>';
    echo '<td><input type="text" id="story_city" name="story_city" value="' . esc_attr( $city ) . '" /></td></tr>';
    
    echo '<tr><th><label for="story_expiry">Expiry Date:</label></th>';
    echo '<td><input type="datetime-local" id="story_expiry" name="story_expiry" value="' . esc_attr( $expiry ) . '" /></td></tr>';
    
    echo '<tr><th><label for="story_media_url">Media URL:</label></th>';
    echo '<td><input type="url" id="story_media_url" name="story_media_url" value="' . esc_attr( $media_url ) . '" /></td></tr>';
    echo '</table>';
}

// Save story meta
function grabtogo_save_story_meta( $post_id ) {
    if ( ! isset( $_POST['story_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['story_meta_box_nonce'], 'story_meta_box' ) ) {
        return;
    }
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    $fields = [ '_story_vendor_id', '_story_city', '_story_expiry', '_story_media_url' ];
    foreach ( $fields as $field ) {
        $key = str_replace( '_story_', '', $field );
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $key ] ) );
        }
    }
}
add_action( 'save_post', 'grabtogo_save_story_meta' );

// AJAX: Upload story from vendor dashboard
function gtg_upload_vendor_story() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Please log in.' );
    }
    
    $user_id = get_current_user_id();
    $caption = sanitize_textarea_field( $_POST['caption'] ?? '' );
    $expiry_hours = intval( $_POST['expiry_hours'] ?? 24 );
    $user_city = get_user_meta( $user_id, 'gtg_shop_city', true );
    
    // Handle file upload
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    
    $uploadedfile = $_FILES['story_media'];
    $upload_overrides = [ 'test_form' => false ];
    $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
    
    if ( $movefile && ! isset( $movefile['error'] ) ) {
        // Create story post
        $story_id = wp_insert_post([
            'post_type'    => 'vendor_story',
            'post_title'   => 'Story by ' . get_user_meta( $user_id, 'gtg_shop_name', true ),
            'post_content' => $caption,
            'post_author'  => $user_id,
            'post_status'  => 'publish',
        ]);
        
        if ( $story_id ) {
            // Set expiry timestamp
            $expiry_timestamp = date( 'Y-m-d H:i:s', strtotime( "+{$expiry_hours} hours" ) );
            
            // Save meta
            update_post_meta( $story_id, '_story_vendor_id', $user_id );
            update_post_meta( $story_id, '_story_city', $user_city );
            update_post_meta( $story_id, '_story_expiry', $expiry_timestamp );
            update_post_meta( $story_id, '_story_media_url', $movefile['url'] );
            
            // Set featured image
            $attachment_id = wp_insert_attachment([
                'post_mime_type' => $movefile['type'],
                'post_title'     => sanitize_file_name( $movefile['file'] ),
                'post_status'    => 'inherit',
            ], $movefile['file'], $story_id );
            
            require_once ABSPATH . 'wp-admin/includes/image.php';
            wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $movefile['file'] ) );
            set_post_thumbnail( $story_id, $attachment_id );
            
            wp_send_json_success( 'Story uploaded successfully!' );
        }
    }
    
    wp_send_json_error( 'Failed to upload story.' );
}
add_action( 'wp_ajax_gtg_upload_vendor_story', 'gtg_upload_vendor_story' );

// AJAX: Get stories for current user location
function gtg_get_location_stories() {
    $user_city = sanitize_text_field( $_POST['user_city'] ?? '' );
    
    if ( empty( $user_city ) ) {
        wp_send_json_error( 'City not provided.' );
    }
    
    // Get active stories for this city
    $stories = get_posts([
        'post_type'      => 'vendor_story',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'meta_query'     => [
            [
                'key'     => '_story_city',
                'value'   => $user_city,
                'compare' => '='
            ],
            [
                'key'     => '_story_expiry',
                'value'   => current_time( 'mysql' ),
                'compare' => '>'
            ]
        ],
        'orderby' => 'date',
        'order'   => 'DESC'
    ]);
    
    $story_data = [];
    foreach ( $stories as $story ) {
        $vendor_id = get_post_meta( $story->ID, '_story_vendor_id', true );
        $vendor_name = get_user_meta( $vendor_id, 'gtg_shop_name', true );
        $media_url = get_post_meta( $story->ID, '_story_media_url', true );
        $expiry = get_post_meta( $story->ID, '_story_expiry', true );
        
        $story_data[] = [
            'id'          => $story->ID,
            'vendor_name' => $vendor_name,
            'vendor_id'   => $vendor_id,
            'caption'     => $story->post_content,
            'media_url'   => $media_url,
            'expiry'      => $expiry,
            'time_ago'    => human_time_diff( strtotime( $story->post_date ), current_time( 'timestamp' ) ) . ' ago'
        ];
    }
    
    wp_send_json_success( $story_data );
}
add_action( 'wp_ajax_gtg_get_location_stories', 'gtg_get_location_stories' );
add_action( 'wp_ajax_nopriv_gtg_get_location_stories', 'gtg_get_location_stories' );

// Shortcode: Display stories carousel
function grabtogo_stories_carousel_shortcode( $atts ) {
    $atts = shortcode_atts([
        'city' => '',
        'limit' => 10
    ], $atts );
    
    ob_start();
    ?>
    <div class="gtg-stories-carousel" data-city="<?php echo esc_attr( $atts['city'] ); ?>">
        <h3 class="stories-title">Stories Near You</h3>
        <div class="stories-container" id="stories-container">
            <div class="story-loading">Loading stories...</div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'grabtogo_stories_carousel', 'grabtogo_stories_carousel_shortcode' );

// Add Dokan dashboard story management page
function grabtogo_add_story_dashboard_menu( $urls ) {
    $urls['post-story'] = [
        'title' => 'Post Story',
        'icon'  => '<i class="fas fa-camera"></i>',
        'url'   => dokan_get_navigation_url( 'post-story' ),
        'pos'   => 155
    ];
    return $urls;
}
add_filter( 'dokan_get_dashboard_nav', 'grabtogo_add_story_dashboard_menu' );

// Handle story dashboard template
function grabtogo_story_dashboard_template( $query_vars ) {
    if ( isset( $query_vars['post-story'] ) ) {
        get_header( 'dashboard' );
        ?>
        <div class="dashboard-content-container" data-simplebar>
            <div class="dashboard-content-inner">
                <h3><i class="fas fa-camera"></i> Post Your Story</h3>
                <p>Share what's happening at your store with customers in your city!</p>
                
                <form id="gtg-story-form" class="gtg-story-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="story_media">Upload Image/Video*</label>
                        <input type="file" id="story_media" name="story_media" accept="image/*,video/*" required>
                        <small>Max file size: 10MB. Supported: JPG, PNG, MP4</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="story_caption">Caption</label>
                        <textarea id="story_caption" name="caption" rows="3" placeholder="Tell customers about your offer or what's new..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="expiry_hours">Story Duration</label>
                        <select id="expiry_hours" name="expiry_hours">
                            <option value="24">24 Hours</option>
                            <option value="48">48 Hours</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Post Story
                    </button>
                </form>
                
                <hr>
                
                <h4>Your Recent Stories</h4>
                <div id="vendor-stories-list">
                    Loading your stories...
                </div>
                
            </div>
        </div>
        <?php
        get_footer( 'dashboard' );
        return;
    }
}
add_action( 'dokan_load_custom_template', 'grabtogo_story_dashboard_template' );

// Clean up expired stories (run daily)
function grabtogo_cleanup_expired_stories() {
    $expired_stories = get_posts([
        'post_type'      => 'vendor_story',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => '_story_expiry',
                'value'   => current_time( 'mysql' ),
                'compare' => '<'
            ]
        ]
    ]);
    
    foreach ( $expired_stories as $story ) {
        wp_update_post([
            'ID'          => $story->ID,
            'post_status' => 'expired'
        ]);
    }
}
add_action( 'grabtogo_daily_cleanup', 'grabtogo_cleanup_expired_stories' );

// Schedule daily cleanup if not already scheduled
if ( ! wp_next_scheduled( 'grabtogo_daily_cleanup' ) ) {
    wp_schedule_event( time(), 'daily', 'grabtogo_daily_cleanup' );
}

// ============ END VENDOR STORIES FEATURE ============

// ============ ADDITIONAL MOBILE UX HELPERS ============

// Shortcode: Category navigation bar
function grabtogo_category_bar_shortcode( $atts ) {
    $atts = shortcode_atts([
        'sticky' => 'true'
    ], $atts );
    
    $categories = get_terms([
        'taxonomy' => 'listing_category',
        'hide_empty' => true,
        'number' => 10
    ]);
    
    if ( empty( $categories ) || is_wp_error( $categories ) ) {
        return '';
    }
    
    $sticky_class = $atts['sticky'] === 'true' ? 'sticky' : '';
    
    ob_start();
    ?>
    <div class="gtg-category-bar <?php echo esc_attr( $sticky_class ); ?>">
        <a href="<?php echo home_url( '/listings/' ); ?>" class="category-item">All</a>
        <?php foreach ( $categories as $category ) : ?>
            <a href="<?php echo get_term_link( $category ); ?>" class="category-item">
                <?php echo esc_html( $category->name ); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'grabtogo_category_bar', 'grabtogo_category_bar_shortcode' );

// AJAX: Get vendor stories for dashboard
function gtg_get_vendor_stories() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Please log in.' );
    }
    
    $user_id = get_current_user_id();
    $stories = get_posts([
        'post_type'      => 'vendor_story',
        'author'         => $user_id,
        'post_status'    => ['publish', 'expired'],
        'posts_per_page' => 10,
        'orderby'        => 'date',
        'order'          => 'DESC'
    ]);
    
    $story_data = [];
    foreach ( $stories as $story ) {
        $expiry = get_post_meta( $story->ID, '_story_expiry', true );
        $media_url = get_post_meta( $story->ID, '_story_media_url', true );
        $is_active = strtotime( $expiry ) > current_time( 'timestamp' );
        
        $story_data[] = [
            'id'        => $story->ID,
            'title'     => $story->post_title,
            'caption'   => $story->post_content,
            'media_url' => $media_url,
            'expiry'    => $expiry,
            'status'    => $is_active ? 'active' : 'expired',
            'created'   => human_time_diff( strtotime( $story->post_date ), current_time( 'timestamp' ) ) . ' ago'
        ];
    }
    
    wp_send_json_success( $story_data );
}
add_action( 'wp_ajax_gtg_get_vendor_stories', 'gtg_get_vendor_stories' );

// Mobile app detection
function grabtogo_is_mobile() {
    return wp_is_mobile() || isset( $_SERVER['HTTP_X_REQUESTED_WITH'] );
}

// Add mobile viewport meta tag
function grabtogo_add_mobile_viewport() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    echo '<meta name="mobile-web-app-capable" content="yes">';
    echo '<meta name="apple-mobile-web-app-capable" content="yes">';
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">';
    echo '<meta name="theme-color" content="#ff6f00">';
}
add_action( 'wp_head', 'grabtogo_add_mobile_viewport' );

// Add PWA manifest (for app-like experience)
function grabtogo_add_pwa_manifest() {
    ?>
    <link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/manifest.json">
    <script>
        // Register service worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo get_stylesheet_directory_uri(); ?>/sw.js')
                .then(function(registration) {
                    console.log('SW registered: ', registration);
                }).catch(function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }
    </script>
    <?php
}
add_action( 'wp_head', 'grabtogo_add_pwa_manifest' );

// Enhanced listing display for mobile
function grabtogo_mobile_listing_card( $listing_id ) {
    $listing = get_post( $listing_id );
    if ( ! $listing ) return '';
    
    $featured_image = get_the_post_thumbnail_url( $listing_id, 'medium' );
    $price = get_post_meta( $listing_id, '_regular_price', true );
    $sale_price = get_post_meta( $listing_id, '_sale_price', true );
    $vendor_id = $listing->post_author;
    $vendor_name = get_user_meta( $vendor_id, 'gtg_shop_name', true );
    $city = get_post_meta( $listing_id, '_job_location', true );
    
    ob_start();
    ?>
    <div class="gtg-mobile-listing-card" data-listing-id="<?php echo $listing_id; ?>">
        <?php if ( $featured_image ) : ?>
            <div class="listing-image">
                <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $listing->post_title ); ?>">
                <?php if ( $sale_price ) : ?>
                    <span class="sale-badge">SALE</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="listing-content">
            <h3 class="listing-title"><?php echo esc_html( $listing->post_title ); ?></h3>
            <div class="listing-meta">
                <span class="vendor-name"><?php echo esc_html( $vendor_name ); ?></span>
                <span class="listing-location"><?php echo esc_html( $city ); ?></span>
            </div>
            
            <?php if ( $price ) : ?>
                <div class="listing-price">
                    <?php if ( $sale_price ) : ?>
                        <span class="original-price">₹<?php echo number_format( $price ); ?></span>
                        <span class="sale-price">₹<?php echo number_format( $sale_price ); ?></span>
                    <?php else : ?>
                        <span class="current-price">₹<?php echo number_format( $price ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <a href="<?php echo get_permalink( $listing_id ); ?>" class="view-offer-btn">
                View Offer
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Force mobile redirect for specific pages
function grabtogo_force_mobile_redirect() {
    if ( grabtogo_is_mobile() && ! is_admin() ) {
        // Add any specific mobile redirects here
        if ( is_page( 'dashboard' ) && ! strpos( $_SERVER['REQUEST_URI'], '/my-profile/' ) ) {
            wp_redirect( home_url( '/my-profile/' ) );
            exit;
        }
    }
}
add_action( 'template_redirect', 'grabtogo_force_mobile_redirect' );

// Add connection status indicator
function grabtogo_add_connection_status() {
    if ( grabtogo_is_mobile() ) {
        echo '<div class="gtg-connection-status" id="connection-status"></div>';
    }
}
add_action( 'wp_footer', 'grabtogo_add_connection_status' );

// Optimize images for mobile
function grabtogo_mobile_image_sizes( $sizes ) {
    if ( grabtogo_is_mobile() ) {
        $sizes['mobile-thumb'] = [
            'width'  => 400,
            'height' => 300,
            'crop'   => true
        ];
        $sizes['mobile-large'] = [
            'width'  => 800,
            'height' => 600,
            'crop'   => true
        ];
    }
    return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'grabtogo_mobile_image_sizes' );

// ============ END MOBILE UX HELPERS ============